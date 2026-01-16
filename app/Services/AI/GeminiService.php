<?php

namespace App\Services\AI;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeminiService
{
    protected string $model;
    protected string $region;
    protected string $projectId;
    protected string $serviceEmail;
    protected string $privateKey;
    protected bool $useVertexAI = true;

    public function __construct()
    {
        $this->model = Setting::getValue('global_ai_model', 'gemini-2.0-flash');
        $this->region = Setting::getValue('vertex_region', 'asia-south1');
        $this->projectId = Setting::getValue('vertex_project_id', '');
        $this->serviceEmail = Setting::getValue('vertex_service_email', '');
        $this->privateKey = Setting::getValue('vertex_private_key', '');

        // Check if Vertex AI is configured
        $this->useVertexAI = !empty($this->projectId) && !empty($this->serviceEmail) && !empty($this->privateKey);
    }

    /**
     * Get access token for Vertex AI using Service Account JWT
     */
    protected function getAccessToken(): string
    {
        // Cache the token for 50 minutes (tokens are valid for 60 minutes)
        return Cache::remember('vertex_ai_access_token', 3000, function () {
            $now = time();
            $exp = $now + 3600; // Token expires in 1 hour

            // Create JWT header
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            // Create JWT payload
            $payload = [
                'iss' => $this->serviceEmail,
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

            // Clean up the private key
            $privateKey = $this->privateKey;
            if (!str_contains($privateKey, '-----BEGIN')) {
                // Key might be escaped, unescape it
                $privateKey = str_replace('\\n', "\n", $privateKey);
            }

            $signature = '';
            $key = openssl_pkey_get_private($privateKey);
            if (!$key) {
                throw new \Exception('Invalid private key: ' . openssl_error_string());
            }

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
                throw new \Exception('Failed to get access token: ' . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

    /**
     * Generate content using Gemini via Vertex AI
     */
    public function generateContent(string $prompt, array $context = []): ?string
    {
        if (!$this->useVertexAI) {
            throw new \Exception('Vertex AI is not configured. Please set up Service Account in SuperAdmin AI Config.');
        }

        // Build Vertex AI endpoint URL
        $url = sprintf(
            'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/%s:generateContent',
            $this->region,
            $this->projectId,
            $this->region,
            $this->model
        );

        $contents = [];

        // Add context messages
        foreach ($context as $message) {
            $contents[] = [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ];
        }

        // Add current prompt
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 2048,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_ONLY_HIGH',
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_ONLY_HIGH',
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_ONLY_HIGH',
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_ONLY_HIGH',
                ],
            ],
        ];

        try {
            $accessToken = $this->getAccessToken();

            $response = Http::timeout(60)
                ->withToken($accessToken)
                ->post($url, $payload);

            if ($response->failed()) {
                Log::error('Vertex AI Gemini Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);

                // If token expired, clear cache and retry
                if ($response->status() === 401) {
                    Cache::forget('vertex_ai_access_token');
                    $accessToken = $this->getAccessToken();
                    $response = Http::timeout(60)->withToken($accessToken)->post($url, $payload);

                    if ($response->failed()) {
                        return null;
                    }
                } else {
                    return null;
                }
            }

            $data = $response->json();

            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('Vertex AI Gemini Exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate structured response (for message classification)
     */
    public function classifyMessage(string $message, string $productMemory = '', string $chatHistory = ''): ?array
    {
        $systemPrompt = $this->getClassificationPrompt();

        $prompt = <<<PROMPT
{$systemPrompt}

### Product Memory (user's current selections):
{$productMemory}

### Recent Chat History:
{$chatHistory}

### User's Current Message:
{$message}

### Response (JSON only):
PROMPT;

        $response = $this->generateContent($prompt);

        if (!$response) {
            return null;
        }

        // Parse JSON from response
        try {
            // Extract JSON from markdown code block if present
            if (preg_match('/```json?\s*([\s\S]*?)\s*```/', $response, $matches)) {
                $response = $matches[1];
            }

            return json_decode($response, true);
        } catch (\Exception $e) {
            Log::error('Failed to parse Gemini JSON response', [
                'response' => $response,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate sales response
     */
    public function generateSalesResponse(string $message, string $productMemory, string $catalogueData, string $chatHistory, string $leadStage): ?array
    {
        $systemPrompt = $this->getSalesPrompt();

        $prompt = <<<PROMPT
{$systemPrompt}

### Product Memory:
{$productMemory}

### Available Catalogue Data:
{$catalogueData}

### Recent Chat History:
{$chatHistory}

### Current Lead Stage: {$leadStage}

### User's Message:
{$message}

### Response (JSON only):
PROMPT;

        $response = $this->generateContent($prompt);

        if (!$response) {
            return null;
        }

        try {
            if (preg_match('/```json?\s*([\s\S]*?)\s*```/', $response, $matches)) {
                $response = $matches[1];
            }

            return json_decode($response, true);
        } catch (\Exception $e) {
            Log::error('Failed to parse Gemini sales response', [
                'response' => $response,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get classification prompt
     */
    protected function getClassificationPrompt(): string
    {
        return <<<PROMPT
You are an AI assistant that classifies WhatsApp messages for a hardware company (Datsun Hardware, Rajkot).

Products available:
1. Cabinet handles (Models: 007, 008, 009, 0010, 0011-0015)
2. Wardrobe handles (Models: 252, 253, 9001-9053)
3. Wardrobe profile handle (Models: 05, 022)
4. Knob handles (Models: 401-422, 9014, 9017, 9034, 9035)
5. Main door handles (Models: 90-100)
6. Profile handles (Models: 09, 016, 028-039)

Classify the message and respond with JSON:
{
    "messageType": "casual|business|non-business",
    "language": "hindi|hinglish|english|gujarati",
    "catalogueIntent": true|false,
    "productMsg": [
        {
            "product": "product type or null",
            "model": "model code or null",
            "size": "size or null",
            "finish": "finish or null",
            "qty": "quantity or null",
            "action": "confirm|reject|query"
        }
    ],
    "leadStage": "New Lead|Qualified|Confirm|Lose",
    "requiresEscalation": true|false,
    "sentiment": "positive|neutral|negative|complaint"
}
PROMPT;
    }

    /**
     * Get sales prompt
     */
    protected function getSalesPrompt(): string
    {
        $salesPersonName = Setting::getValue('sales_person_name', 'Rahul');

        return <<<PROMPT
You are {$salesPersonName}, a friendly sales executive at Datsun Hardware, Rajkot. You help customers with hardware handles.

Rules:
1. Only recommend products from the catalogue data provided
2. Ask one question at a time (don't overwhelm the customer)
3. Follow this order when asking: Product Type → Model → Size → Finish → Quantity → Packaging → Material
4. For product confirmations, verify all details before confirming
5. If customer rejects a product, mark it with "*" suffix in the model
6. Be conversational and helpful in the customer's language

Respond with JSON:
{
    "QueryMsg": "your message to the customer",
    "ConfirmMsg": [
        {
            "product": "product type",
            "model": "model code",
            "size": "size",
            "finish": "finish",
            "qty": "quantity",
            "packaging": "packaging",
            "material": "material"
        }
    ],
    "RejectionMsg": [
        {
            "product": "product type",
            "model": "model code with * suffix"
        }
    ],
    "City": "customer city if mentioned",
    "purpose_of_purchase": "Wholesale|Retail or null",
    "CatalogueFlag": true|false,
    "NextEmptyField": "which field to ask next"
}
PROMPT;
    }
}
