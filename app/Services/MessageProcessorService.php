<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Admin;
use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class MessageProcessorService
{
    protected Admin $admin;
    protected Customer $customer;
    protected ?Lead $lead;
    protected QuestionnaireService $questionnaireService;
    protected ActionProcessorService $actionProcessor;
    protected WorkflowExecutionService $workflowService;
    protected CatalogueMatchingService $catalogueService;
    protected ?AIService $aiService = null;

    public function __construct(Admin $admin, Customer $customer, ?Lead $lead = null)
    {
        $this->admin = $admin;
        $this->customer = $customer;

        // Ensure lead exists for workflow
        if (!$lead) {
            $lead = $customer->getOrCreateLead();
        }
        $this->lead = $lead;

        $this->questionnaireService = new QuestionnaireService($admin->id, $customer, $lead);
        $this->actionProcessor = new ActionProcessorService($admin->id, $customer);

        // Initialize workflow and catalogue services
        $this->workflowService = new WorkflowExecutionService($admin, $customer, $lead);
        $this->catalogueService = new CatalogueMatchingService($admin->id);
    }

    /**
     * Process incoming message and return response
     */
    public function process(string $message): array
    {
        // Step 1: Check for casual message
        if ($this->isCasualMessage($message)) {
            // Only send greeting for first-time users or after 24 hours of inactivity
            if ($this->shouldSendGreeting()) {
                // Mark that we've sent greeting
                $this->customer->update(['last_greeted_at' => now()]);
                return $this->handleCasualMessage($message);
            }
            // For regular casual messages (hi, ok, etc.), continue to workflow
            // Don't reply with greeting again
        }

        // Step 2: Check for catalogue/product intent
        if ($this->catalogueService->hasProductIntent($message)) {
            $matches = $this->catalogueService->findMatches($message);

            if ($matches->isNotEmpty()) {
                $productMessage = $this->catalogueService->formatProductList($matches);

                // Continue with workflow after showing products
                $workflowResponse = $this->workflowService->processMessage($message);

                return [
                    'message' => $productMessage . "\n\n" . $workflowResponse['message'],
                    'completed' => $workflowResponse['completed']
                ];
            }
        }

        // Step 3: Use workflow execution (always available now)
        return $this->workflowService->processMessage($message);
    }

    /**
     * Process with AI
     */
    protected function processWithAI(string $message): array
    {
        try {
            $this->aiService = new AIService();

            // Get context
            $context = [
                'history' => $this->getRecentHistory(10),
                'products' => $this->customer->products()->pending()->get(),
                'state' => $this->customer->getOrCreateState(),
            ];

            // Call AI
            $aiResponse = $this->aiService->processMessage($this->admin, $this->customer, $message, $context);

            if (!$aiResponse['success']) {
                // Fallback to simple processing
                return $this->processSimple($message);
            }

            // Update language if detected
            if ($aiResponse['language'] && $aiResponse['language'] !== $this->customer->detected_language) {
                $this->customer->update(['detected_language' => $aiResponse['language']]);
            }

            // Process confirmations
            if (!empty($aiResponse['confirmMsg'])) {
                $this->actionProcessor->process($aiResponse['confirmMsg'], $aiResponse['rejectionMsg'] ?? []);
            }

            // Get response message
            $responseMessage = $aiResponse['userLanguageMsg'] ?? $aiResponse['nextQuestion'] ?? null;

            if (!$responseMessage) {
                $next = $this->questionnaireService->getNextQuestionSmart();
                $responseMessage = $next['question'];
            }

            return [
                'type' => $aiResponse['intent'] ?? 'response',
                'message' => $responseMessage,
                'extracted' => $aiResponse['extractedFields'] ?? [],
                'confirmed' => $aiResponse['confirmMsg'] ?? [],
                'rejected' => $aiResponse['rejectionMsg'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('AI processing failed', ['error' => $e->getMessage()]);
            return $this->processSimple($message);
        }
    }

    /**
     * Simple processing without AI
     */
    protected function processSimple(string $message): array
    {
        $state = $this->customer->getOrCreateState();
        $currentField = $state->current_field;

        // If waiting for a specific field
        if ($currentField) {
            $result = $this->questionnaireService->processResponse($currentField, $message);
            $next = $this->questionnaireService->getNextQuestionSmart();

            // Update state with next field
            $state->current_field = $next['field'];
            $state->save();

            return [
                'type' => 'field_response',
                'message' => $next['question'],
                'field' => $next['field'],
                'action' => $result,
            ];
        }

        // Try to extract fields from message
        $extracted = $this->simpleExtract($message);

        if (!empty($extracted)) {
            foreach ($extracted as $field => $value) {
                $this->questionnaireService->processResponse($field, $value);
            }
        }

        // Get next question
        $next = $this->questionnaireService->getNextQuestionSmart();

        if ($next['field']) {
            $state->current_field = $next['field'];
            $state->save();
        }

        return [
            'type' => $next['type'],
            'message' => $next['question'],
            'field' => $next['field'],
            'extracted' => $extracted,
        ];
    }

    /**
     * Handle casual message
     */
    protected function handleCasualMessage(string $message): array
    {
        $language = $this->customer->detected_language ?? 'hi';

        // Detect language from message
        if (preg_match('/^(hi|hello|hey|good\s+(morning|evening|night))/i', $message)) {
            $language = 'en';
        }

        $responses = [
            'greeting' => [
                'hi' => 'Namaste! 🙏 Main aapki kya madad kar sakta hoon? Aapko kaunsa product chahiye?',
                'en' => 'Hello! 🙏 How can I help you? What product are you looking for?',
            ],
            'thanks' => [
                'hi' => 'Dhanyavaad! 🙏 Kuch aur chahiye toh jaroor batayein.',
                'en' => 'Thank you! 🙏 Let me know if you need anything else.',
            ],
            'ok' => [
                'hi' => 'Theek hai! Aage batayein kya chahiye aapko.',
                'en' => 'Okay! Please tell me what you need.',
            ],
        ];

        $type = 'greeting';
        if (preg_match('/(thank|shukriya|dhanyavaad)/i', $message)) {
            $type = 'thanks';
        } elseif (preg_match('/^(ok|okay|thik|theek)/i', $message)) {
            $type = 'ok';
        }

        return [
            'type' => 'casual',
            'message' => $responses[$type][$language] ?? $responses[$type]['hi'],
            'detected_type' => $type,
        ];
    }

    /**
     * Check if message is casual
     */
    protected function isCasualMessage(string $message): bool
    {
        $message = strtolower(trim($message));
        $patterns = [
            '/^(hi+|hello|hey|namaste|namaskar)/i',
            '/^(good\s+(morning|evening|night|afternoon))/i',
            '/^(ok+|okay|thik|theek)/i',
            '/^(thanks?|thank\s+you|shukriya|dhanyavaad)/i',
            '/^(jai\s+shree?\s+krishna)/i',
            '/^[👋🙏😊]+$/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if we should send greeting message
     * Only for first-time users or after 24 hours of inactivity
     */
    protected function shouldSendGreeting(): bool
    {
        // Check if customer has last_greeted_at field
        $lastGreeted = $this->customer->last_greeted_at;

        // Never greeted before = first time user
        if (empty($lastGreeted)) {
            return true;
        }

        // Greeted more than 24 hours ago
        $lastGreetedAt = \Carbon\Carbon::parse($lastGreeted);
        if ($lastGreetedAt->diffInHours(now()) >= 24) {
            return true;
        }

        return false;
    }

    /**
     * Simple field extraction (fallback)
     */
    protected function simpleExtract(string $message): array
    {
        $extracted = [];
        $message = strtolower($message);

        // Try to match catalogue items
        $catalogues = \App\Models\Catalogue::where('tenant_id', $this->admin->id)->get();

        foreach ($catalogues as $item) {
            $data = $item->data ?? [];

            // Check category
            $category = strtolower($data['category'] ?? $item->category ?? '');
            if ($category && str_contains($message, $category)) {
                $extracted['category'] = $data['category'] ?? $item->category;
            }

            // Check model
            $model = strtolower($data['model_code'] ?? $item->model ?? '');
            if ($model && strlen($model) >= 2 && str_contains($message, $model)) {
                $extracted['model'] = $data['model_code'] ?? $item->model;
            }
        }

        // Extract quantity
        if (preg_match('/(\d+)\s*(pcs|pieces?|pc|units?)/i', $message, $matches)) {
            $extracted['qty'] = (int) $matches[1];
        } elseif (preg_match('/qty\s*[:=]?\s*(\d+)/i', $message, $matches)) {
            $extracted['qty'] = (int) $matches[1];
        }

        // Extract size
        if (preg_match('/(\d+(?:\.\d+)?)\s*(inch|"|mm|cm)/i', $message, $matches)) {
            $extracted['size'] = $matches[1] . $matches[2];
        }

        return $extracted;
    }

    /**
     * Get recent chat history
     */
    protected function getRecentHistory(int $limit = 10): array
    {
        return \DB::table('chat_messages')
            ->where('tenant_id', $this->admin->id)
            ->where('customer_id', $this->customer->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['role', 'content', 'created_at'])
            ->reverse()
            ->values()
            ->toArray();
    }
}
