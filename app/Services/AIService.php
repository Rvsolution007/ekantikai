<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AiUsageLog;
use App\Models\Catalogue;
use App\Models\CatalogueField;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\ProductQuestion;
use App\Models\QuestionnaireNode;
use App\Models\QuestionTemplate;
use App\Models\Setting;
use App\Models\WhatsappChat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $provider;
    protected ?string $projectId;
    protected ?string $location;
    protected ?int $adminId = null;
    protected LanguageDetectionService $languageService;

    public function __construct()
    {
        // Load global AI settings from Super Admin
        $this->provider = Setting::getValue('global_ai_provider', 'google');
        $this->model = Setting::getValue('global_ai_model', 'gemini-2.0-flash');

        // Load Vertex AI specific settings from database
        $this->projectId = Setting::getValue('vertex_project_id', '');
        $this->location = Setting::getValue('vertex_region', 'asia-south1');

        // Check if Vertex AI is configured - if so, force use vertex provider
        $vertexPrivateKey = Setting::getValue('vertex_private_key', '');
        $vertexServiceEmail = Setting::getValue('vertex_service_email', '');

        if (!empty($this->projectId) && !empty($vertexPrivateKey) && !empty($vertexServiceEmail)) {
            // Vertex AI is fully configured, use it
            $this->provider = 'vertex';
            $this->apiKey = ''; // Not needed for Vertex AI (uses JWT)
        } else {
            // Fall back to API key-based providers
            $this->apiKey = $this->getApiKeyForProvider($this->provider);
        }

        $this->languageService = new LanguageDetectionService();
    }

    /**
     * Get API key for provider
     */
    protected function getApiKeyForProvider(string $provider): string
    {
        // Try database first, then fall back to config file (for hardcoded keys)
        return match ($provider) {
            'google' => Setting::getValue('gemini_api_key', '') ?: config('services.ai.api_key', ''),
            'openai' => Setting::getValue('openai_api_key', ''),
            'deepseek' => Setting::getValue('deepseek_api_key', ''),
            default => '',
        };
    }

    /**
     * Set admin for cost tracking
     */
    public function setAdmin(int $adminId): self
    {
        $this->adminId = $adminId;
        return $this;
    }

    /**
     * Process message with enhanced AI context
     * This is the main method that handles all the AI processing requirements
     */
    public function processMessageEnhanced(
        Admin $admin,
        Customer $customer,
        Lead $lead,
        string $message,
        array $options = []
    ): array {
        $this->adminId = $admin->id;

        // Build comprehensive context
        $context = $this->buildEnhancedContext($admin, $customer, $lead, $message, $options);

        // Detect language
        $detectedLanguage = $this->languageService->detect($message);
        $languageInstruction = $this->languageService->getLanguageInstruction($detectedLanguage);

        // Build system prompt with all context
        $systemPrompt = $this->buildEnhancedSystemPrompt($admin, $customer, $lead, $context, $languageInstruction);

        try {
            $response = $this->callAI($systemPrompt, $message);
            $parsed = $this->parseEnhancedResponse($response);

            // Add detected language
            $parsed['detected_language'] = $detectedLanguage;

            return $parsed;
        } catch (\Exception $e) {
            Log::error('AI Service error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback' => true,
                'detected_language' => $detectedLanguage,
            ];
        }
    }

    /**
     * Get system prompt preview (for admin viewing)
     * This generates the same prompt that would be sent to AI, but returns it for display
     */
    public function getSystemPromptPreview(Admin $admin): array
    {
        // Create a dummy customer and lead for preview (with fake IDs to prevent errors)
        $dummyCustomer = new Customer([
            'admin_id' => $admin->id,
            'phone' => '9999999999',
            'name' => 'Preview Customer',
            'detected_language' => 'hi',
            'global_fields' => [],
        ]);
        $dummyCustomer->id = 0; // Set fake ID to prevent null errors

        $dummyLead = new Lead([
            'admin_id' => $admin->id,
            'customer_id' => 0,
            'collected_data' => [],
            'product_confirmations' => [],
        ]);
        $dummyLead->id = 0; // Set fake ID

        // Build context manually for preview (skip conversation history)
        $context = [
            'reply' => [],
            'recent_conversation' => [], // Skip for preview
            'product_confirmations' => [], // Skip for preview
            'field_rules' => $this->getFlowchartRules($admin->id),
            'collected_data' => [],
            'customer_fields' => [],
            'catalogue' => $this->getCatalogueContext($admin->id),
        ];

        // Get language instruction
        $languageInstruction = $this->languageService->getLanguageInstruction('hi');

        // Build the actual system prompt
        $systemPrompt = $this->buildEnhancedSystemPrompt($admin, $dummyCustomer, $dummyLead, $context, $languageInstruction);

        return [
            'prompt' => $systemPrompt,
            'context' => [
                'field_rules' => $context['field_rules'] ?? [],
                'catalogue_products' => $context['catalogue']['total_products'] ?? 0,
            ],
            'catalogue' => $context['catalogue'] ?? [],
        ];
    }

    /**
     * Build enhanced context for AI
     */
    protected function buildEnhancedContext(
        Admin $admin,
        Customer $customer,
        Lead $lead,
        string $message,
        array $options = []
    ): array {
        $context = [];

        // Store IDs for fallback lookups
        $context['lead_id'] = $lead->id;
        $context['customer_id'] = $customer->id;
        $context['admin_id'] = $admin->id;
        // 8.1 Reply context
        if (isset($options['reply_to_content']) && $options['reply_to_content']) {
            $context['reply'] = [
                'original_message' => $options['reply_to_content'],
                'current_message' => $message,
            ];
        }

        // 8.2 Recent conversation (5 messages)
        $context['recent_conversation'] = WhatsappChat::getRecentConversation($customer->id, 5);

        // 8.3 Product confirmation memory
        $context['product_confirmations'] = $lead->getProductConfirmationsForAI();

        // 8.7 Flowchart field rules
        $context['field_rules'] = $this->getFlowchartRules($admin->id);

        // Lead collected data
        $context['collected_data'] = $lead->collected_data ?? [];

        // Customer global fields
        $context['customer_fields'] = $customer->global_fields ?? [];

        // *** Get actual catalogue data for this admin ***
        $context['catalogue'] = $this->getCatalogueContext($admin->id);

        // *** NEW: Find mentioned model from message or collected_data and fetch exact product ***
        $mentionedModels = $this->findMentionedModels($admin->id, $message, $context);
        if (!empty($mentionedModels)) {
            $context['mentioned_products'] = $mentionedModels;
        }

        // *** NEW: Find products by mentioned category - check message AND collected_data ***
        $categoryProducts = $this->findProductsByCategory($admin->id, $message, $context);
        if (!empty($categoryProducts)) {
            $context['category_products'] = $categoryProducts;
        }

        // *** NEW: Dynamic catalogue filtering based on confirmed workflow answers ***
        $filteredOptions = $this->getFilteredCatalogueOptions($admin->id, $context);
        if (!empty($filteredOptions)) {
            $context['available_options'] = $filteredOptions;
        }

        // *** NEW: Get PENDING flowchart questions (not yet answered) ***
        $pendingQuestions = $this->getPendingFlowchartQuestions($admin->id, $context);
        if (!empty($pendingQuestions)) {
            $context['pending_questions'] = $pendingQuestions;
        }

        return $context;
    }

    /**
     * Get flowchart questions that haven't been answered yet
     */
    protected function getPendingFlowchartQuestions(int $adminId, array $context): array
    {
        $fieldRules = $context['field_rules'] ?? [];
        $collectedData = $context['collected_data'] ?? [];
        $productConfirmations = $context['product_confirmations'] ?? [];
        $customerFields = $context['customer_fields'] ?? [];

        // Combine all answered fields
        $answeredFields = [];

        // From workflow_questions
        if (isset($collectedData['workflow_questions'])) {
            foreach ($collectedData['workflow_questions'] as $key => $value) {
                if (!empty($value)) {
                    $answeredFields[strtolower($key)] = $value;
                }
            }
        }

        // From global_questions
        if (isset($collectedData['global_questions'])) {
            foreach ($collectedData['global_questions'] as $key => $value) {
                if (!empty($value)) {
                    $answeredFields[strtolower($key)] = $value;
                }
            }
        }

        // From product_confirmations
        foreach ($productConfirmations as $conf) {
            foreach ($conf as $key => $value) {
                if (!empty($value) && !str_starts_with($key, '_')) {
                    $answeredFields[strtolower($key)] = $value;
                }
            }
        }

        // From customer global_fields
        foreach ($customerFields as $key => $value) {
            if (!empty($value)) {
                $answeredFields[strtolower($key)] = $value;
            }
        }

        // Find pending questions
        $pending = [];
        foreach ($fieldRules as $rule) {
            $fieldName = strtolower($rule['field_name'] ?? '');
            if (!isset($answeredFields[$fieldName])) {
                $pending[] = [
                    'field_name' => $rule['field_name'],
                    'display_name' => $rule['display_name'],
                    'is_required' => $rule['is_required'] ?? true,
                ];
            }
        }

        return $pending;
    }

    /**
     * Find products by ANY mentioned field value in message OR collected_data
     * FULLY DYNAMIC - works with ALL CatalogueFields, not hardcoded fields
     * When user mentions any value from catalogue (or mentioned earlier), returns all matching products
     */
    protected function findProductsByCategory(int $adminId, string $message, array $context = []): array
    {
        $messageLower = strtolower($message);
        $result = [];

        // Get ALL catalogue fields for this admin (dynamic - from CatalogueField table)
        $catalogueFields = CatalogueField::forTenant($adminId)->ordered()->get();

        if ($catalogueFields->isEmpty()) {
            return [];
        }

        // Get all catalogue items
        $catalogueItems = Catalogue::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        if ($catalogueItems->isEmpty()) {
            return [];
        }

        // Build a map of ALL unique values for EACH field
        // Structure: $fieldValuesMap[fieldKey][value] = [items array]
        $fieldValuesMap = [];
        foreach ($catalogueFields as $field) {
            $fieldValuesMap[$field->field_key] = [
                'field_name' => $field->field_name,
                'values' => [],
            ];
        }

        foreach ($catalogueItems as $item) {
            foreach ($catalogueFields as $field) {
                $value = $item->data[$field->field_key] ?? null;
                if ($value && !empty(trim($value))) {
                    $value = trim($value);
                    if (!isset($fieldValuesMap[$field->field_key]['values'][$value])) {
                        $fieldValuesMap[$field->field_key]['values'][$value] = [];
                    }
                    $fieldValuesMap[$field->field_key]['values'][$value][] = $item;
                }
            }
        }

        // Check if user is asking for model list/options - skip to fallback check
        $modelListKeywords = [
            'model list',
            'model ka list',
            'model konse',
            'kaunsa model',
            'कौनसा मॉडल',
            'model number list',
            'sab model',
            'all models',
            'model batao',
            'model bata',
            'which model',
            'konse model',
            'kitne model',
            'model hai',
            'available model',
            'model options',
            'model dikhao'
        ];
        $askingForModels = false;
        foreach ($modelListKeywords as $keyword) {
            if (stripos($messageLower, $keyword) !== false) {
                $askingForModels = true;
                Log::info('User asking for model list', ['keyword_matched' => $keyword, 'message' => $message]);
                break;
            }
        }

        // FIRST: Check if any field value is mentioned in CURRENT message
        foreach ($fieldValuesMap as $fieldKey => $fieldData) {
            foreach ($fieldData['values'] as $value => $matchingItems) {
                if (stripos($messageLower, strtolower($value)) !== false) {
                    // Found a match! Now get ALL other field values from matching products
                    $otherFieldValues = [];

                    foreach ($catalogueFields as $otherField) {
                        $otherKey = $otherField->field_key;
                        if ($otherKey === $fieldKey)
                            continue; // Skip the matched field

                        $uniqueValues = [];
                        foreach ($matchingItems as $item) {
                            $otherValue = $item->data[$otherKey] ?? null;
                            if ($otherValue && !empty(trim($otherValue))) {
                                $uniqueValues[] = trim($otherValue);
                            }
                        }

                        if (!empty($uniqueValues)) {
                            $otherFieldValues[$otherKey] = [
                                'field_name' => $otherField->field_name,
                                'values' => array_unique($uniqueValues),
                            ];
                        }
                    }

                    $result = [
                        'matched_field' => $fieldData['field_name'],
                        'matched_value' => $value,
                        'matching_count' => count($matchingItems),
                        'related_fields' => $otherFieldValues,
                        'sample_products' => array_slice(array_map(fn($i) => $i->data, $matchingItems), 0, 15),
                    ];

                    Log::info('Dynamic catalogue match found', [
                        'admin_id' => $adminId,
                        'matched_field' => $fieldData['field_name'],
                        'matched_value' => $value,
                        'matching_count' => count($matchingItems),
                        'related_fields_count' => count($otherFieldValues),
                    ]);

                    return $result; // Return first match
                }
            }
        }

        // FALLBACK: Check multiple sources when:
        // 1. No direct match found in message, OR
        // 2. User is asking for model list/options (trigger regardless of direct match)
        if ((empty($result) || $askingForModels) && !empty($context)) {
            $collectedCategories = [];

            // 1. Check workflow_questions (using all keys, not just 'category')
            $workflowData = $context['collected_data']['workflow_questions'] ?? [];
            foreach ($workflowData as $key => $value) {
                if (!empty($value)) {
                    $collectedCategories[$key] = $value;
                }
            }

            // 2. Check product_confirmations - check ALL field values, not just 'category'
            $confirmations = $context['collected_data']['product_confirmations'] ?? [];
            foreach ($confirmations as $conf) {
                foreach ($conf as $fieldKey => $fieldValue) {
                    if (!empty($fieldValue) && $fieldValue !== '-' && !in_array($fieldKey, ['qty', 'quantity'])) {
                        $collectedCategories[$fieldKey] = $fieldValue;
                    }
                }
            }

            // 3. Check LeadProducts table for this lead
            if (!empty($context['lead_id'])) {
                $leadProducts = \App\Models\LeadProduct::where('lead_id', $context['lead_id'])->get();
                foreach ($leadProducts as $product) {
                    if (!empty($product->category)) {
                        $collectedCategories['category'] = $product->category;
                    }
                    if (!empty($product->product)) {
                        $collectedCategories['product'] = $product->product;
                    }
                }
            }

            // 4. Check recent chat messages for ANY catalogue value mentions
            if (!empty($context['customer_id'])) {
                $recentChats = \App\Models\WhatsappChat::where('customer_id', $context['customer_id'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->pluck('content')
                    ->toArray();

                $allChatText = strtolower(implode(' ', $recentChats));

                // Check if any known catalogue value is mentioned in recent chats
                foreach ($fieldValuesMap as $fieldKey => $fieldData) {
                    foreach (array_keys($fieldData['values']) as $catalogueValue) {
                        if (stripos($allChatText, strtolower($catalogueValue)) !== false) {
                            $collectedCategories[$fieldKey] = $catalogueValue;
                            break 2; // Found a match, stop searching
                        }
                    }
                }
            }

            // Now check if any collected value exists in our field map
            foreach ($collectedCategories as $key => $value) {
                foreach ($fieldValuesMap as $fieldKey => $fieldData) {
                    if (isset($fieldData['values'][$value])) {
                        $matchingItems = $fieldData['values'][$value];

                        // Build the related fields data
                        $otherFieldValues = [];
                        foreach ($catalogueFields as $otherField) {
                            $otherKey = $otherField->field_key;
                            if ($otherKey === $fieldKey)
                                continue;

                            $uniqueValues = [];
                            foreach ($matchingItems as $item) {
                                $otherValue = $item->data[$otherKey] ?? null;
                                if ($otherValue && !empty(trim($otherValue))) {
                                    $uniqueValues[] = trim($otherValue);
                                }
                            }

                            if (!empty($uniqueValues)) {
                                $otherFieldValues[$otherKey] = [
                                    'field_name' => $otherField->field_name,
                                    'values' => array_unique($uniqueValues),
                                ];
                            }
                        }

                        Log::info('Found category from fallback sources', [
                            'source_key' => $key,
                            'matched_value' => $value,
                            'matching_count' => count($matchingItems),
                        ]);

                        return [
                            'matched_field' => $fieldData['field_name'],
                            'matched_value' => $value,
                            'matching_count' => count($matchingItems),
                            'related_fields' => $otherFieldValues,
                            'sample_products' => array_slice(array_map(fn($i) => $i->data, $matchingItems), 0, 15),
                            'from_collected_data' => true,
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get filtered catalogue options based on confirmed workflow answers
     * This filters the catalogue progressively as user confirms each field
     */
    protected function getFilteredCatalogueOptions(int $adminId, array $context): array
    {
        // Get all catalogue fields for this admin
        $catalogueFields = CatalogueField::forTenant($adminId)->ordered()->get();

        if ($catalogueFields->isEmpty()) {
            return [];
        }

        // Get confirmed values from workflow_questions
        $confirmedValues = [];

        // Check workflow_questions in collected_data
        $workflowQuestions = $context['collected_data']['workflow_questions'] ?? [];
        foreach ($workflowQuestions as $fieldKey => $value) {
            if (!empty($value)) {
                $confirmedValues[$fieldKey] = $value;
            }
        }

        // Also check global_questions
        $globalQuestions = $context['collected_data']['global_questions'] ?? [];
        foreach ($globalQuestions as $fieldKey => $value) {
            if (!empty($value)) {
                $confirmedValues[$fieldKey] = $value;
            }
        }

        // Also check customer global_fields
        foreach ($context['customer_fields'] ?? [] as $fieldKey => $value) {
            if (!empty($value) && !isset($confirmedValues[$fieldKey])) {
                $confirmedValues[$fieldKey] = $value;
            }
        }

        if (empty($confirmedValues)) {
            // No filters yet, return all unique values for first field
            return $this->getAllCatalogueOptionsForFields($adminId, $catalogueFields);
        }

        // Build filter query based on confirmed values
        $query = Catalogue::where('admin_id', $adminId)->where('is_active', true);

        foreach ($confirmedValues as $fieldKey => $value) {
            // Filter catalogue where this field matches the confirmed value
            // Quote field key properly for JSON path (handles spaces in field names)
            $jsonPath = '$."' . $fieldKey . '"';
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) = ?", [$jsonPath, $value]);
        }

        // Get filtered catalogue items
        $filteredItems = $query->get();

        if ($filteredItems->isEmpty()) {
            return ['no_matching_products' => true, 'confirmed_filters' => $confirmedValues];
        }

        // Extract unique values for each remaining field from filtered catalogue
        $availableOptions = [];

        foreach ($catalogueFields as $field) {
            $fieldKey = $field->field_key;

            // Skip already confirmed fields
            if (isset($confirmedValues[$fieldKey])) {
                $availableOptions[$fieldKey] = [
                    'field_name' => $field->field_name,
                    'confirmed_value' => $confirmedValues[$fieldKey],
                    'is_confirmed' => true,
                ];
                continue;
            }

            // Get unique values for this field from filtered catalogue
            $uniqueValues = $filteredItems->map(function ($item) use ($fieldKey) {
                return $item->data[$fieldKey] ?? null;
            })->filter()->unique()->values()->toArray();

            if (!empty($uniqueValues)) {
                $availableOptions[$fieldKey] = [
                    'field_name' => $field->field_name,
                    'available_values' => $uniqueValues,
                    'is_confirmed' => false,
                ];
            }
        }

        $availableOptions['matching_products_count'] = $filteredItems->count();

        Log::debug('Filtered catalogue options', [
            'admin_id' => $adminId,
            'confirmed_values' => $confirmedValues,
            'available_options' => $availableOptions,
        ]);

        return $availableOptions;
    }

    /**
     * Get all unique values for each catalogue field (no filters applied)
     */
    protected function getAllCatalogueOptionsForFields(int $adminId, $catalogueFields): array
    {
        $allItems = Catalogue::where('admin_id', $adminId)->where('is_active', true)->get();

        if ($allItems->isEmpty()) {
            return ['no_products_in_catalogue' => true];
        }

        $options = [];

        foreach ($catalogueFields as $field) {
            $fieldKey = $field->field_key;

            $uniqueValues = $allItems->map(function ($item) use ($fieldKey) {
                return $item->data[$fieldKey] ?? null;
            })->filter()->unique()->values()->toArray();

            if (!empty($uniqueValues)) {
                $options[$fieldKey] = [
                    'field_name' => $field->field_name,
                    'available_values' => $uniqueValues,
                    'is_confirmed' => false,
                ];
            }
        }

        $options['total_products'] = $allItems->count();

        return $options;
    }

    /**
     * Find mentioned models from message and collected_data, then fetch their exact catalogue data
     * UPDATED: Now reads from dynamic JSON 'data' field instead of static model_code column
     */
    protected function findMentionedModels(int $adminId, string $message, array $context): array
    {
        $mentionedProducts = [];

        // Get catalogue fields that might contain model info
        $catalogueFields = CatalogueField::forTenant($adminId)->ordered()->get();
        $modelFieldKeys = [];
        foreach ($catalogueFields as $field) {
            $keyLower = strtolower($field->field_key);
            if (str_contains($keyLower, 'model') || str_contains($keyLower, 'number') || str_contains($keyLower, 'code')) {
                $modelFieldKeys[] = $field->field_key;
            }
        }

        // Get all catalogue items for this admin
        $allCatalogueItems = Catalogue::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        // Extract all model values from JSON data
        $allModels = [];
        foreach ($allCatalogueItems as $item) {
            foreach ($modelFieldKeys as $fieldKey) {
                $modelValue = $item->data[$fieldKey] ?? null;
                if ($modelValue && !empty(trim($modelValue))) {
                    $allModels[trim($modelValue)] = $item;
                }
            }
            // Also try legacy model_code column as fallback
            if (!empty($item->model_code)) {
                $allModels[trim($item->model_code)] = $item;
            }
        }

        // Check if any model is mentioned in the current message
        $messageLower = strtolower($message);
        foreach ($allModels as $model => $item) {
            if (stripos($messageLower, strtolower($model)) !== false) {
                $mentionedProducts[$model] = [
                    'model' => $model,
                    'data' => $item->data ?? [],
                    // Also include legacy fields for backward compatibility
                    'product_type' => $item->data['product_category'] ?? $item->product_type ?? null,
                    'category' => $item->category ?? null,
                    'sizes' => $item->data['size'] ?? $item->sizes ?? null,
                    'finishes' => $item->data['finish_color'] ?? $item->finishes ?? null,
                ];
                Log::debug('Found mentioned model in message', ['model' => $model, 'data' => $mentionedProducts[$model]]);
            }
        }

        // Also check model from collected_data (global_questions or product_confirmations)
        $collectedModel = $context['collected_data']['global_questions']['model'] ??
            $context['collected_data']['global_questions']['model_number'] ??
            $context['customer_fields']['model'] ??
            $context['customer_fields']['model_number'] ?? null;

        if ($collectedModel && !isset($mentionedProducts[$collectedModel])) {
            $model = trim($collectedModel);
            if (isset($allModels[$model])) {
                $item = $allModels[$model];
                $mentionedProducts[$model] = [
                    'model' => $model,
                    'data' => $item->data ?? [],
                    'product_type' => $item->data['product_category'] ?? $item->product_type ?? null,
                    'sizes' => $item->data['size'] ?? $item->sizes ?? null,
                    'finishes' => $item->data['finish_color'] ?? $item->finishes ?? null,
                ];
                Log::debug('Found model from collected_data', ['model' => $model, 'data' => $mentionedProducts[$model]]);
            }
        }

        return $mentionedProducts;
    }

    /**
     * Get catalogue context for AI (product categories, types, sample models)
     * UPDATED: Now reads from dynamic JSON 'data' field instead of static columns
     */
    protected function getCatalogueContext(int $adminId): array
    {
        // Get all catalogue items with data
        $catalogueItems = Catalogue::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        $totalProducts = $catalogueItems->count();

        Log::debug('getCatalogueContext called', [
            'admin_id' => $adminId,
            'total_active_products' => $totalProducts,
        ]);

        if ($totalProducts === 0) {
            return [
                'product_types' => [],
                'categories' => [],
                'sample_models' => [],
                'sample_products' => [],
                'total_products' => 0,
                'field_options' => [],
            ];
        }

        // Get catalogue fields for this admin
        $catalogueFields = CatalogueField::forTenant($adminId)->ordered()->get();

        // Extract unique values for each field from the JSON data
        $fieldOptions = [];
        $productTypes = [];
        $categories = [];
        $sampleModels = [];

        foreach ($catalogueFields as $field) {
            $fieldKey = $field->field_key;
            $uniqueValues = $catalogueItems->map(function ($item) use ($fieldKey) {
                return $item->data[$fieldKey] ?? null;
            })->filter()->unique()->values()->toArray();

            if (!empty($uniqueValues)) {
                $fieldOptions[$fieldKey] = [
                    'name' => $field->field_name,
                    'values' => $uniqueValues,
                ];

                // Try to identify category/product_type/model fields
                $keyLower = strtolower($fieldKey);
                if (str_contains($keyLower, 'category') || str_contains($keyLower, 'product_type') || str_contains($keyLower, 'type')) {
                    $productTypes = array_merge($productTypes, $uniqueValues);
                }
                if (str_contains($keyLower, 'model') || str_contains($keyLower, 'code') || str_contains($keyLower, 'number')) {
                    $sampleModels = array_merge($sampleModels, array_slice($uniqueValues, 0, 20));
                }
            }
        }

        $productTypes = array_unique($productTypes);
        $sampleModels = array_unique($sampleModels);

        // Get sample products with ALL their data (first 15)
        $sampleProducts = $catalogueItems->take(15)->map(function ($item) {
            return $item->data ?? [];
        })->toArray();

        Log::debug('getCatalogueContext result', [
            'admin_id' => $adminId,
            'field_options_count' => count($fieldOptions),
            'product_types' => $productTypes,
            'sample_models_count' => count($sampleModels),
            'sample_products' => array_slice($sampleProducts, 0, 3), // Log first 3 for debug
        ]);

        return [
            'product_types' => array_values($productTypes),
            'categories' => $categories,
            'sample_models' => array_values($sampleModels),
            'sample_products' => $sampleProducts,
            'total_products' => $totalProducts,
            'field_options' => $fieldOptions,
        ];
    }

    /**
     * Get flowchart rules for AI context
     * CRITICAL: Traverses from Start node following connections to get correct order
     * This ensures any flowchart changes are reflected in bot's question order
     */
    protected function getFlowchartRules(int $adminId): array
    {
        // Get full ProductQuestion data including is_unique_key and is_qty_field
        $productQuestions = ProductQuestion::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get()
            ->keyBy(function ($item) {
                return strtolower($item->field_name);
            });

        // TRAVERSE FLOWCHART FROM START NODE TO GET CORRECT ORDER
        $orderedNodes = $this->traverseFlowchartForQuestions($adminId);

        $rules = [];
        foreach ($orderedNodes as $node) {
            $field = $node->questionnaireField;
            if ($field) {
                $fieldName = $field->field_name;
                $fieldNameLower = strtolower($fieldName);

                // Get ProductQuestion data for this field
                $pq = $productQuestions[$fieldNameLower] ?? null;
                $isUniqueKey = $pq->is_unique_key ?? false;
                $isQtyField = $pq->is_qty_field ?? false;

                // Determine field behavior
                $fieldBehavior = $this->getFieldBehavior($isUniqueKey, $isQtyField);

                $rules[] = [
                    'field_name' => $fieldName,
                    'display_name' => $field->display_name,
                    'is_required' => $node->is_required,
                    'is_optional' => !$node->is_required,
                    'ask_digit' => $node->ask_digit,
                    'is_unique_field' => $node->is_unique_field,
                    'is_unique_key' => $isUniqueKey,
                    'is_qty_field' => $isQtyField,
                    'field_behavior' => $fieldBehavior,
                    'field_type' => $field->field_type,
                    // Add question template from ProductQuestion for AI to enhance
                    'question_template' => $pq->question_template ?? null,
                ];
            }
        }

        return $rules;
    }

    /**
     * Traverse flowchart from Start node to collect question nodes in order
     * Follows connections to determine the actual flowchart sequence
     */
    protected function traverseFlowchartForQuestions(int $adminId): array
    {
        $startNode = QuestionnaireNode::getStartNode($adminId);
        if (!$startNode) {
            // Fallback: return nodes ordered by ProductQuestion.sort_order if no start node
            return QuestionnaireNode::where('questionnaire_nodes.admin_id', $adminId)
                ->where('questionnaire_nodes.is_active', true)
                ->where('questionnaire_nodes.node_type', QuestionnaireNode::TYPE_QUESTION)
                ->leftJoin('product_questions', 'questionnaire_nodes.questionnaire_field_id', '=', 'product_questions.id')
                ->orderBy('product_questions.sort_order', 'asc')
                ->select('questionnaire_nodes.*')
                ->with('questionnaireField')
                ->get()
                ->all();
        }

        $orderedQuestions = [];
        $visited = [];
        $current = $startNode;
        $maxIterations = 50; // Prevent infinite loops
        $iteration = 0;

        while ($current && $iteration < $maxIterations) {
            $iteration++;

            // Avoid infinite loops
            if (isset($visited[$current->id])) {
                break;
            }
            $visited[$current->id] = true;

            // If current is a question node, add it to our ordered list
            if ($current->node_type === QuestionnaireNode::TYPE_QUESTION && $current->is_active) {
                // Load the relationship if not already loaded
                if (!$current->relationLoaded('questionnaireField')) {
                    $current->load('questionnaireField');
                }
                $orderedQuestions[] = $current;
            }

            // Get next node
            $current = $current->getNextNode();
        }

        return $orderedQuestions;
    }

    /**
     * Determine field behavior based on ProductQuestion settings
     * @return string 'ask_options' | 'ask_input' | 'inform_availability'
     */
    protected function getFieldBehavior(bool $isUniqueKey, bool $isQtyField): string
    {
        if ($isQtyField) {
            return 'ask_input'; // Always ask for qty input
        }
        return $isUniqueKey ? 'ask_options' : 'inform_availability';
    }

    /**
     * Build enhanced system prompt with all context
     */
    protected function buildEnhancedSystemPrompt(
        Admin $admin,
        Customer $customer,
        Lead $lead,
        array $context,
        string $languageInstruction
    ): string {
        $tenantName = $admin->company_name ?? $admin->name ?? 'Datsun Hardware';

        // Get AI settings - ai_system_prompt from Admin model (per-admin), others from global
        $aiTone = Setting::getValue('ai_tone', 'friendly');
        $customSystemPrompt = $admin->ai_system_prompt ?? Setting::getValue('ai_system_prompt', '');
        $maxLength = Setting::getValue('ai_max_length', 'medium');

        // Tone instructions based on setting
        $toneInstruction = match ($aiTone) {
            'professional' => 'Be formal, polite, and professional. Use respectful language (आप/आपका). Avoid casual slang.',
            'casual' => 'Be very casual and friendly like talking to a friend. Use informal language (तू/तुम). Use slang freely.',
            'friendly' => 'Be warm, helpful and approachable. Use polite but relaxed language (आप). Be conversational.',
            default => 'Be warm, helpful and approachable.',
        };

        // Response length instruction
        $lengthInstruction = match ($maxLength) {
            'short' => 'Keep responses very brief: 10-30 words maximum.',
            'long' => 'Provide detailed responses: 80-150 words when explaining.',
            default => 'Keep responses moderate: 30-60 words for explanations.',
        };

        // Get lead statuses for this admin
        $leadStatuses = \App\Models\LeadStatus::where('admin_id', $admin->id)
            ->active()
            ->ordered()
            ->pluck('name')
            ->toArray();
        $statusList = implode(', ', $leadStatuses);

        // Format context sections
        $replyContext = '';
        if (!empty($context['reply'])) {
            $replyContext = "## REPLY CONTEXT\nUser is replying to: \"{$context['reply']['original_message']}\"\nCurrent message: \"{$context['reply']['current_message']}\"\n";
        }

        $recentConv = '';
        if (!empty($context['recent_conversation'])) {
            $recentConv = "## RECENT CONVERSATION (last 5 messages)\n";
            foreach ($context['recent_conversation'] as $msg) {
                $role = $msg['role'] === 'user' ? 'User' : 'Bot';
                $recentConv .= "{$role}: {$msg['content']}\n";
            }
        }

        $productMemory = '';
        if (!empty($context['product_confirmations'])) {
            $productMemory = "## CONFIRMED PRODUCTS\n" . json_encode($context['product_confirmations'], JSON_PRETTY_PRINT) . "\n";
        }

        $fieldRules = '';
        $allowedFieldNames = [];
        if (!empty($context['field_rules'])) {
            $fieldRules = "## 🔴 CRITICAL: ALLOWED FIELDS (FLOWCHART ONLY)\n";
            $fieldRules .= "⚠️ YOU CAN ONLY ASK ABOUT THESE FIELDS - NO EXCEPTIONS:\n\n";
            foreach ($context['field_rules'] as $rule) {
                $type = $rule['is_required'] ? 'REQUIRED' : 'OPTIONAL';
                $unique = $rule['is_unique_field'] ? ' [UNIQUE IDENTIFIER]' : '';
                $askDigit = $rule['ask_digit'] > 0 ? " (ask max {$rule['ask_digit']} times)" : '';
                $template = $rule['question_template'] ?? null;
                $behavior = $rule['field_behavior'] ?? 'ask_options';

                // Behavior indicator
                $behaviorLabel = match ($behavior) {
                    'ask_input' => '[📦 QTY - ASK INPUT]',
                    'inform_availability' => '[ℹ️ INFORM ONLY]',
                    default => '[🔑 ASK OPTIONS]',
                };

                $fieldRules .= "✓ {$rule['display_name']} ({$rule['field_name']}): {$type}{$unique}{$askDigit} {$behaviorLabel}\n";

                // Include question template for AI to enhance
                if (!empty($template)) {
                    $fieldRules .= "   → ASK LIKE THIS (enhance naturally): \"{$template}\"\n";
                }

                $allowedFieldNames[] = $rule['field_name'];
            }

            // Add behavior-based instructions
            $fieldRules .= "\n### 📋 FIELD BEHAVIOR RULES:\n\n";

            $fieldRules .= "**[🔑 ASK OPTIONS] - For is_unique_key=true fields:**\n";
            $fieldRules .= "- Show available options from catalogue to user\n";
            $fieldRules .= "- Ask: \"Kaunsa chahiye? [option1], [option2], [option3]...\"\n";
            $fieldRules .= "- Wait for user to select one option\n\n";

            $fieldRules .= "**[📦 QTY - ASK INPUT] - For is_qty_field=true fields:**\n";
            $fieldRules .= "- Always ASK user for quantity input\n";
            $fieldRules .= "- Ask: \"Kitni qty chahiye?\" or \"Quantity batao\"\n";
            $fieldRules .= "- Accept any number as valid response\n\n";

            $fieldRules .= "**[ℹ️ INFORM ONLY] - For fields without unique_key/qty:**\n";
            $fieldRules .= "- DO NOT ask the user to choose\n";
            $fieldRules .= "- INFORM them: \"Hmare pas [field] me [value] available he\"\n";
            $fieldRules .= "- Wait for positive confirmation (ha, ok, theek hai, chalega)\n";
            $fieldRules .= "- Then move to next field\n\n";

            $fieldRules .= "### ✅ POSITIVE CONFIRMATION DETECTION:\n";
            $fieldRules .= "Accept these as positive confirmations:\n";
            $fieldRules .= "- Hindi: ha, haan, ji, theek hai, thik he, chalega, sahi hai\n";
            $fieldRules .= "- English: yes, ok, okay, fine, alright, sure, correct, right\n";
            $fieldRules .= "- Hinglish: ok hai, theek, done, confirm\n\n";

            $fieldRules .= "### 🚫 FORBIDDEN FIELDS (NEVER ASK THESE IF NOT IN LIST ABOVE):\n";
            $fieldRules .= "- Any field NOT listed above\n";
            $fieldRules .= "\n### QUESTION ENHANCEMENT RULES:\n";
            $fieldRules .= "- When asking a question, use the template provided as REFERENCE\n";
            $fieldRules .= "- Make it sound natural and friendly, like a real salesperson\n";
            $fieldRules .= "- Keep the same meaning as the template\n";
            $fieldRules .= "- Add brief acknowledgment of user's message if relevant\n";
        }

        // *** NEW: Build pending questions section ***
        $pendingQuestionsSection = '';
        if (!empty($context['pending_questions'])) {
            $firstPending = $context['pending_questions'][0] ?? null;

            // CRITICAL: Tell AI which field we're expecting answer for
            if ($firstPending) {
                $pendingQuestionsSection = "\n## 🎯 CURRENT QUESTION - EXPECTING ANSWER FOR:\n";
                $pendingQuestionsSection .= "**Field: {$firstPending['field_name']}** ({$firstPending['display_name']})\n";
                $pendingQuestionsSection .= "⚠️ CRITICAL: If user gives ANY answer now, save it to '{$firstPending['field_name']}' in extracted_data!\n";
                $pendingQuestionsSection .= "Example: User says '15 or 20' → extracted_data.{$firstPending['field_name']} = '15 or 20'\n\n";
            }

            $pendingQuestionsSection .= "## PENDING QUESTIONS (in order):\n";
            foreach ($context['pending_questions'] as $i => $q) {
                $num = $i + 1;
                $required = $q['is_required'] ? '[REQUIRED]' : '[OPTIONAL]';
                $current = ($i === 0) ? ' ← CURRENT (waiting for answer)' : '';
                $pendingQuestionsSection .= "{$num}. {$q['display_name']} ({$q['field_name']}) {$required}{$current}\n";
            }
            $pendingQuestionsSection .= "\n### MAPPING RULES:\n";
            $pendingQuestionsSection .= "- User's answer goes to the FIRST pending field ({$firstPending['field_name']})\n";
            $pendingQuestionsSection .= "- After saving, ask the NEXT pending question\n";
            $pendingQuestionsSection .= "- DO NOT repeat the question that was just answered\n";
        } else {
            $pendingQuestionsSection = "\n## ✅ ALL QUESTIONS ANSWERED\n";
            $pendingQuestionsSection .= "All flowchart questions have been answered. Order is complete.\n";
        }

        $collectedData = json_encode($context['collected_data'] ?? [], JSON_PRETTY_PRINT);

        // Custom personality from admin settings
        $customPersonality = $customSystemPrompt ? "\n## CUSTOM PERSONALITY\n{$customSystemPrompt}\n" : '';

        // *** NEW: Format catalogue data for AI ***
        $catalogueSection = '';
        if (!empty($context['catalogue'])) {
            $cat = $context['catalogue'];
            $catalogueSection = "## YOUR PRODUCT CATALOGUE\n";
            $catalogueSection .= "IMPORTANT: Only mention products from this list. Do NOT make up products.\n\n";

            // Show field options from dynamic JSON data
            if (!empty($cat['field_options'])) {
                $catalogueSection .= "### Available Options by Field:\n";
                foreach ($cat['field_options'] as $fieldKey => $fieldData) {
                    $values = array_slice($fieldData['values'], 0, 30); // Limit to 30 values
                    $catalogueSection .= "• {$fieldData['name']}: " . implode(', ', $values) . "\n";
                }
                $catalogueSection .= "\n";
            }

            if (!empty($cat['product_types'])) {
                $catalogueSection .= "Product Types Available: " . implode(', ', $cat['product_types']) . "\n";
            }
            if (!empty($cat['sample_models'])) {
                $catalogueSection .= "Sample Model Codes: " . implode(', ', array_slice($cat['sample_models'], 0, 25)) . "\n";
            }

            // Show sample products with all their data
            if (!empty($cat['sample_products'])) {
                $catalogueSection .= "\nSample Products (first 5):\n";
                foreach (array_slice($cat['sample_products'], 0, 5) as $product) {
                    if (!empty($product)) {
                        $details = [];
                        foreach ($product as $key => $value) {
                            if (!empty($value)) {
                                $details[] = "{$key}: {$value}";
                            }
                        }
                        if (!empty($details)) {
                            $catalogueSection .= "- " . implode(', ', $details) . "\n";
                        }
                    }
                }
            }
            $catalogueSection .= "\nTotal Products in Catalogue: {$cat['total_products']}\n";
        }

        // *** NEW: Add CURRENT PRODUCT section if model is mentioned ***
        $currentProductSection = '';
        if (!empty($context['mentioned_products'])) {
            $currentProductSection = "\n## CURRENT PRODUCT CONTEXT (USE THIS DATA)\n";
            $currentProductSection .= "CRITICAL: The customer is asking about these specific products. Use ONLY these exact options:\n\n";
            foreach ($context['mentioned_products'] as $model => $data) {
                $currentProductSection .= "### Model: {$model}\n";

                // Show ALL data from JSON field
                if (!empty($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $key => $value) {
                        if (!empty($value)) {
                            $displayKey = ucwords(str_replace('_', ' ', $key));
                            $currentProductSection .= "- {$displayKey}: {$value}\n";
                        }
                    }
                } else {
                    // Fallback to legacy fields
                    if (!empty($data['product_type']))
                        $currentProductSection .= "- Product Type: {$data['product_type']}\n";
                    if (!empty($data['sizes']))
                        $currentProductSection .= "- Available Sizes: {$data['sizes']}\n";
                    if (!empty($data['finishes']))
                        $currentProductSection .= "- Available Finishes: {$data['finishes']}\n";
                }
                $currentProductSection .= "\n";
            }
            $currentProductSection .= "IMPORTANT: When asking about size or finish for these models, ONLY offer the options listed above. Do NOT make up options.\n";
        }

        // *** NEW: Add AVAILABLE OPTIONS section from filtered catalogue ***
        $availableOptionsSection = '';
        if (!empty($context['available_options'])) {
            $opts = $context['available_options'];

            // Check if no products match
            if (isset($opts['no_matching_products']) && $opts['no_matching_products']) {
                $availableOptionsSection = "\n## CATALOGUE STATUS\n";
                $availableOptionsSection .= "WARNING: No products match the customer's selected criteria. Inform customer politely.\n";
            } elseif (isset($opts['no_products_in_catalogue']) && $opts['no_products_in_catalogue']) {
                $availableOptionsSection = "\n## CATALOGUE STATUS\n";
                $availableOptionsSection .= "NOTE: Catalogue is empty. Ask customer for their requirements and note them.\n";
            } else {
                $availableOptionsSection = "\n## AVAILABLE OPTIONS FROM CATALOGUE (USE ONLY THESE)\n";
                $availableOptionsSection .= "CRITICAL: When asking about any field below, ONLY offer the options listed. Do NOT invent options.\n\n";

                foreach ($opts as $fieldKey => $fieldData) {
                    // Skip meta fields
                    if (in_array($fieldKey, ['matching_products_count', 'total_products', 'no_matching_products', 'no_products_in_catalogue'])) {
                        continue;
                    }

                    if (is_array($fieldData) && isset($fieldData['field_name'])) {
                        if (isset($fieldData['is_confirmed']) && $fieldData['is_confirmed']) {
                            $availableOptionsSection .= "✓ {$fieldData['field_name']}: CONFIRMED = {$fieldData['confirmed_value']}\n";
                        } elseif (isset($fieldData['available_values']) && !empty($fieldData['available_values'])) {
                            $values = implode(', ', $fieldData['available_values']);
                            $availableOptionsSection .= "• {$fieldData['field_name']}: Available options = [{$values}]\n";
                        }
                    }
                }

                if (isset($opts['matching_products_count'])) {
                    $availableOptionsSection .= "\nMatching Products: {$opts['matching_products_count']} products available\n";
                }

                $availableOptionsSection .= "\nIMPORTANT: When asking about any field above, list ALL available options to customer. Never make up options not in the list.\n";
            }
        }

        // *** DYNAMIC: Add matching products section when user asks about ANY catalogue value ***
        $categoryProductsSection = '';
        if (!empty($context['category_products'])) {
            $catData = $context['category_products'];
            $matchedField = $catData['matched_field'] ?? 'Category';
            $matchedValue = $catData['matched_value'] ?? '';
            $matchingCount = $catData['matching_count'] ?? 0;
            $relatedFields = $catData['related_fields'] ?? [];

            $categoryProductsSection = "\n## ⚡ PRODUCTS MATCHING '{$matchedValue}' ({$matchedField})\n";
            $categoryProductsSection .= "**CRITICAL:** Customer mentioned '{$matchedValue}'. Found {$matchingCount} matching products.\n\n";

            // Show ALL related field values dynamically
            if (!empty($relatedFields)) {
                $categoryProductsSection .= "**AVAILABLE OPTIONS (GIVE THESE IMMEDIATELY):**\n";
                foreach ($relatedFields as $fieldKey => $fieldInfo) {
                    $fieldName = $fieldInfo['field_name'] ?? ucwords(str_replace('_', ' ', $fieldKey));
                    $values = array_slice($fieldInfo['values'] ?? [], 0, 50);
                    $valuesList = implode(', ', $values);
                    $categoryProductsSection .= "• {$fieldName}: {$valuesList}\n";
                }
                $categoryProductsSection .= "\n";
            }

            // Show sample products
            if (!empty($catData['sample_products'])) {
                $categoryProductsSection .= "**Sample Products:**\n";
                foreach (array_slice($catData['sample_products'], 0, 5) as $product) {
                    $details = [];
                    foreach ($product as $key => $value) {
                        if (!empty($value)) {
                            $displayKey = ucwords(str_replace('_', ' ', $key));
                            $details[] = "{$displayKey}: {$value}";
                        }
                    }
                    if (!empty($details)) {
                        $categoryProductsSection .= "- " . implode(', ', $details) . "\n";
                    }
                }
            }

            $categoryProductsSection .= "\n### ⚠️ MANDATORY BEHAVIOR - FIRST RESPONSE RULE:\n";
            $categoryProductsSection .= "1. IMMEDIATELY list the available options above when customer asks about '{$matchedValue}'\n";
            $categoryProductsSection .= "2. Do NOT ask follow-up questions BEFORE giving the full list\n";
            $categoryProductsSection .= "3. FIRST give all available values, THEN ask which one they want\n";
            $categoryProductsSection .= "4. Example: 'Ji, {$matchedValue} me ye options available hain: [LIST]. Kaunsa chahiye?'\n";
        }

        return <<<PROMPT
You are a sales assistant for {$tenantName}. You must communicate naturally like a human sales person.

## CRITICAL: LANGUAGE DETECTION & MATCHING
IMPORTANT: You MUST detect the language of user's message and respond in THE EXACT SAME LANGUAGE.
- If user writes in Hindi → Reply in Hindi
- If user writes in English → Reply in English  
- If user writes in Hinglish (mixed Hindi-English) → Reply in Hinglish
- If user writes in Marathi/Gujarati/Tamil/Telugu/Bengali → Reply in that same language
- NEVER change language mid-conversation unless user changes first
- Detect language from: script used, words, sentence structure
- Example: "muje product chahiye" → Reply in Hindi/Hinglish
- Example: "I need a product" → Reply in English

## TONE & STYLE
{$toneInstruction}

## RESPONSE LENGTH
{$lengthInstruction}
{$customPersonality}

## ADDITIONAL LANGUAGE GUIDANCE
{$languageInstruction}

{$catalogueSection}

{$currentProductSection}

{$categoryProductsSection}

{$availableOptionsSection}

## RESPONSE STYLE
- Keep responses natural and human-like
- For simple confirmations: 5-15 words
- For explanations/details: 40-60 words
- Ask flowchart questions in conversational manner
- Never sound robotic or AI-generated
- ONLY mention products that exist in your catalogue above
- Match user's language style (formal/informal)

## ⚠️ CRITICAL: CONFIRMATION ACKNOWLEDGMENT RULES
These rules are MANDATORY and apply to ALL admins:
1. When user says "yes", "ok", "theek hai", "haan", "confirm" → ACCEPT IT AND MOVE FORWARD
2. NEVER ask the same question twice after user confirms
3. NEVER repeat information that user already confirmed
4. After confirmation, either:
   - Ask the NEXT missing field, OR
   - If all fields complete, say "Order noted" and end conversation
5. If user confirms packaging → Do NOT ask about packaging again
6. Example flow:
   - Bot: "Model 28, 6inch, Rose Gold ke liye 16 pieces per box. Theek hai?"
   - User: "yes"
   - Bot: "Perfect! Order noted. Kuch aur chahiye?" ← CORRECT
   - Bot: "Kya yeh packaging confirm karein?" ← WRONG (asking again)

## 🔴 CRITICAL: LIST REQUEST HANDLING (MUST FOLLOW)
When user asks for a list/options with phrases like:
- "konsi size he", "size list", "size batao", "which sizes available"
- "kaunse model hain", "model list do", "available models"
- "finish options", "color list", "finish batao"
- "options kya hain", "list de do", "batao kya kya hai"
- "kitne type", "list bhejo", "all options"

### MANDATORY BEHAVIOR FOR LIST REQUESTS:
1. **DETECT** if user is asking for available options/list for ANY field
2. **RESPOND FIRST** with the complete list from AVAILABLE OPTIONS section above
3. **DO NOT SKIP** to the next question without giving the list
4. **FORMAT**: "Ji, {field} ke options ye hain: {list from catalogue}. Kaunsa chahiye?"

### WRONG BEHAVIOR (NEVER DO THIS):
- User: "konsi size he"
- Bot: "Got it! Now, what finish would you like?" ← WRONG! User asked for SIZE LIST!

### CORRECT BEHAVIOR:
- User: "konsi size he"
- Bot: "Ji, size ke options ye hain: 32mm, 64mm, 96mm, 160mm, 224mm, 288mm, 450mm, 600mm, 900mm, 1200mm. Kaunsi size chahiye?" ← CORRECT!

### PRIORITY ORDER:
1. If user asks for a list → SHOW THE LIST FIRST (from Available Options above)
2. Only after showing list → Ask which option they want
3. Never skip showing the list to ask the next field

{$replyContext}

{$recentConv}

{$productMemory}

{$fieldRules}

{$pendingQuestionsSection}

## COLLECTED DATA SO FAR
{$collectedData}

## LEAD STATUSES AVAILABLE
{$statusList}

## YOUR TASKS:
1. FIRST: Detect the language of user's message
2. Analyze user message and extract product information
3. Determine appropriate lead status based on conversation progress
4. If user mentions removing/changing products, note it in product_actions
5. Identify if user mentions any unique field values (for catalogue lookup)
6. Generate natural conversational response IN THE SAME LANGUAGE as user's input

## OUTPUT FORMAT (JSON only)
{
    "intent": "inquiry|confirmation|modification|rejection|casual|unclear",
    "lead_status_suggestion": "status name from list above or null",
    "extracted_data": {
        "FIELD_NAME_HERE": "user's answer for that field"
    },
    "product_confirmations": [
        {
            "category": "product type",
            "model": "model number",
            "size": "size value",
            "finish": "finish value"
        }
    ],
    "product_rejections": [
        {
            "model": "model* (add * to mark for deletion)"
        }
    ],
    "unique_field_mentioned": "unique field value if mentioned or null",
    "response_message": "Your conversational response IN USER'S LANGUAGE",
    "all_required_complete": true/false,
    "detected_language": "hi|en|hinglish|mr|gu|ta|te|bn|other"
}

## RULES (FOLLOW STRICTLY):
1. ALWAYS respond in the SAME LANGUAGE as user's input - this is MANDATORY
2. Extract ONLY the fields that are defined in FIELD RULES section above (from flowchart)
3. 🚫 NEVER ask for fields that are NOT in FIELD RULES - follow flowchart ONLY
4. 🚫 NEVER ask about qty/quantity UNLESS it is explicitly listed in FIELD RULES above
5. 🚫 NEVER ask about material UNLESS it is explicitly listed in FIELD RULES above
6. If user wants to remove/cancel something, add to product_rejections with * on the field
7. Return ONLY valid JSON
8. Check if all required questions from FIELD RULES are answered
9. Determine lead status based on answered questions and user engagement
10. NEVER mention products not in your catalogue
11. If language is unclear, default to Hinglish (Hindi+English mix)
12. When all FIELD RULES questions are answered, say order is complete - DO NOT invent new questions
13. 🔴 **ALWAYS ADD PRODUCT_CONFIRMATIONS**: Whenever extracted_data has ANY product field value (category, model, size, finish), you MUST ALSO add same values to product_confirmations array. This is MANDATORY - never leave product_confirmations empty if extracted_data has product values.

## 🔴 CRITICAL: MULTI-VALUE EXTRACTION RULES (MUST FOLLOW)
When user mentions MULTIPLE values for a field (using "or", "and", ",", "aur" etc.):

### EXAMPLE INPUT:
- "profile handle or knob" → User wants 2 categories
- "9007, 9008, 9009" → User wants 3 models  
- "black and gold" → User wants 2 finishes
- "450 or 900" → User wants 2 sizes

### MANDATORY BEHAVIOR:
1. **SPLIT** the values into SEPARATE product_confirmations entries
2. **NEVER** store combined value like "profile handle or knob" as single entry
3. Create ONE product_confirmation object for EACH separate value

### CORRECT OUTPUT EXAMPLE:
User: "profile handle or knob chahiye"
```json
{
    "product_confirmations": [
        {"category": "Profile handles"},
        {"category": "Knobs"}
    ],
    "extracted_data": {
        "category": "Profile handles, Knobs"
    }
}
```

### WRONG OUTPUT (NEVER DO THIS):
```json
{
    "product_confirmations": [
        {"category": "profile handle or knob"}  ← WRONG! This is combined value
    ]
}
```

### MULTI-VALUE DETECTION KEYWORDS:
- "or" / "ya" / "aur" → Split by this
- "and" / "aur" / "&" → Split by this  
- "," / " " with multiple values → Split by this
- "both" / "dono" → Split into 2 entries

### FIELD VALUE NORMALIZATION:
Always normalize category names to match catalogue:
- "profile handle" → "Profile handles"
- "knob" / "knobs" → "Knobs"
- "cabinet handle" → "Cabinet handles"
- "main door handle" → "Main door handles"



PROMPT;
    }

    /**
     * Call AI API based on provider with token tracking
     */
    public function callAI(string $systemPrompt, string $userMessage): string
    {
        $startTime = microtime(true);

        $result = match ($this->provider) {
            'vertex' => $this->callVertexAI($systemPrompt, $userMessage),
            'google', 'gemini' => $this->callGemini($systemPrompt, $userMessage),
            'openai' => $this->callOpenAI($systemPrompt, $userMessage),
            'deepseek' => $this->callDeepSeek($systemPrompt, $userMessage),
            default => ['content' => $this->fallbackResponse(), 'tokens' => ['input' => 0, 'output' => 0]],
        };

        // Log AI usage for cost tracking
        if ($this->adminId && isset($result['tokens'])) {
            AiUsageLog::log(
                $this->adminId,
                $this->provider,
                $this->model,
                $result['tokens']['input'] ?? 0,
                $result['tokens']['output'] ?? 0,
                AiUsageLog::TYPE_MESSAGE
            );
        }

        return $result['content'];
    }

    /**
     * Call Gemini API (Google AI Studio)
     */
    protected function callGemini(string $systemPrompt, string $userMessage): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($url . '?key=' . $this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $systemPrompt . "\n\nUser: " . $userMessage]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 1500,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

        if ($response->failed()) {
            Log::error('Gemini API error', ['response' => $response->body()]);
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

        // Extract token counts if available
        $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? $this->estimateTokens($systemPrompt . $userMessage);
        $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? $this->estimateTokens($content);

        return [
            'content' => $this->cleanJsonResponse($content),
            'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
        ];
    }

    /**
     * Call Vertex AI
     */
    protected function callVertexAI(string $systemPrompt, string $userMessage): array
    {
        if (!$this->projectId) {
            throw new \Exception('Vertex AI project_id not configured');
        }

        $accessToken = $this->getVertexAccessToken();
        $url = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $systemPrompt . "\n\nUser: " . $userMessage]]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 1500,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

        if ($response->failed()) {
            Log::error('Vertex AI error', ['response' => $response->body()]);
            throw new \Exception('Vertex AI error: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? $this->estimateTokens($systemPrompt . $userMessage);
        $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? $this->estimateTokens($content);

        return [
            'content' => $this->cleanJsonResponse($content),
            'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
        ];
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $systemPrompt, string $userMessage): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 1500,
                    'response_format' => ['type' => 'json_object'],
                ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $inputTokens = $data['usage']['prompt_tokens'] ?? $this->estimateTokens($systemPrompt . $userMessage);
        $outputTokens = $data['usage']['completion_tokens'] ?? $this->estimateTokens($content);

        return [
            'content' => $content,
            'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
        ];
    }

    /**
     * Call DeepSeek API
     */
    protected function callDeepSeek(string $systemPrompt, string $userMessage): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.deepseek.com/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 1500,
                    'response_format' => ['type' => 'json_object'],
                ]);

        if ($response->failed()) {
            throw new \Exception('DeepSeek API error: ' . $response->body());
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $inputTokens = $data['usage']['prompt_tokens'] ?? $this->estimateTokens($systemPrompt . $userMessage);
        $outputTokens = $data['usage']['completion_tokens'] ?? $this->estimateTokens($content);

        return [
            'content' => $content,
            'tokens' => ['input' => $inputTokens, 'output' => $outputTokens],
        ];
    }

    /**
     * Get Vertex AI access token from database settings (JWT auth)
     */
    protected function getVertexAccessToken(): string
    {
        // Cache the token for 50 minutes (tokens are valid for 60 minutes)
        return \Illuminate\Support\Facades\Cache::remember('ai_service_vertex_token', 3000, function () {
            $serviceEmail = Setting::getValue('vertex_service_email', '');
            $privateKey = Setting::getValue('vertex_private_key', '');

            if (empty($serviceEmail) || empty($privateKey)) {
                throw new \Exception('Vertex AI service account not configured. Please set up in SuperAdmin AI Config.');
            }

            $now = time();
            $exp = $now + 3600;

            // Create JWT header
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            // Create JWT payload
            $payload = [
                'iss' => $serviceEmail,
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $exp,
            ];

            // Base64url encode
            $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
            $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

            // Create signature
            $signatureInput = $base64Header . '.' . $base64Payload;

            // Clean up the private key - handle all possible escaped newline formats
            // Step 1: Replace literal backslash-n sequences with actual newlines
            $privateKey = str_replace('\\n', "\n", $privateKey);

            // Step 2: If still no newlines, try replacing literal \n as 2 characters
            if (!str_contains($privateKey, "\n")) {
                $privateKey = str_replace('\n', "\n", $privateKey);
            }

            // Step 3: If key doesn't have proper PEM format, wrap it
            if (!str_contains($privateKey, '-----BEGIN')) {
                Log::error('Vertex AI private key missing PEM header', [
                    'key_length' => strlen($privateKey),
                    'key_preview' => substr($privateKey, 0, 50) . '...',
                ]);
                throw new \Exception('Invalid private key format: missing PEM header. Please paste the complete private key from your service account JSON file.');
            }

            // Step 4: Ensure proper line breaks after header and before footer
            $privateKey = preg_replace('/-----BEGIN (PRIVATE KEY|RSA PRIVATE KEY)-----/', "-----BEGIN $1-----\n", $privateKey);
            $privateKey = preg_replace('/-----END (PRIVATE KEY|RSA PRIVATE KEY)-----/', "\n-----END $1-----", $privateKey);

            // Step 5: Clean up multiple consecutive newlines
            $privateKey = preg_replace("/\n{2,}/", "\n", $privateKey);

            // Debug log (remove in production)
            Log::debug('Vertex AI private key prepared', [
                'key_length' => strlen($privateKey),
                'has_header' => str_contains($privateKey, '-----BEGIN'),
                'has_footer' => str_contains($privateKey, '-----END'),
                'newline_count' => substr_count($privateKey, "\n"),
            ]);

            $key = openssl_pkey_get_private($privateKey);
            if (!$key) {
                $opensslError = openssl_error_string();
                Log::error('Vertex AI private key OpenSSL error', [
                    'error' => $opensslError,
                    'key_preview' => substr($privateKey, 0, 100),
                ]);
                throw new \Exception('Invalid private key: ' . $opensslError . '. Please ensure you copied the complete private_key from your Google Cloud service account JSON file.');
            }

            $signature = '';
            openssl_sign($signatureInput, $signature, $key, OPENSSL_ALGO_SHA256);
            $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

            $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->failed()) {
                Log::error('Failed to get Vertex AI access token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to get Vertex AI access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Fallback response
     */
    protected function fallbackResponse(): string
    {
        return json_encode([
            'intent' => 'unclear',
            'lead_status_suggestion' => null,
            'extracted_data' => [],
            'product_confirmations' => [],
            'product_actions' => null,
            'unique_field_mentioned' => null,
            'response_message' => 'Kripya apna message dobara bhejein.',
            'all_required_complete' => false,
            'detected_language' => 'hi',
        ]);
    }

    /**
     * Clean JSON response
     */
    protected function cleanJsonResponse(string $content): string
    {
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        return trim($content);
    }

    /**
     * Estimate tokens (rough approximation)
     */
    protected function estimateTokens(string $text): int
    {
        // Rough estimate: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Parse enhanced AI response
     */
    protected function parseEnhancedResponse(string $response): array
    {
        try {
            // Log raw response for debugging
            Log::debug('AI raw response for parsing', [
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 500),
            ]);

            // Clean the response - remove any markdown code blocks
            $cleanedResponse = $response;
            $cleanedResponse = preg_replace('/```json\s*/', '', $cleanedResponse);
            $cleanedResponse = preg_replace('/```\s*/', '', $cleanedResponse);
            $cleanedResponse = trim($cleanedResponse);

            $data = json_decode($cleanedResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('AI response not valid JSON', [
                    'json_error' => json_last_error_msg(),
                    'response' => substr($response, 0, 1000),
                ]);

                // FALLBACK: If not JSON, treat the raw response as a plain text message
                // This handles cases where AI returns a simple text response instead of JSON
                $fallbackMessage = $this->extractFallbackMessage($response);

                return [
                    'success' => true, // Mark as success since we have a message to send
                    'intent' => 'unclear',
                    'lead_status_suggestion' => null,
                    'extracted_data' => [],
                    'product_confirmations' => [],
                    'product_actions' => null,
                    'unique_field_mentioned' => null,
                    'response_message' => $fallbackMessage,
                    'all_required_complete' => false,
                    'detected_language' => 'hi',
                    'fallback_used' => true,
                ];
            }

            return [
                'success' => true,
                'intent' => $data['intent'] ?? 'unclear',
                'lead_status_suggestion' => $data['lead_status_suggestion'] ?? null,
                'extracted_data' => $data['extracted_data'] ?? [],
                'product_confirmations' => $data['product_confirmations'] ?? [],
                'product_actions' => $data['product_actions'] ?? null,
                'unique_field_mentioned' => $data['unique_field_mentioned'] ?? null,
                'response_message' => $data['response_message'] ?? '',
                'all_required_complete' => $data['all_required_complete'] ?? false,
                'detected_language' => $data['detected_language'] ?? 'hi',
            ];
        } catch (\Exception $e) {
            Log::error('parseEnhancedResponse exception', [
                'error' => $e->getMessage(),
                'response' => substr($response, 0, 500),
            ]);

            return [
                'success' => true,
                'intent' => 'unclear',
                'response_message' => $this->extractFallbackMessage($response),
                'all_required_complete' => false,
                'fallback_used' => true,
            ];
        }
    }

    /**
     * Extract a usable message from non-JSON AI response
     */
    protected function extractFallbackMessage(string $response): string
    {
        // Remove any JSON-like structures that are malformed
        $cleaned = preg_replace('/\{[^}]*$/', '', $response); // Remove incomplete JSON
        $cleaned = preg_replace('/```[a-z]*\s*/', '', $cleaned); // Remove code block markers
        $cleaned = trim($cleaned);

        // If it looks like the response has a response_message field but failed to parse,
        // try to extract it manually
        if (preg_match('/"response_message"\s*:\s*"([^"]+)"/', $response, $matches)) {
            return $matches[1];
        }

        // If response is empty or too short, provide a default
        if (empty($cleaned) || strlen($cleaned) < 5) {
            return 'Aapka sawaal samajh nahi aaya. Kripya dobara batayein.';
        }

        // Return cleaned response (limit length)
        return mb_substr($cleaned, 0, 500);
    }


    /**
     * Check if unique field is mentioned and get catalogue image
     */
    public function checkCatalogueForUniqueField(int $adminId, ?string $uniqueFieldValue): ?array
    {
        if (!$uniqueFieldValue) {
            return null;
        }

        $catalogue = Catalogue::where('admin_id', $adminId)
            ->where('unique_field_value', $uniqueFieldValue)
            ->where('is_active', true)
            ->first();

        if (!$catalogue) {
            return null;
        }

        return [
            'image_url' => $catalogue->image_url,
            'images' => $catalogue->images ?? [],
            'video_url' => $catalogue->video_url,
            'product_data' => $catalogue->data,
        ];
    }

    // Keep old method for backward compatibility
    public function processMessage($tenant, Customer $customer, string $message, array $context = []): array
    {
        $systemPrompt = $this->buildSystemPromptLegacy($tenant, $customer, $context);

        try {
            $response = $this->callAI($systemPrompt, $message);
            return $this->parseResponse($response);
        } catch (\Exception $e) {
            Log::error('AI Service error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback' => true,
            ];
        }
    }

    protected function buildSystemPromptLegacy($tenant, Customer $customer, array $context): string
    {
        $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        $fields = ProductQuestion::where('admin_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $fieldsList = $fields->map(function ($f) {
            $required = $f->is_required ? '(Required)' : '(Optional)';
            $unique = $f->is_unique_key ? '🔑' : '';
            return "- {$f->field_name}: {$f->display_name} {$required} {$unique}";
        })->join("\n");

        $tenantName = is_object($tenant) ? ($tenant->company_name ?? $tenant->name ?? 'Datsun Hardware') : 'Datsun Hardware';

        return <<<PROMPT
You are a sales assistant AI for {$tenantName}.

## TASK
Analyze customer message and extract product info. Output JSON only.

## FIELDS
{$fieldsList}

## OUTPUT (JSON only)
{
    "intent": "inquiry|confirmation|rejection|casual|unclear",
    "language": "hi|en",
    "extractedFields": {},
    "confirmMsg": [],
    "rejectionMsg": [],
    "userLanguageMsg": "Response in user's language"
}

RULES:
1. Extract all fields from message
2. Respond in Hindi/Hinglish
3. Return ONLY valid JSON
PROMPT;
    }

    protected function parseResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid JSON', 'raw' => $response];
            }

            return [
                'success' => true,
                'intent' => $data['intent'] ?? 'unclear',
                'language' => $data['language'] ?? 'hi',
                'extractedFields' => $data['extractedFields'] ?? [],
                'confirmMsg' => $data['confirmMsg'] ?? [],
                'rejectionMsg' => $data['rejectionMsg'] ?? [],
                'userLanguageMsg' => $data['userLanguageMsg'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Parse error', 'raw' => $response];
        }
    }

    public function generateResponse(int $tenantId, string $fieldName, string $language, array $options = []): string
    {
        $template = QuestionTemplate::getTemplate($tenantId, $fieldName, $language);

        if ($template) {
            $text = $template->question_text;
            if (!empty($options) && $template->options_text) {
                $text .= ' ' . str_replace('{options}', implode(', ', $options), $template->options_text);
            }
            return $text;
        }

        $defaults = [
            'hi' => [
                'category' => 'Aapko kaunsa product chahiye?',
                'model' => 'Kaunsa model number chahiye?',
                'size' => 'Size kya chahiye?',
                'finish' => 'Finish/Color kaunsa?',
                'qty' => 'Kitne pieces chahiye?',
            ],
            'en' => [
                'category' => 'What product do you need?',
                'model' => 'Which model number?',
                'size' => 'What size?',
                'finish' => 'What finish/color?',
                'qty' => 'How many pieces?',
            ]
        ];

        return $defaults[$language][$fieldName] ?? $defaults['en'][$fieldName] ?? "Please provide {$fieldName}:";
    }
}
