<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\QuestionnaireField;
use App\Models\QuestionTemplate;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;
    protected string $model;
    protected string $provider;
    protected ?string $projectId;
    protected ?string $location;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'gemini');
        $this->apiKey = config('services.ai.api_key', '');
        $this->model = config('services.ai.model', 'gemini-2.0-flash');

        // Vertex AI specific
        $this->projectId = config('services.ai.vertex_project_id');
        $this->location = config('services.ai.vertex_location', 'us-central1');
    }

    /**
     * Process message with AI to extract intent and entities
     */
    public function processMessage(Tenant $tenant, Customer $customer, string $message, array $context = []): array
    {
        $systemPrompt = $this->buildSystemPrompt($tenant, $customer, $context);

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

    /**
     * Build system prompt for AI
     */
    protected function buildSystemPrompt(Tenant $tenant, Customer $customer, array $context): string
    {
        $fields = QuestionnaireField::where('tenant_id', $tenant->id)
            ->active()
            ->ordered()
            ->get();

        $fieldsList = $fields->map(function ($f) {
            $required = $f->is_required ? '(Required)' : '(Optional)';
            $unique = $f->is_unique_key ? '🔑' : '';
            return "- {$f->field_name}: {$f->display_name} {$required} {$unique}";
        })->join("\n");

        $uniqueFields = $fields->where('is_unique_key', true)
            ->sortBy('unique_key_order')
            ->pluck('field_name')
            ->join(', ');

        $state = $customer->getOrCreateState();
        $globalFields = $customer->global_fields ?? [];

        $cityValue = $globalFields['city'] ?? 'Not asked';
        $purposeValue = $globalFields['purpose_of_purchase'] ?? 'Not asked';
        $tenantName = $tenant->name ?? 'Datsun Hardware';

        return <<<PROMPT
You are a sales assistant AI for {$tenantName}.

## TASK
Analyze customer message and extract product info. Output JSON only.

## FIELDS
{$fieldsList}

## UNIQUE KEY: {$uniqueFields}

## STATE
- City: {$cityValue}
- Purpose: {$purposeValue}

## OUTPUT (JSON only)
{
    "intent": "inquiry|confirmation|rejection|casual|unclear",
    "language": "hi|en",
    "extractedFields": {"category": null, "model": null, "size": null, "finish": null, "qty": null},
    "confirmMsg": [],
    "rejectionMsg": [],
    "userLanguageMsg": "Response in user's language"
}

## RULES
1. Extract all fields from message
2. Respond in Hindi/Hinglish
3. Return ONLY valid JSON
PROMPT;
    }

    /**
     * Call AI API based on provider
     */
    protected function callAI(string $systemPrompt, string $userMessage): string
    {
        return match ($this->provider) {
            'vertex' => $this->callVertexAI($systemPrompt, $userMessage),
            'gemini' => $this->callGemini($systemPrompt, $userMessage),
            'openai' => $this->callOpenAI($systemPrompt, $userMessage),
            default => $this->fallbackResponse(),
        };
    }

    /**
     * Call Google Vertex AI
     */
    protected function callVertexAI(string $systemPrompt, string $userMessage): string
    {
        if (!$this->projectId) {
            throw new \Exception('Vertex AI project_id not configured');
        }

        // Get access token from service account
        $accessToken = $this->getVertexAccessToken();

        $url = "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->model}:generateContent";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt . "\n\nUser: " . $userMessage]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 1000,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

        if ($response->failed()) {
            Log::error('Vertex AI error', ['response' => $response->body()]);
            throw new \Exception('Vertex AI error: ' . $response->body());
        }

        $content = $response->json('candidates.0.content.parts.0.text', '{}');
        return $this->cleanJsonResponse($content);
    }

    /**
     * Get Vertex AI access token from service account
     */
    protected function getVertexAccessToken(): string
    {
        $keyPath = config('services.ai.vertex_key_path');

        if (!$keyPath || !file_exists($keyPath)) {
            throw new \Exception('Vertex AI service account key file not found');
        }

        $key = json_decode(file_get_contents($keyPath), true);

        // Create JWT
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

        // Exchange for access token
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
     * Call Gemini API (direct, not Vertex)
     */
    protected function callGemini(string $systemPrompt, string $userMessage): string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url . '?key=' . $this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $systemPrompt . "\n\nUser: " . $userMessage]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 1000,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

        if ($response->failed()) {
            Log::error('Gemini API error', ['response' => $response->body()]);
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $content = $response->json('candidates.0.content.parts.0.text', '{}');
        return $this->cleanJsonResponse($content);
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $systemPrompt, string $userMessage): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                    'response_format' => ['type' => 'json_object'],
                ]);

        if ($response->failed()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content', '{}');
    }

    /**
     * Fallback response
     */
    protected function fallbackResponse(): string
    {
        return json_encode([
            'intent' => 'unclear',
            'language' => 'hi',
            'extractedFields' => [],
            'confirmMsg' => [],
            'rejectionMsg' => [],
            'userLanguageMsg' => null,
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
     * Parse AI response
     */
    protected function parseResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('AI response not valid JSON', ['response' => $response]);
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

    /**
     * Generate response in user's language
     */
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
