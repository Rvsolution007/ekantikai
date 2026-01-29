<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Catalogue;
use App\Models\Customer;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\WhatsappChat;
use App\Services\AIService;
use App\Services\BotControlService;
use App\Services\LanguageDetectionService;
use App\Services\MessageProcessorService;
use App\Services\ProductConfirmationService;
use App\Services\QuestionnaireService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    protected BotControlService $botControlService;
    protected LanguageDetectionService $languageService;
    protected ProductConfirmationService $productService;

    public function __construct()
    {
        $this->botControlService = new BotControlService();
        $this->languageService = new LanguageDetectionService();
        $this->productService = new ProductConfirmationService();
    }

    /**
     * Handle incoming WhatsApp webhook from Evolution API
     */
    public function handle(Request $request, string $instanceName = 'default')
    {
        try {
            $data = $request->all();

            // DEBUG: Log ALL incoming webhook data for troubleshooting
            Log::channel('daily')->info('=== WEBHOOK CALL RECEIVED ===', [
                'instance' => $instanceName,
                'ip' => $request->ip(),
                'event' => $data['event'] ?? 'unknown',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'raw_data' => json_encode($data),
            ]);

            // Also write to a dedicated webhook log file
            file_put_contents(
                storage_path('logs/webhook_debug.log'),
                date('Y-m-d H:i:s') . " | Instance: {$instanceName} | Event: " . ($data['event'] ?? 'unknown') . " | IP: " . $request->ip() . "\n",
                FILE_APPEND
            );

            // Get event type
            $event = $data['event'] ?? null;

            // Only process messages
            if (!in_array($event, ['messages.upsert', 'message', 'MESSAGES_UPSERT'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'not a message event']);
            }

            // Extract message data
            $messageData = $this->extractMessageData($data);
            if (!$messageData || empty($messageData['content'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'no message data']);
            }

            // Find tenant by instance name
            $tenant = $this->findTenantByInstance($instanceName);
            if (!$tenant) {
                Log::warning('Tenant not found for instance', ['instance' => $instanceName]);
                return response()->json(['status' => 'error', 'reason' => 'tenant not found']);
            }

            // Skip if from self (sent messages)
            if ($messageData['fromMe']) {
                return response()->json(['status' => 'ignored', 'reason' => 'self message']);
            }

            // Skip group messages - only process 1-on-1 chats
            if (!empty($messageData['isGroupMessage'])) {
                Log::debug('Skipping group message', ['phone' => $messageData['phone']]);
                return response()->json(['status' => 'ignored', 'reason' => 'group message']);
            }

            // DEDUPLICATION: Skip if message already processed
            $cacheKey = 'processed_msg_' . ($messageData['messageId'] ?? md5($messageData['phone'] . $messageData['content'] . $messageData['timestamp']));
            if (Cache::has($cacheKey)) {
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate message']);
            }
            Cache::put($cacheKey, true, 60);

            // Skip if message matches bot's greeting patterns (self-reply prevention)
            if ($this->isBotMessage($messageData['content'])) {
                return response()->json(['status' => 'ignored', 'reason' => 'bot message pattern detected']);
            }

            // CHECK BOT CONTROL COMMANDS (Point 2)
            $controlResult = $this->botControlService->handleControlMessage(
                $tenant,
                $messageData['phone'],
                $messageData['content']
            );
            if ($controlResult) {
                // This is a control message, send confirmation
                if ($controlResult['success']) {
                    $this->sendResponse($tenant, $messageData['phone'], $controlResult['message']);
                }
                return response()->json([
                    'status' => 'control_command',
                    'result' => $controlResult
                ]);
            }

            // Get or create customer
            $customer = $this->getOrCreateCustomer($tenant->id, $messageData['phone'], $messageData['name']);

            // CHECK IF BOT IS STOPPED (Point 2)
            if ($customer->isBotStopped()) {
                // Still save message to database but don't respond
                $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);
                return response()->json(['status' => 'ignored', 'reason' => 'bot stopped for this user']);
            }

            // Get or create lead based on timeout setting
            $lead = $customer->getOrCreateLead();

            // CHECK IF BOT IS ACTIVE FOR THIS LEAD (Point 7)
            if (!$lead->bot_active) {
                $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);
                return response()->json(['status' => 'ignored', 'reason' => 'bot inactive for this lead']);
            }

            // Detect language (Point 8.4)
            $detectedLanguage = $this->languageService->detect($messageData['content']);
            $customer->setLanguage($detectedLanguage);
            $lead->update(['detected_language' => $detectedLanguage]);

            // Save incoming message with reply info (Point 4 & 5)
            $this->saveMessage($tenant->id, $customer->id, 'user', $messageData['content'], $messageData);

            // PROCESS WITH AI (Point 8)
            $aiService = new AIService();
            $aiResponse = $aiService->processMessageEnhanced(
                $tenant,
                $customer,
                $lead,
                $messageData['content'],
                [
                    'reply_to_content' => $messageData['replyToContent'] ?? null,
                ]
            );

            // Update lead from AI response (Point 8.5, 8.8)
            if ($aiResponse['success']) {
                $this->processAIResponse($tenant, $customer, $lead, $aiResponse);
            }

            // Update customer's last activity
            $customer->updateLastActivity();

            // Get response message from AI
            $responseMessage = $aiResponse['response_message'] ?? '';
            $detectedLang = $aiResponse['detected_language'] ?? 'hi';

            // DEBUG: Log when response is empty
            if (empty($responseMessage)) {
                Log::warning('AI returned empty response_message', [
                    'admin_id' => $tenant->id,
                    'phone' => $messageData['phone'],
                    'user_message' => $messageData['content'],
                    'ai_success' => $aiResponse['success'] ?? false,
                    'ai_response' => json_encode($aiResponse),
                ]);

                // IMPORTANT: Refresh lead to get latest collected_data after processAIResponse saved it
                $lead->refresh();

                // SMART EXTRACTION WITH VALIDATION: Try to save user's message as answer to current pending question
                // Only save if the answer is valid in catalogue
                $currentPending = $this->getNextPendingQuestion($tenant->id, $lead);
                $validationResult = null;

                if ($currentPending && !empty($messageData['content'])) {
                    $userAnswer = trim($messageData['content']);

                    // CRITICAL FIX: Check if user is asking for options (not providing an answer)
                    if ($this->isQuestionTypeResponse($userAnswer)) {
                        // User is asking "konse he?", "options batao?" etc.
                        // Don't save - instead show available options
                        $availableOptions = $this->getFieldOptionsFromCatalogue(
                            $tenant->id,
                            $currentPending['field_name'],
                            $lead
                        );

                        Log::info('Detected question-type response, showing options', [
                            'field' => $currentPending['field_name'],
                            'user_message' => $userAnswer,
                            'options_count' => count($availableOptions),
                        ]);

                        // Set validation result to show options
                        $validationResult = [
                            'valid' => false,
                            'is_question_request' => true,
                            'available_options' => $availableOptions,
                            'invalid_items' => [],
                        ];
                    }
                    // Only validate against catalogue if not a question
                    elseif (strlen($userAnswer) < 100) {
                        $validationResult = $this->validateUserAnswerWithCatalogue(
                            $tenant->id,
                            $currentPending['field_name'],
                            $userAnswer,
                            $lead
                        );

                        if ($validationResult['valid']) {
                            // Valid answer - save it
                            $lead->addCollectedData($currentPending['field_name'], $validationResult['value'], 'workflow_questions');
                            $lead->refresh();

                            Log::info('Smart extraction saved validated user answer', [
                                'field' => $currentPending['field_name'],
                                'answer' => $validationResult['value'],
                                'lead_id' => $lead->id,
                            ]);

                            // SYNC TO LEAD_PRODUCTS: Create/update LeadProduct for Product Quotation display
                            $this->syncWorkflowToLeadProduct($lead, $tenant->id);
                        } else {
                            // Invalid answer - show available options
                            Log::warning('Smart extraction found invalid answer', [
                                'field' => $currentPending['field_name'],
                                'answer' => $userAnswer,
                                'available' => $validationResult['available_options'] ?? [],
                                'lead_id' => $lead->id,
                            ]);
                        }
                    }
                }

                // IMPROVED FALLBACK: Generate appropriate response
                // Get next pending question from flowchart (now after smart extraction)
                $pendingQuestion = $this->getNextPendingQuestion($tenant->id, $lead);

                if ($validationResult && !$validationResult['valid'] && !empty($validationResult['available_options'])) {
                    $options = implode(', ', array_slice($validationResult['available_options'], 0, 15));

                    // Check if user was asking for options (friendly response) vs giving invalid answer
                    if (!empty($validationResult['is_question_request'])) {
                        // User asked "konse he?" - show options in friendly manner
                        $fieldName = $currentPending['display_name'] ?? $currentPending['field_name'] ?? 'options';
                        $responseMessage = match ($detectedLang) {
                            'en' => "Here are the available {$fieldName} options: {$options}. Which one would you like?",
                            'hi', 'hinglish' => "Ji, {$fieldName} me ye options available hain: {$options}. Kaunsa chahiye?",
                            default => "{$fieldName} me ye options hain: {$options}. Kaunsa chahiye?",
                        };
                    } else {
                        // Invalid answer detected - tell user what's not available and show options
                        $invalidItems = $validationResult['invalid_items'] ?? [];
                        $invalidText = !empty($invalidItems) ? implode(', ', $invalidItems) : $messageData['content'];

                        $responseMessage = match ($detectedLang) {
                            'en' => "Sorry, '{$invalidText}' is not available. Please choose from: {$options}",
                            'hi', 'hinglish' => "Maaf kijiye, '{$invalidText}' available nahi hai. Yeh options available hain: {$options}",
                            default => "'{$invalidText}' available nahi hai. Available options: {$options}",
                        };
                    }
                } elseif ($pendingQuestion) {
                    // Ask the next pending question
                    $responseMessage = match ($detectedLang) {
                        'en' => "Got it! Now, what is your " . strtolower($pendingQuestion['display_name']) . "?",
                        'hi', 'hinglish' => "Ji zaroor! Ab aapka " . $pendingQuestion['display_name'] . " kya hai?",
                        default => "Ji, aapka " . $pendingQuestion['display_name'] . " bataiye?",
                    };
                } else {
                    // No pending questions - all answered, confirm order
                    $responseMessage = match ($detectedLang) {
                        'en' => "Got it! Your order has been noted. Is there anything else you need?",
                        'hi', 'hinglish' => "Ji zaroor! Aapka order note ho gaya hai. Kuch aur chahiye?",
                        default => "Ji, aapka order note ho gaya. Aur kuch?",
                    };
                }
            }

            // CHECK FOR CATALOGUE IMAGE (Point 12)
            $catalogueMedia = null;
            if (!empty($aiResponse['unique_field_mentioned'])) {
                $catalogueMedia = $aiService->checkCatalogueForUniqueField(
                    $tenant->id,
                    $aiResponse['unique_field_mentioned']
                );
            }

            // Send response (now always has a message due to fallback)
            if (!empty($responseMessage)) {
                // Send catalogue image first if available
                if ($catalogueMedia && !empty($catalogueMedia['image_url'])) {
                    $this->sendImageResponse($tenant, $messageData['phone'], $catalogueMedia['image_url']);
                }

                $this->sendResponse($tenant, $messageData['phone'], $responseMessage);

                // Save bot response (Point 5)
                $this->saveMessage($tenant->id, $customer->id, 'assistant', $responseMessage, [
                    'ai_response' => true,
                    'detected_language' => $detectedLanguage,
                ]);
            }

            // CHECK IF ALL REQUIRED QUESTIONS COMPLETE (Point 7)
            // Only mark complete if AI explicitly says so AND we actually sent a response
            if (($aiResponse['all_required_complete'] ?? false) && !empty($aiResponse['response_message'])) {
                $lead->markBotComplete();
            }

            // Update lead score after processing
            $lead->calculateScore();

            return response()->json([
                'status' => 'success',
                'lead_id' => $lead->id,
                'detected_language' => $detectedLanguage,
                'bot_active' => $lead->bot_active,
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process AI response and update lead/customer
     */
    protected function processAIResponse(Admin $tenant, Customer $customer, Lead $lead, array $aiResponse): void
    {
        // Update extracted data (Point 8.8)
        // CRITICAL FIX: Only save workflow fields if they match current pending question
        // This prevents flowchart skip when AI auto-extracts from casual mentions
        if (!empty($aiResponse['extracted_data'])) {
            // Get workflow field names for this admin
            $workflowFields = $this->getWorkflowFieldNames($tenant->id);

            // Get current pending question from flowchart for validation
            $currentPending = $this->getNextPendingQuestion($tenant->id, $lead);
            $pendingFieldName = $currentPending ? strtolower($currentPending['field_name']) : null;

            foreach ($aiResponse['extracted_data'] as $key => $value) {
                if ($value !== null && !empty(trim($value))) {
                    $fieldKey = strtolower($key);

                    // Check if this is a workflow/product field
                    if (in_array($fieldKey, $workflowFields)) {
                        // FLOWCHART ORDER CHECK: Only save if this is the current pending question
                        // OR if all previous questions are already answered
                        if ($pendingFieldName === null || $fieldKey === $pendingFieldName) {
                            // VALIDATION CHECK: Validate against catalogue before saving
                            $validationResult = $this->validateUserAnswerWithCatalogue(
                                $tenant->id,
                                $key,
                                $value,
                                $lead
                            );

                            if ($validationResult['valid']) {
                                $lead->addCollectedData($key, $validationResult['value'], 'workflow_questions');
                                Log::info('AI extracted valid workflow data', [
                                    'field' => $key,
                                    'value' => $validationResult['value'],
                                    'lead_id' => $lead->id,
                                ]);

                                // Update pending field for next iteration
                                $lead->refresh();
                                $currentPending = $this->getNextPendingQuestion($tenant->id, $lead);
                                $pendingFieldName = $currentPending ? strtolower($currentPending['field_name']) : null;
                            } else {
                                Log::warning('AI extracted data not valid in catalogue, skipping save', [
                                    'field' => $key,
                                    'value' => $value,
                                    'available_options' => array_slice($validationResult['available_options'] ?? [], 0, 10),
                                ]);
                            }
                        } else {
                            Log::info('Skipping out-of-order AI extraction to preserve flowchart', [
                                'extracted_field' => $fieldKey,
                                'pending_field' => $pendingFieldName,
                                'value' => $value,
                            ]);
                        }
                    } else {
                        // Global field (city, purpose, etc.) - save directly to global_questions
                        $lead->addCollectedData($key, $value, 'global_questions');
                    }
                }
            }
        }

        // Handle product confirmations using ProductConfirmationService (Point 8.3)
        if (!empty($aiResponse['product_confirmations'])) {
            Log::debug('Processing product confirmations with service', [
                'lead_id' => $lead->id,
                'count' => count($aiResponse['product_confirmations']),
                'data' => $aiResponse['product_confirmations'],
            ]);

            $results = $this->productService->processConfirmations($lead, $aiResponse['product_confirmations']);

            Log::debug('Product confirmation results', [
                'lead_id' => $lead->id,
                'results' => $results,
            ]);
        }

        // Handle product rejections/deletions (Point 8.3b)
        if (!empty($aiResponse['product_rejections'])) {
            Log::debug('Processing product rejections', [
                'lead_id' => $lead->id,
                'rejections' => $aiResponse['product_rejections'],
            ]);

            $results = $this->productService->processRejections($lead, $aiResponse['product_rejections']);

            Log::debug('Product rejection results', [
                'lead_id' => $lead->id,
                'results' => $results,
            ]);
        }

        // Update lead status (Point 8.5)
        if (!empty($aiResponse['lead_status_suggestion'])) {
            $status = LeadStatus::where('admin_id', $tenant->id)
                ->where('name', $aiResponse['lead_status_suggestion'])
                ->first();
            if ($status) {
                $lead->updateLeadStatus($status->id);
            }
        }
    }

    /**
     * Get all workflow field names for an admin
     * These are the product-related questions from flowchart/product_questions table
     */
    protected function getWorkflowFieldNames(int $adminId): array
    {
        // Cache for 5 minutes to avoid repeated DB queries
        return Cache::remember("workflow_fields_{$adminId}", 300, function () use ($adminId) {
            $fields = \App\Models\ProductQuestion::where('admin_id', $adminId)
                ->pluck('field_name')
                ->map(fn($f) => strtolower($f))
                ->toArray();

            // Also include common product field names that might not be in database
            $commonProductFields = ['category', 'product', 'model', 'size', 'finish', 'color', 'quantity', 'qty', 'packaging'];

            return array_unique(array_merge($fields, $commonProductFields));
        });
    }

    /**
     * Get the next pending (unanswered) question from flowchart
     */
    protected function getNextPendingQuestion(int $adminId, Lead $lead): ?array
    {
        // Get all workflow questions
        $questions = \App\Models\ProductQuestion::where('admin_id', $adminId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($questions->isEmpty()) {
            return null;
        }

        // Get collected data
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData['workflow_questions'] ?? [];
        $globalAnswers = $collectedData['global_questions'] ?? [];

        // Merge all answered fields
        $allAnswered = array_merge(
            array_change_key_case($workflowAnswers, CASE_LOWER),
            array_change_key_case($globalAnswers, CASE_LOWER)
        );

        // Find first unanswered question
        foreach ($questions as $question) {
            $fieldName = strtolower($question->field_name);
            if (!isset($allAnswered[$fieldName]) || empty($allAnswered[$fieldName])) {
                return [
                    'field_name' => $question->field_name,
                    'display_name' => $question->display_name,
                    'is_required' => $question->is_required,
                    'question_template' => $question->question_template, // Ask Question Format from flowchart
                ];
            }
        }

        return null; // All questions answered
    }

    /**
     * Validate user's answer against catalogue data
     * Returns validation result with valid/invalid items and available options
     */
    protected function validateUserAnswerWithCatalogue(int $adminId, string $fieldName, string $userAnswer, Lead $lead): array
    {
        $fieldName = strtolower($fieldName);

        // Get collected data to filter catalogue based on previous answers
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData['workflow_questions'] ?? [];
        $globalAnswers = $collectedData['global_questions'] ?? [];
        $allAnswers = array_merge(
            array_change_key_case($workflowAnswers, CASE_LOWER),
            array_change_key_case($globalAnswers, CASE_LOWER)
        );

        // Build catalogue query with filters from previous answers
        $query = \App\Models\Catalogue::where('admin_id', $adminId)
            ->where('is_active', true);

        // Apply category filter if already answered
        if (!empty($allAnswers['category'])) {
            $query->where('product_type', 'LIKE', '%' . $allAnswers['category'] . '%');
        }

        // Get available values for this field from catalogue
        $availableOptions = [];

        if ($fieldName === 'category' || $fieldName === 'product_type') {
            // Get unique product types
            $availableOptions = $query->distinct()->pluck('product_type')->filter()->unique()->values()->toArray();
        } elseif ($fieldName === 'model' || $fieldName === 'model_code' || $fieldName === 'model_number') {
            // Get unique model codes
            $availableOptions = $query->distinct()->pluck('model_code')->filter()->unique()->values()->toArray();
        } elseif ($fieldName === 'size') {
            // Get sizes from all matching products
            $products = $query->pluck('sizes')->filter()->toArray();
            foreach ($products as $sizeStr) {
                $sizes = array_map('trim', explode(',', $sizeStr));
                $availableOptions = array_merge($availableOptions, $sizes);
            }
            $availableOptions = array_unique(array_filter($availableOptions));
        } elseif ($fieldName === 'finish' || $fieldName === 'color') {
            // Get finishes from all matching products
            $products = $query->pluck('finishes')->filter()->toArray();
            foreach ($products as $finishStr) {
                $finishes = array_map('trim', explode(',', $finishStr));
                $availableOptions = array_merge($availableOptions, $finishes);
            }
            $availableOptions = array_unique(array_filter($availableOptions));
        } else {
            // Try to get from dynamic 'data' JSON field
            $products = $query->whereNotNull('data')->pluck('data')->toArray();
            foreach ($products as $data) {
                if (is_array($data) && isset($data[$fieldName])) {
                    $value = $data[$fieldName];
                    if (is_array($value)) {
                        $availableOptions = array_merge($availableOptions, $value);
                    } else {
                        $availableOptions[] = $value;
                    }
                }
            }
            $availableOptions = array_unique(array_filter($availableOptions));
        }

        // IMPORTANT: If no options available in catalogue, accept the user's answer as-is
        // This handles cases where catalogue is empty or field not tracked in catalogue
        if (empty($availableOptions)) {
            Log::info('Validation: No catalogue options found, accepting user answer', [
                'field' => $fieldName,
                'user_answer' => $userAnswer,
                'admin_id' => $adminId,
            ]);

            return [
                'valid' => true,
                'value' => $userAnswer,
                'valid_items' => [$userAnswer],
                'invalid_items' => [],
                'available_options' => [],
                'no_catalogue_data' => true,
            ];
        }

        // Parse user answer (may contain multiple values separated by 'and', ',', '&')
        $userValues = preg_split('/[,&]|\band\b/i', $userAnswer);
        $userValues = array_map('trim', $userValues);
        $userValues = array_filter($userValues);

        // Validate each user-provided value
        $validItems = [];
        $invalidItems = [];

        foreach ($userValues as $userValue) {
            $found = false;
            foreach ($availableOptions as $option) {
                // Case-insensitive partial match
                if (stripos($option, $userValue) !== false || stripos($userValue, $option) !== false) {
                    $validItems[] = $option;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $invalidItems[] = $userValue;
            }
        }

        Log::debug('Validation result', [
            'field' => $fieldName,
            'user_answer' => $userAnswer,
            'available_options_count' => count($availableOptions),
            'valid_items' => $validItems,
            'invalid_items' => $invalidItems,
        ]);

        // Determine result
        if (empty($invalidItems)) {
            // All items are valid
            // Use ' or ' as separator so syncWorkflowToLeadProduct can split properly
            // The split pattern handles: or, and, aur, comma
            return [
                'valid' => true,
                'value' => count($validItems) > 1 ? implode(' or ', $validItems) : ($validItems[0] ?? ''),
                'valid_items' => $validItems,
                'invalid_items' => [],
                'available_options' => array_values($availableOptions),
            ];
        } elseif (!empty($validItems)) {
            // Some valid, some invalid - save valid ones and warn about invalid
            // Use ' or ' as separator so syncWorkflowToLeadProduct can split properly
            return [
                'valid' => false, // Mark as invalid to show warning
                'value' => count($validItems) > 1 ? implode(' or ', $validItems) : ($validItems[0] ?? ''),
                'valid_items' => $validItems,
                'invalid_items' => $invalidItems,
                'available_options' => array_values($availableOptions),
                'partial' => true, // Some items were valid
            ];
        } else {
            // All invalid
            return [
                'valid' => false,
                'value' => null,
                'valid_items' => [],
                'invalid_items' => $invalidItems,
                'available_options' => array_values($availableOptions),
            ];
        }
    }

    /**
     * Sync workflow answers to LeadProduct for Product Quotation display
     * Creates or updates a LeadProduct entry when we have at least category+model
     */
    protected function syncWorkflowToLeadProduct(Lead $lead, int $adminId): void
    {
        try {
            $collectedData = $lead->collected_data ?? [];
            $workflowAnswers = $collectedData['workflow_questions'] ?? [];

            if (empty($workflowAnswers)) {
                return;
            }

            // Normalize keys to lowercase
            $answers = array_change_key_case($workflowAnswers, CASE_LOWER);

            // Get unique key fields for this admin
            $uniqueKeyFields = \App\Models\ProductQuestion::where('admin_id', $adminId)
                ->where('is_unique_key', true)
                ->orderBy('unique_key_order')
                ->pluck('field_name')
                ->map(fn($f) => strtolower($f))
                ->toArray();

            // Get all product fields
            $allFields = \App\Models\ProductQuestion::where('admin_id', $adminId)
                ->orderBy('sort_order')
                ->pluck('field_name')
                ->map(fn($f) => strtolower($f))
                ->toArray();

            // Prepare field values - parse multi-values for unique key fields
            $fieldValues = [];
            foreach ($allFields as $field) {
                $value = $answers[$field] ?? null;

                if (empty($value)) {
                    $fieldValues[$field] = [null];
                    continue;
                }

                // For unique key fields, parse multi-values (split by , or and &)
                if (in_array($field, $uniqueKeyFields)) {
                    $values = preg_split('/[,&]|\bor\b|\band\b/i', $value);
                    $values = array_map('trim', $values);
                    $values = array_filter($values, fn($v) => !empty($v));
                    $fieldValues[$field] = !empty($values) ? array_values($values) : [$value];
                } else {
                    $fieldValues[$field] = [$value];
                }
            }

            // Generate Cartesian product of unique key field values
            $combinations = $this->generateCartesianProduct($fieldValues, $uniqueKeyFields);

            Log::info('Generating unique key combinations', [
                'lead_id' => $lead->id,
                'unique_keys' => $uniqueKeyFields,
                'combinations_count' => count($combinations),
            ]);

            // Create LeadProduct for each combination
            foreach ($combinations as $combo) {
                // Build unique key from unique key fields only
                $keyParts = [$lead->id];
                foreach ($uniqueKeyFields as $ukField) {
                    $keyParts[] = strtolower(trim($combo[$ukField] ?? ''));
                }
                $uniqueKey = implode('|', $keyParts);

                // Find or create LeadProduct
                $leadProduct = \App\Models\LeadProduct::firstOrNew([
                    'lead_id' => $lead->id,
                    'unique_key' => $uniqueKey,
                ]);

                // Update all fields from combination
                $leadProduct->admin_id = $adminId;

                // Map common field names to LeadProduct columns
                $columnMapping = [
                    'category' => 'category',
                    'product_type' => 'category',
                    'product_category' => 'category',
                    'model' => 'model',
                    'model_code' => 'model',
                    'model_number' => 'model',
                    'size' => 'size',
                    'finish' => 'finish',
                    'color' => 'finish',
                    'finish/color' => 'finish',
                    'qty' => 'qty',
                    'quantity' => 'qty',
                    'packaging' => 'packaging',
                    'material' => 'material',
                ];

                // Store dynamic data for fields not in standard columns
                $dynamicData = [];

                foreach ($combo as $field => $value) {
                    if (empty($value))
                        continue;

                    $column = $columnMapping[$field] ?? null;
                    if ($column && property_exists($leadProduct, $column)) {
                        $leadProduct->$column = $value;
                    } else {
                        // Store in dynamic data JSON
                        $dynamicData[$field] = $value;
                    }
                }

                if (!empty($dynamicData)) {
                    $leadProduct->data = array_merge($leadProduct->data ?? [], $dynamicData);
                }

                $leadProduct->save();

                Log::debug('Created/Updated LeadProduct', [
                    'lead_product_id' => $leadProduct->id,
                    'unique_key' => $uniqueKey,
                    'category' => $leadProduct->category,
                    'model' => $leadProduct->model,
                    'size' => $leadProduct->size,
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to sync workflow to LeadProduct', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'lead_id' => $lead->id,
            ]);
        }
    }

    /**
     * Generate Cartesian product of field values for unique key fields
     * Non-unique key fields use first value only
     */
    protected function generateCartesianProduct(array $fieldValues, array $uniqueKeyFields): array
    {
        $result = [[]];

        foreach ($fieldValues as $field => $values) {
            // For unique key fields, expand all values
            // For non-unique key fields, use only first value
            if (!in_array($field, $uniqueKeyFields)) {
                $values = [reset($values)]; // Use only first value
            }

            $newResult = [];
            foreach ($result as $combo) {
                foreach ($values as $value) {
                    $newCombo = $combo;
                    $newCombo[$field] = $value;
                    $newResult[] = $newCombo;
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    /**
     * Extract message data from webhook payload with reply detection (Point 4)
     */
    protected function extractMessageData(array $data): ?array
    {
        $message = $data['data'] ?? $data;

        if (isset($message['key'])) {
            $remoteJid = $message['key']['remoteJid'] ?? '';
            $phone = $this->cleanPhone($remoteJid);

            // Detect if this is a group message (JID ends with @g.us)
            $isGroupMessage = str_contains($remoteJid, '@g.us');

            $msgContent = $message['message'] ?? [];

            // REPLY DETECTION (Point 4)
            $replyToContent = null;
            $replyToMessageId = null;

            // Check for contextInfo in extendedTextMessage
            if (isset($msgContent['extendedTextMessage']['contextInfo'])) {
                $contextInfo = $msgContent['extendedTextMessage']['contextInfo'];
                $replyToMessageId = $contextInfo['stanzaId'] ?? null;
                $replyToContent = $contextInfo['quotedMessage']['conversation']
                    ?? $contextInfo['quotedMessage']['extendedTextMessage']['text']
                    ?? null;
            }

            return [
                'phone' => $phone,
                'name' => $message['pushName'] ?? $phone,
                'content' => $this->extractContent($msgContent),
                'fromMe' => $message['key']['fromMe'] ?? false,
                'messageId' => $message['key']['id'] ?? null,
                'whatsappMessageId' => $message['key']['id'] ?? null,
                'timestamp' => $message['messageTimestamp'] ?? time(),
                'isReply' => !empty($replyToMessageId),
                'replyToMessageId' => $replyToMessageId,
                'replyToContent' => $replyToContent,
                'isGroupMessage' => $isGroupMessage,
            ];
        }

        // Legacy format
        if (isset($message['messages']) && is_array($message['messages'])) {
            $msg = $message['messages'][0] ?? null;
            if ($msg) {
                $msgContent = $msg['message'] ?? [];
                $remoteJid = $msg['key']['remoteJid'] ?? '';

                // Detect if this is a group message (JID ends with @g.us)
                $isGroupMessage = str_contains($remoteJid, '@g.us');

                // Reply detection for legacy
                $replyToContent = null;
                $replyToMessageId = null;
                if (isset($msgContent['extendedTextMessage']['contextInfo'])) {
                    $contextInfo = $msgContent['extendedTextMessage']['contextInfo'];
                    $replyToMessageId = $contextInfo['stanzaId'] ?? null;
                    $replyToContent = $contextInfo['quotedMessage']['conversation'] ?? null;
                }

                return [
                    'phone' => $this->cleanPhone($remoteJid),
                    'name' => $msg['pushName'] ?? '',
                    'content' => $this->extractContent($msgContent),
                    'fromMe' => $msg['key']['fromMe'] ?? false,
                    'messageId' => $msg['key']['id'] ?? null,
                    'whatsappMessageId' => $msg['key']['id'] ?? null,
                    'timestamp' => $msg['messageTimestamp'] ?? time(),
                    'isReply' => !empty($replyToMessageId),
                    'replyToMessageId' => $replyToMessageId,
                    'replyToContent' => $replyToContent,
                    'isGroupMessage' => $isGroupMessage,
                ];
            }
        }

        return null;
    }

    /**
     * Extract text content from message
     */
    protected function extractContent(array $message): string
    {
        if (isset($message['conversation'])) {
            return $message['conversation'];
        }

        if (isset($message['extendedTextMessage']['text'])) {
            return $message['extendedTextMessage']['text'];
        }

        if (isset($message['buttonsResponseMessage']['selectedButtonId'])) {
            return $message['buttonsResponseMessage']['selectedButtonId'];
        }

        if (isset($message['listResponseMessage']['singleSelectReply']['selectedRowId'])) {
            return $message['listResponseMessage']['singleSelectReply']['selectedRowId'];
        }

        foreach (['imageMessage', 'videoMessage', 'documentMessage'] as $type) {
            if (isset($message[$type]['caption'])) {
                return $message[$type]['caption'];
            }
        }

        return '';
    }

    /**
     * Find tenant by WhatsApp instance name
     */
    protected function findTenantByInstance(string $instanceName): ?Admin
    {
        $admin = Admin::where('whatsapp_instance', $instanceName)
            ->where('is_active', true)
            ->first();

        if ($admin) {
            Log::debug('Found admin by whatsapp_instance', [
                'instance' => $instanceName,
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'catalogue_count' => $admin->catalogues()->where('is_active', true)->count(),
            ]);
            return $admin;
        }

        if (\Schema::hasTable('whatsapp_instances')) {
            $instance = DB::table('whatsapp_instances')
                ->where('instance_name', $instanceName)
                ->first();

            if ($instance) {
                $admin = Admin::find($instance->admin_id);
                if ($admin) {
                    Log::debug('Found admin via whatsapp_instances table', [
                        'instance' => $instanceName,
                        'admin_id' => $admin->id,
                    ]);
                }
                return $admin;
            }
        }

        Log::warning('Could not find tenant by instance name, using first active', [
            'instance' => $instanceName
        ]);
        $fallbackAdmin = Admin::where('is_active', true)->first();
        if ($fallbackAdmin) {
            Log::debug('Using fallback admin', [
                'admin_id' => $fallbackAdmin->id,
                'admin_name' => $fallbackAdmin->name,
                'catalogue_count' => $fallbackAdmin->catalogues()->where('is_active', true)->count(),
            ]);
        }
        return $fallbackAdmin;
    }

    /**
     * Get or create customer
     */
    protected function getOrCreateCustomer(int $adminId, string $phone, ?string $name): Customer
    {
        $customer = Customer::where('admin_id', $adminId)
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            $customer = Customer::create([
                'admin_id' => $adminId,
                'phone' => $phone,
                'name' => $name,
                'bot_enabled' => true,
                'bot_stopped_by_user' => false,
                'detected_language' => 'hi',
                'last_activity_at' => now(),
            ]);
        } else {
            $customer->update([
                'name' => $name ?: $customer->name,
                'last_activity_at' => now(),
            ]);
        }

        return $customer;
    }

    /**
     * Save chat message with reply info (Point 4 & 5)
     */
    protected function saveMessage(int $adminId, int $customerId, string $role, string $content, array $metadata = []): void
    {
        WhatsappChat::create([
            'admin_id' => $adminId,
            'customer_id' => $customerId,
            'whatsapp_user_id' => $customerId, // Use customer ID as whatsapp_user_id
            'number' => $metadata['phone'] ?? null,
            'role' => $role,
            'content' => $content,
            'whatsapp_message_id' => $metadata['whatsappMessageId'] ?? $metadata['messageId'] ?? null,
            'message_id' => $metadata['messageId'] ?? null,
            'is_reply' => $metadata['isReply'] ?? false,
            'reply_to_message_id' => $metadata['replyToMessageId'] ?? null,
            'reply_to_content' => $metadata['replyToContent'] ?? null,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Send response via WhatsApp
     */
    protected function sendResponse(Admin $tenant, string $phone, string $message): void
    {
        try {
            $instance = $tenant->whatsapp_instance;
            if (empty($instance)) {
                Log::warning('No WhatsApp instance configured for tenant', ['admin_id' => $tenant->id]);
                return;
            }

            $evolutionService = new \App\Services\WhatsApp\EvolutionApiService($tenant);
            $evolutionService->sendTextMessage($instance, $phone, $message);

            Log::info('Bot response sent', [
                'admin_id' => $tenant->id,
                'phone' => $phone,
                'message_length' => strlen($message)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'error' => $e->getMessage(),
                'phone' => $phone,
                'admin_id' => $tenant->id
            ]);
        }
    }

    /**
     * Send image response via WhatsApp (Point 12)
     */
    protected function sendImageResponse(Admin $tenant, string $phone, string $imageUrl): void
    {
        try {
            $instance = $tenant->whatsapp_instance;
            if (empty($instance)) {
                return;
            }

            $evolutionService = new \App\Services\WhatsApp\EvolutionApiService($tenant);
            $evolutionService->sendMediaMessage($instance, $phone, $imageUrl, 'image');

            Log::info('Bot image sent', [
                'admin_id' => $tenant->id,
                'phone' => $phone,
                'image_url' => $imageUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp image', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);
        }
    }

    /**
     * Clean phone number
     */
    protected function cleanPhone(string $jid): string
    {
        $phone = preg_replace('/@.*$/', '', $jid);
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Check if message is from bot (to prevent self-reply)
     */
    protected function isBotMessage(string $message): bool
    {
        $botPatterns = [
            'Hello! 🙏 How can I help you?',
            'Namaste! 🙏',
            'How can I help you?',
            'What product are you looking for?',
            'Main aapki kya madad kar sakta hoon?',
            'Aapko kaunsa product chahiye?',
            'Thank you! 🙏 Let me know if you need anything else.',
            'Dhanyavaad! 🙏',
            'Our team will contact you soon.',
            'I have collected all the information.',
        ];

        foreach ($botPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enhance flowchart question using AI
     * Takes the question template from flowchart and makes it friendly/conversational
     */
    protected function enhanceQuestionWithAI(Admin $tenant, array $pendingQuestion, string $language, string $userMessage): string
    {
        $questionTemplate = $pendingQuestion['question_template'] ?? null;
        $displayName = $pendingQuestion['display_name'] ?? $pendingQuestion['field_name'];
        $fieldName = $pendingQuestion['field_name'];

        // If no template, generate a default friendly question
        if (empty($questionTemplate)) {
            return match ($language) {
                'en' => "Got it! Now, what {$displayName} would you like?",
                'hi', 'hinglish' => "Ji samajh gaya! Ab aapko kaunsa {$displayName} chahiye?",
                default => "Ji! Aapka {$displayName} batayein?",
            };
        }

        // Use AI to enhance the question template into a friendly conversational question
        try {
            $aiService = new AIService();

            // Build a simple prompt to enhance the question
            $enhancePrompt = $this->buildQuestionEnhancePrompt($tenant, $questionTemplate, $displayName, $language, $userMessage);

            // Call AI with a minimal prompt for speed
            $response = $aiService->callAI($enhancePrompt, "Enhance this question naturally.");

            // Parse the response - expect just the enhanced question text
            $enhancedQuestion = $this->parseEnhancedQuestion($response, $questionTemplate, $displayName, $language);

            if (!empty($enhancedQuestion)) {
                Log::debug('AI enhanced question successfully', [
                    'original' => $questionTemplate,
                    'enhanced' => $enhancedQuestion,
                ]);
                return $enhancedQuestion;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to enhance question with AI, using template', [
                'error' => $e->getMessage(),
                'template' => $questionTemplate,
            ]);
        }

        // Fallback: Use the template directly with a friendly prefix
        return match ($language) {
            'en' => "Great! " . $questionTemplate,
            'hi', 'hinglish' => "Ji! " . $questionTemplate,
            default => $questionTemplate,
        };
    }

    /**
     * Build prompt to enhance a flowchart question
     */
    protected function buildQuestionEnhancePrompt(Admin $tenant, string $template, string $displayName, string $language, string $userMessage): string
    {
        $tenantName = $tenant->company_name ?? $tenant->name ?? 'our company';
        $tone = $tenant->ai_tone ?? 'friendly';

        $langInstruction = match ($language) {
            'en' => 'Respond in English only.',
            'hi' => 'Respond in Hindi only (Devanagari script).',
            'hinglish' => 'Respond in Hinglish (Hindi words in English script, casual style).',
            default => 'Respond in Hinglish.',
        };

        return <<<PROMPT
You are a {$tone} sales assistant for {$tenantName}.

TASK: Convert this question template into a natural, friendly conversational question.

QUESTION TEMPLATE (reference): "{$template}"
FIELD NAME: {$displayName}
USER'S LAST MESSAGE: "{$userMessage}"

RULES:
1. Make it sound natural and friendly, like a real salesperson
2. Keep the same meaning as the template
3. {$langInstruction}
4. Keep it SHORT - max 15-20 words
5. Include a brief acknowledgment of user's message if relevant
6. DO NOT add any greeting if not needed
7. If user mentioned something specific, acknowledge it briefly

OUTPUT: Return ONLY the enhanced question text, nothing else. No JSON, no explanation.

Example input: "Aapko kaunsa product chahiye?"
Example output: "Ji, aapko konsa product pasand aayega - handles, hinges ya kuch aur?"

Now enhance this question:
PROMPT;
    }

    /**
     * Parse the AI response to extract enhanced question
     */
    protected function parseEnhancedQuestion(string $response, string $fallbackTemplate, string $displayName, string $language): string
    {
        // Clean the response
        $cleaned = trim($response);

        // Remove any JSON formatting if accidentally returned
        $cleaned = preg_replace('/^```.*$/m', '', $cleaned);
        $cleaned = preg_replace('/^\{.*?\}$/s', '', $cleaned);
        $cleaned = trim($cleaned);

        // If response looks like JSON, try to parse
        if (str_starts_with($cleaned, '{')) {
            $data = json_decode($cleaned, true);
            if (isset($data['response_message'])) {
                return $data['response_message'];
            }
            if (isset($data['question'])) {
                return $data['question'];
            }
        }

        // If response is too long or empty, use fallback
        if (empty($cleaned) || strlen($cleaned) > 200) {
            return match ($language) {
                'en' => "Got it! " . $fallbackTemplate,
                'hi', 'hinglish' => "Ji! " . $fallbackTemplate,
                default => $fallbackTemplate,
            };
        }

        return $cleaned;
    }

    /**
     * Check if user's message is asking for options/list rather than providing an answer
     * Examples: "konse he?", "options batao", "list do", "kya available hai?"
     */
    protected function isQuestionTypeResponse(string $message): bool
    {
        $message = strtolower(trim($message));

        // Common question-type patterns in Hindi/Hinglish/English
        $questionPatterns = [
            // Asking for list/options
            'konse he',
            'konse hai',
            'kaunse he',
            'kaunse hai',
            'kon se',
            'kaun se',
            'konsa he',
            'konsa hai',
            'kaunsa he',
            'kaunsa hai',
            'kon sa',
            'kaun sa',
            'options',
            'option',
            'list',
            'batao',
            'bata do',
            'dikhao',
            'dikha do',
            'available',
            'availabl',
            'kya hai',
            'kya he',
            'kya kya',
            'which one',
            'which ones',
            'what options',
            'what are',
            'show me',
            'tell me',
            'give me list',
            // Question markers
            'konsi',
            'kaunsi',
            'kitne',
            'kitni',
            'kitna',
            // Clarification requests
            'matlab',
            'meaning',
            'samjha',
            'samjhao',
            'explain',
            'example',
            'for example',
            'jaise',
            'jese',
        ];

        foreach ($questionPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        // Also check if message ends with question mark
        if (str_ends_with($message, '?')) {
            return true;
        }

        // Check if message is very short and likely a question word
        $questionWords = ['kya', 'kon', 'kaun', 'kis', 'kab', 'kaha', 'kaise', 'kyun', 'kitna', 'what', 'which', 'who', 'how', 'why', 'when', 'where'];
        $words = explode(' ', $message);
        if (count($words) <= 2 && in_array($words[0], $questionWords)) {
            return true;
        }

        return false;
    }

    /**
     * Get available options for a field from catalogue
     */
    protected function getFieldOptionsFromCatalogue(int $adminId, string $fieldName, Lead $lead): array
    {
        // Get collected data to apply filters
        $collectedData = $lead->collected_data ?? [];
        $workflowAnswers = $collectedData['workflow_questions'] ?? [];

        // Build catalogue query
        $query = \App\Models\Catalogue::where('admin_id', $adminId)
            ->where('is_active', true);

        // Apply progressive filters based on already-answered fields
        foreach ($workflowAnswers as $key => $value) {
            if (!empty($value)) {
                // Try to filter by this field in JSON data
                $query->where(function ($q) use ($key, $value) {
                    $jsonPath = '$.\"' . $key . '\"';
                    $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$value}%"]);
                });
            }
        }

        // Get catalogue items
        $items = $query->get();

        if ($items->isEmpty()) {
            return [];
        }

        // Extract unique values for the requested field
        $options = [];
        $fieldKeyLower = strtolower($fieldName);

        foreach ($items as $item) {
            $data = $item->data ?? [];
            foreach ($data as $key => $value) {
                if (strtolower($key) === $fieldKeyLower && !empty($value)) {
                    // Handle comma-separated values
                    $values = array_map('trim', explode(',', $value));
                    $options = array_merge($options, $values);
                }
            }
        }

        return array_unique(array_filter($options));
    }
}
