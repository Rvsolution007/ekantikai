<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AiUsageLog;
use App\Models\Catalogue;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\QuestionnaireField;
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
        $this->model = Setting::getValue('global_ai_model', 'gemini-2.5-flash');

        // Load API keys based on provider
        $this->apiKey = $this->getApiKeyForProvider($this->provider);

        // Vertex AI specific
        $this->projectId = config('services.ai.vertex_project_id');
        $this->location = config('services.ai.vertex_location', 'us-central1');

        $this->languageService = new LanguageDetectionService();
    }

    /**
     * Get API key for provider
     */
    protected function getApiKeyForProvider(string $provider): string
    {
        return match ($provider) {
            'google' => Setting::getValue('gemini_api_key', config('services.ai.api_key', '')),
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

        return $context;
    }

    /**
     * Get flowchart rules for AI context
     */
    protected function getFlowchartRules(int $adminId): array
    {
        $nodes = QuestionnaireNode::where('admin_id', $adminId)
            ->where('is_active', true)
            ->where('node_type', QuestionnaireNode::TYPE_QUESTION)
            ->with('questionnaireField')
            ->get();

        $rules = [];
        foreach ($nodes as $node) {
            $field = $node->questionnaireField;
            if ($field) {
                $rules[] = [
                    'field_name' => $field->field_name,
                    'display_name' => $field->display_name,
                    'is_required' => $node->is_required,
                    'is_optional' => !$node->is_required,
                    'ask_digit' => $node->ask_digit,
                    'is_unique_field' => $node->is_unique_field,
                    'field_type' => $field->field_type,
                ];
            }
        }

        return $rules;
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

        // Get AI settings from global settings (or admin-specific later)
        $aiTone = Setting::getValue('ai_tone', 'friendly');
        $customSystemPrompt = Setting::getValue('ai_system_prompt', '');
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
        if (!empty($context['field_rules'])) {
            $fieldRules = "## FIELD RULES\n";
            foreach ($context['field_rules'] as $rule) {
                $type = $rule['is_required'] ? 'REQUIRED' : 'OPTIONAL';
                $unique = $rule['is_unique_field'] ? ' [UNIQUE IDENTIFIER]' : '';
                $askDigit = $rule['ask_digit'] > 0 ? " (ask max {$rule['ask_digit']} times)" : '';
                $fieldRules .= "- {$rule['display_name']} ({$rule['field_name']}): {$type}{$unique}{$askDigit}\n";
            }
        }

        $collectedData = json_encode($context['collected_data'] ?? [], JSON_PRETTY_PRINT);

        // Custom personality from admin settings
        $customPersonality = $customSystemPrompt ? "\n## CUSTOM PERSONALITY\n{$customSystemPrompt}\n" : '';

        return <<<PROMPT
You are a sales assistant for {$tenantName}. You must communicate naturally like a human sales person.

## TONE & STYLE
{$toneInstruction}

## RESPONSE LENGTH
{$lengthInstruction}
{$customPersonality}
## LANGUAGE INSTRUCTION
{$languageInstruction}

## RESPONSE STYLE (POINT 13)
- Keep responses natural and human-like
- For simple confirmations: 5-15 words
- For explanations/details: 40-60 words
- Ask flowchart questions in conversational manner
- Never sound robotic or AI-generated

{$replyContext}

{$recentConv}

{$productMemory}

{$fieldRules}

## COLLECTED DATA SO FAR
{$collectedData}

## LEAD STATUSES AVAILABLE
{$statusList}

## YOUR TASKS:
1. Analyze user message and extract product information
2. Determine appropriate lead status based on conversation progress
3. If user mentions removing/changing products, note it in product_actions
4. Identify if user mentions any unique field values (for catalogue lookup)
5. Generate natural conversational response

## OUTPUT FORMAT (JSON only)
{
    "intent": "inquiry|confirmation|modification|rejection|casual|unclear",
    "lead_status_suggestion": "status name from list above or null",
    "extracted_data": {"field_name": "value"},
    "product_confirmations": [{"field": "value"}],
    "product_actions": {"action": "add|remove|update", "details": {}},
    "unique_field_mentioned": "unique field value if mentioned or null",
    "response_message": "Your conversational response to user",
    "all_required_complete": true/false,
    "detected_language": "language code"
}

## RULES:
1. Extract ALL relevant fields from message
2. Response in SAME language as user
3. Return ONLY valid JSON
4. Check if all required questions are answered
5. If user says they don't want something, note removal in product_actions
6. Determine lead status based on answered questions and user engagement

PROMPT;
    }

    /**
     * Call AI API based on provider with token tracking
     */
    protected function callAI(string $systemPrompt, string $userMessage): string
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
     * Get Vertex AI access token
     */
    protected function getVertexAccessToken(): string
    {
        $keyPath = config('services.ai.vertex_key_path');

        if (!$keyPath || !file_exists($keyPath)) {
            throw new \Exception('Vertex AI service account key file not found');
        }

        $key = json_decode(file_get_contents($keyPath), true);

        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $claim = base64_encode(json_encode([
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signature = '';
        openssl_sign("$header.$claim", $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = "$header.$claim." . base64_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to get Vertex AI access token');
        }

        return $response->json('access_token');
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
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('AI response not valid JSON', ['response' => $response]);
                return [
                    'success' => false,
                    'error' => 'Invalid JSON',
                    'raw' => $response,
                    'response_message' => 'Kuch technical problem hui. Kripya dubara try karein.',
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
            return [
                'success' => false,
                'error' => 'Parse error',
                'raw' => $response,
                'response_message' => 'Kuch technical problem hui.',
            ];
        }
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
        $fields = QuestionnaireField::where('admin_id', $tenantId)
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
