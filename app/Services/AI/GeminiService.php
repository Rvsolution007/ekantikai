<?php

namespace App\Services\AI;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = Setting::getValue('gemini_api_key', config('services.gemini.api_key', ''));
        $this->model = Setting::getValue('gemini_model', 'gemini-2.5-flash');
    }

    /**
     * Generate content using Gemini
     */
    public function generateContent(string $prompt, array $context = []): ?string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API key is not configured');
        }

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

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
            $response = Http::timeout(30)->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Exception $e) {
            Log::error('Gemini API Exception', [
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
