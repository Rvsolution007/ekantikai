<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Catalogue;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\ProductQuestion;
use App\Models\QuestionnaireNode;
use Illuminate\Support\Facades\Log;

class DemoConversationService
{
    protected AIService $aiService;
    protected int $adminId;
    protected array $collectedData = [];

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get all flowchart question nodes for the admin in order
     */
    public function getFlowchartQuestions(int $adminId): array
    {
        $questions = [];

        // Get start node
        $startNode = QuestionnaireNode::where('admin_id', $adminId)
            ->where('node_type', 'start')
            ->where('is_active', true)
            ->first();

        if (!$startNode) {
            // Fallback to ProductQuestions if no flowchart
            return ProductQuestion::where('admin_id', $adminId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'type' => 'product_question',
                        'field_name' => $q->field_name,
                        'display_name' => $q->display_name,
                        'question_text' => $q->question_text ?? "What is your {$q->display_name}?",
                        'options' => $q->options_manual ? explode(',', $q->options_manual) : [],
                    ];
                })
                ->toArray();
        }

        // Traverse flowchart from start node
        $visited = [];
        $currentNode = $startNode;

        while ($currentNode) {
            if (in_array($currentNode->id, $visited)) {
                break; // Prevent infinite loops
            }
            $visited[] = $currentNode->id;

            if ($currentNode->node_type === 'question') {
                $config = $currentNode->config ?? [];
                $field = $currentNode->questionnaireField;

                $questions[] = [
                    'id' => $currentNode->id,
                    'type' => 'flowchart_node',
                    'field_name' => $config['field_name'] ?? ($field->field_name ?? 'field_' . $currentNode->id),
                    'display_name' => $currentNode->label ?? ($field->display_name ?? 'Question'),
                    'question_text' => $config['question_text'] ?? "What {$currentNode->label} would you like?",
                    'options' => $config['options'] ?? ($field->options_manual ? explode(',', $field->options_manual) : []),
                ];
            }

            // Get next node
            $nextConnection = $currentNode->outgoingConnections()->orderBy('priority')->first();
            $currentNode = $nextConnection ? $nextConnection->targetNode : null;
        }

        return $questions;
    }

    /**
     * Get catalogue options for a field
     */
    public function getCatalogueOptionsForField(int $adminId, string $fieldName): array
    {
        $catalogues = Catalogue::where('admin_id', $adminId)->get();
        $options = [];

        foreach ($catalogues as $catalogue) {
            $data = $catalogue->data ?? [];
            $fieldLower = strtolower($fieldName);

            foreach ($data as $key => $value) {
                if (strtolower($key) === $fieldLower && !empty($value)) {
                    $options[] = $value;
                }
            }

            // Check standard columns
            if ($fieldLower === 'product_type' && !empty($catalogue->product_type)) {
                $options[] = $catalogue->product_type;
            }
            if ($fieldLower === 'model_code' && !empty($catalogue->model_code)) {
                $options[] = $catalogue->model_code;
            }
        }

        return array_unique($options);
    }

    /**
     * Generate a realistic user question/response for a node
     */
    public function generateUserQuestion(array $node, array $catalogueOptions = []): string
    {
        $displayName = $node['display_name'] ?? 'item';
        $options = !empty($catalogueOptions) ? $catalogueOptions : ($node['options'] ?? []);

        // First message from user (initial inquiry)
        if (empty($this->collectedData)) {
            return "Hi, I want to buy some products in bulk. Can you help me?";
        }

        // Generate a response based on available options
        if (!empty($options)) {
            $selectedOption = $options[array_rand($options)];
            return "I want {$selectedOption}";
        }

        // Generate sample responses based on field type
        $fieldName = strtolower($node['field_name'] ?? '');
        $sampleResponses = [
            'category' => 'I need wardrobe handles',
            'product_type' => 'Knob handles please',
            'model' => 'Model 401',
            'model_code' => '402',
            'size' => '4 inch',
            'finish' => 'Chrome finish',
            'color' => 'Silver',
            'quantity' => '100 pieces',
            'qty' => '50 sets',
            'name' => 'Rahul Sharma',
            'phone' => '9876543210',
            'email' => 'rahul@example.com',
            'city' => 'Mumbai',
            'address' => 'Andheri West, Mumbai',
        ];

        foreach ($sampleResponses as $key => $response) {
            if (str_contains($fieldName, $key)) {
                return $response;
            }
        }

        return "Yes, please proceed with {$displayName}";
    }

    /**
     * Generate AI bot response using the actual AI service
     */
    public function generateBotResponse(int $adminId, string $userMessage, array $context = []): array
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return [
                'success' => false,
                'message' => 'Admin not found',
            ];
        }

        // Create a mock customer and lead for the demo
        $mockCustomer = new Customer([
            'phone' => '9999999999',
            'name' => 'Demo Customer',
            'admin_id' => $adminId,
        ]);
        $mockCustomer->id = 0;

        $mockLead = new Lead([
            'admin_id' => $adminId,
            'customer_id' => 0,
            'status' => 'new',
            'collected_data' => $this->collectedData,
        ]);
        $mockLead->id = 0;

        try {
            $this->aiService->setAdmin($adminId);

            $response = $this->aiService->processMessageEnhanced(
                $admin,
                $mockCustomer,
                $mockLead,
                $userMessage,
                [
                    'is_demo' => true,
                    'collected_data' => $this->collectedData,
                    'pending_questions' => $context['pending_questions'] ?? [],
                ]
            );

            // Check for response_message (correct key from parseEnhancedResponse)
            if (isset($response['response_message']) && !empty($response['response_message'])) {
                return [
                    'success' => true,
                    'message' => $response['response_message'],
                    'extracted_data' => $response['extracted_data'] ?? [],
                ];
            }

            // Also check reply key for backward compatibility
            if (isset($response['reply']) && !empty($response['reply'])) {
                return [
                    'success' => true,
                    'message' => $response['reply'],
                    'extracted_data' => $response['extracted_data'] ?? [],
                ];
            }

            // Generate a contextual fallback message based on current/next question
            $fallbackMessage = $this->generateContextualFallback($context, $admin);

            return [
                'success' => true,
                'message' => $fallbackMessage,
            ];
        } catch (\Exception $e) {
            Log::error('Demo AI response error', ['error' => $e->getMessage()]);

            // Generate contextual fallback even on error
            $fallbackMessage = $this->generateContextualFallback($context, $admin);

            return [
                'success' => false,
                'message' => $fallbackMessage,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a contextual fallback message based on the pending questions
     */
    protected function generateContextualFallback(array $context, Admin $admin): string
    {
        $pendingQuestions = $context['pending_questions'] ?? [];
        $currentQuestion = $context['current_question'] ?? null;
        $isFinal = $context['is_final'] ?? false;

        // Final message
        if ($isFinal) {
            return "Bahut dhanyavaad! Aapka order details note kar liya hai. Hum jaldi aapse contact karenge. 🙏";
        }

        // If we have a current question that was just answered, ask the next one
        if ($currentQuestion && !empty($pendingQuestions)) {
            $nextQuestion = $pendingQuestions[0] ?? null;
            if ($nextQuestion) {
                $displayName = $nextQuestion['display_name'] ?? $nextQuestion['field_name'] ?? 'details';
                return "Achha, samajh gaya! Ab please batayein - aapko kaunsa {$displayName} chahiye?";
            }
        }

        // If there are pending questions, ask the first one
        if (!empty($pendingQuestions)) {
            $firstPending = $pendingQuestions[0];
            $displayName = $firstPending['display_name'] ?? $firstPending['field_name'] ?? 'details';
            return "Zaroor! Pehle batayein - aapko kaunsa {$displayName} chahiye?";
        }

        // All questions answered
        return "Bahut badiya! Aapne saari details de di hain. Kya aur kuch chahiye?";
    }

    /**
     * Run a complete demo conversation
     * Returns array of conversation steps
     */
    public function runFullDemo(int $adminId): array
    {
        $this->adminId = $adminId;
        $this->collectedData = [];
        $conversation = [];
        $admin = Admin::find($adminId);

        if (!$admin) {
            return [['type' => 'error', 'message' => 'Admin not found']];
        }

        // Get flowchart questions
        $questions = $this->getFlowchartQuestions($adminId);

        // Add greeting from bot
        $greeting = "Hello! Welcome to {$admin->name}. How can I help you today?";
        if (!empty($admin->ai_system_prompt)) {
            // Try to extract company name from prompt
            $greeting = "Hello! Welcome! I'm here to help you with your requirements. How can I assist you today?";
        }

        $conversation[] = [
            'type' => 'bot',
            'message' => $greeting,
            'node' => null,
        ];

        // Initial user message
        $userMessage = "Hi, I want to buy some handles in bulk. Can you help me?";
        $conversation[] = [
            'type' => 'user',
            'message' => $userMessage,
            'node' => null,
        ];

        // Get AI response to initial message
        $botResponse = $this->generateBotResponse($adminId, $userMessage, ['pending_questions' => $questions]);
        $conversation[] = [
            'type' => 'bot',
            'message' => $botResponse['message'],
            'node' => null,
        ];

        // Process each flowchart question
        foreach ($questions as $index => $question) {
            // Get catalogue options for this field
            $options = $this->getCatalogueOptionsForField($adminId, $question['field_name']);

            // Generate user response
            $userResponse = $this->generateUserQuestion($question, $options);
            $conversation[] = [
                'type' => 'user',
                'message' => $userResponse,
                'node' => $question,
            ];

            // Store collected data
            $this->collectedData[$question['field_name']] = $userResponse;

            // Get AI response
            $remainingQuestions = array_slice($questions, $index + 1);
            $botResponse = $this->generateBotResponse($adminId, $userResponse, [
                'pending_questions' => $remainingQuestions,
                'current_question' => $question,
            ]);

            $conversation[] = [
                'type' => 'bot',
                'message' => $botResponse['message'],
                'node' => $question,
            ];
        }

        // Final thank you message from user
        $conversation[] = [
            'type' => 'user',
            'message' => "That's all I need. Thank you!",
            'node' => null,
        ];

        // Final bot response
        $finalResponse = $this->generateBotResponse($adminId, "That's all I need. Thank you!", [
            'is_final' => true,
        ]);
        $conversation[] = [
            'type' => 'bot',
            'message' => $finalResponse['message'],
            'node' => null,
        ];

        return $conversation;
    }

    /**
     * Run demo step by step (for streaming/progressive loading)
     */
    public function runDemoStep(int $adminId, int $stepIndex, array $previousData = []): array
    {
        $this->adminId = $adminId;
        $this->collectedData = $previousData['collected_data'] ?? [];

        $admin = Admin::find($adminId);
        $questions = $this->getFlowchartQuestions($adminId);

        // Step 0: Bot greeting
        if ($stepIndex === 0) {
            return [
                'step' => 0,
                'type' => 'bot',
                'message' => "Hello! Welcome to {$admin->name}. How can I help you today?",
                'node' => null,
                'hasMore' => true,
                'totalSteps' => (count($questions) * 2) + 4, // greeting + initial user + response + questions + final
            ];
        }

        // Step 1: User initial message
        if ($stepIndex === 1) {
            return [
                'step' => 1,
                'type' => 'user',
                'message' => "Hi, I want to buy some products in bulk. Can you help me?",
                'node' => null,
                'hasMore' => true,
            ];
        }

        // Step 2: Bot response to initial message
        if ($stepIndex === 2) {
            $userMessage = "Hi, I want to buy some products in bulk. Can you help me?";
            $botResponse = $this->generateBotResponse($adminId, $userMessage, ['pending_questions' => $questions]);

            return [
                'step' => 2,
                'type' => 'bot',
                'message' => $botResponse['message'],
                'node' => null,
                'hasMore' => count($questions) > 0,
            ];
        }

        // Question/answer steps (2 steps per question)
        $questionOffset = $stepIndex - 3;
        $questionIndex = intval($questionOffset / 2);
        $isUserStep = ($questionOffset % 2) === 0;

        if ($questionIndex < count($questions)) {
            $question = $questions[$questionIndex];
            $options = $this->getCatalogueOptionsForField($adminId, $question['field_name']);

            if ($isUserStep) {
                // User response
                $userResponse = $this->generateUserQuestion($question, $options);
                $this->collectedData[$question['field_name']] = $userResponse;

                return [
                    'step' => $stepIndex,
                    'type' => 'user',
                    'message' => $userResponse,
                    'node' => $question,
                    'hasMore' => true,
                    'collected_data' => $this->collectedData,
                ];
            } else {
                // Bot response
                $userResponse = $previousData['last_user_message'] ?? '';
                $remainingQuestions = array_slice($questions, $questionIndex + 1);
                $botResponse = $this->generateBotResponse($adminId, $userResponse, [
                    'pending_questions' => $remainingQuestions,
                    'current_question' => $question,
                ]);

                return [
                    'step' => $stepIndex,
                    'type' => 'bot',
                    'message' => $botResponse['message'],
                    'node' => $question,
                    'hasMore' => $questionIndex < count($questions) - 1,
                ];
            }
        }

        // Final steps
        $finalOffset = $stepIndex - 3 - (count($questions) * 2);

        if ($finalOffset === 0) {
            return [
                'step' => $stepIndex,
                'type' => 'user',
                'message' => "That's all I need. Thank you!",
                'node' => null,
                'hasMore' => true,
            ];
        }

        if ($finalOffset === 1) {
            $finalResponse = $this->generateBotResponse($adminId, "That's all I need. Thank you!", [
                'is_final' => true,
            ]);

            return [
                'step' => $stepIndex,
                'type' => 'bot',
                'message' => $finalResponse['message'],
                'node' => null,
                'hasMore' => false,
            ];
        }

        return [
            'step' => $stepIndex,
            'hasMore' => false,
            'complete' => true,
        ];
    }
}
