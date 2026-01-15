<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireConnection;
use App\Models\CustomerQuestionnaireState;
use Illuminate\Support\Facades\Log;

class WorkflowExecutionService
{
    protected Admin $admin;
    protected Customer $customer;
    protected Lead $lead;
    protected CustomerQuestionnaireState $state;

    public function __construct(Admin $admin, Customer $customer, Lead $lead)
    {
        $this->admin = $admin;
        $this->customer = $customer;
        $this->lead = $lead;
        $this->state = $customer->getOrCreateState();
    }

    /**
     * Process incoming message and return bot response
     */
    public function processMessage(string $message): array
    {
        $currentNode = $this->getCurrentNode();

        // If no current node, start from the beginning (Start node)
        if (!$currentNode) {
            $currentNode = $this->getStartNode();
            if (!$currentNode) {
                return [
                    'message' => 'Workflow not configured. Please contact support.',
                    'completed' => true
                ];
            }

            // Move to first question node
            $nextNode = $this->getNextNode($currentNode, null);
            if ($nextNode) {
                $this->setCurrentNode($nextNode);
                return [
                    'message' => $this->formatQuestion($nextNode),
                    'completed' => false
                ];
            }
        }

        // Validate and save answer for current node
        if ($currentNode && $currentNode->node_type === 'question') {
            $isValid = $this->validateAnswer($currentNode, $message);

            // Check if user wants to skip optional field
            if (!$isValid && !$currentNode->is_required && $this->isSkipIntent($message)) {
                $this->markAsSkipped($currentNode);
                $nextNode = $this->getNextNode($currentNode, 'skipped');
            } elseif (!$isValid && $currentNode->is_required) {
                // Re-ask required question
                return [
                    'message' => "Please provide a valid answer.\n\n" . $this->formatQuestion($currentNode),
                    'completed' => false
                ];
            } else {
                // Save answer
                $this->saveAnswer($currentNode, $message);
                $nextNode = $this->getNextNode($currentNode, $message);
            }

            // Move to next node
            if ($nextNode) {
                $this->setCurrentNode($nextNode);

                if ($nextNode->node_type === 'end') {
                    return [
                        'message' => $this->getCompletionMessage(),
                        'completed' => true
                    ];
                }

                return [
                    'message' => $this->formatQuestion($nextNode),
                    'completed' => false
                ];
            } else {
                // No next node, workflow complete
                $this->setCurrentNode(null);
                return [
                    'message' => $this->getCompletionMessage(),
                    'completed' => true
                ];
            }
        }

        return [
            'message' => 'Something went wrong. Please try again.',
            'completed' => false
        ];
    }

    /**
     * Get current node from customer state
     */
    protected function getCurrentNode(): ?QuestionnaireNode
    {
        if (!$this->state->current_node_id) {
            return null;
        }

        return QuestionnaireNode::find($this->state->current_node_id);
    }

    /**
     * Get start node of workflow
     */
    protected function getStartNode(): ?QuestionnaireNode
    {
        return QuestionnaireNode::where('admin_id', $this->admin->id)
            ->where('node_type', 'start')
            ->first();
    }

    /**
     * Get next node based on current node and answer
     */
    protected function getNextNode(?QuestionnaireNode $currentNode, ?string $answer): ?QuestionnaireNode
    {
        if (!$currentNode) {
            return null;
        }

        // Find connection from current node
        $connection = QuestionnaireConnection::where('source_node_id', $currentNode->id)
            ->first();

        if (!$connection) {
            return null;
        }

        return QuestionnaireNode::find($connection->target_node_id);
    }

    /**
     * Set current node in state
     */
    protected function setCurrentNode(?QuestionnaireNode $node): void
    {
        $this->state->current_node_id = $node?->id;
        $this->state->save();
    }

    /**
     * Validate answer based on node configuration
     */
    protected function validateAnswer(QuestionnaireNode $node, string $answer): bool
    {
        // Basic validation - not empty
        if (trim($answer) === '') {
            return false;
        }

        // If node has options, check if answer matches
        $config = $node->config ?? [];
        if (isset($config['options']) && !empty($config['options'])) {
            $options = is_array($config['options']) ? $config['options'] : json_decode($config['options'], true);
            if (is_array($options)) {
                // Case-insensitive match
                $answerLower = strtolower(trim($answer));
                foreach ($options as $option) {
                    if (strtolower(trim($option)) === $answerLower) {
                        return true;
                    }
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Save answer to lead's collected_data
     */
    protected function saveAnswer(QuestionnaireNode $node, string $answer): void
    {
        $fieldName = $node->config['field_name'] ?? $node->label;

        // Save to lead
        $this->lead->addCollectedData($fieldName, $answer, 'workflow_questions');

        // Also save to workflow_data in state for context
        $workflowData = $this->state->workflow_data ?? [];
        $workflowData[$fieldName] = $answer;
        $this->state->workflow_data = $workflowData;
        $this->state->save();

        Log::info('Workflow answer saved', [
            'node' => $node->label,
            'field' => $fieldName,
            'answer' => $answer
        ]);
    }

    /**
     * Format question with context from previous answers
     */
    protected function formatQuestion(QuestionnaireNode $node): string
    {
        $question = $node->config['question_text'] ?? $node->label;

        // Add context from previous answers
        $workflowData = $this->state->workflow_data ?? [];
        foreach ($workflowData as $key => $value) {
            $question = str_replace("{{" . $key . "}}", $value, $question);
        }

        // Add options if available
        $config = $node->config ?? [];
        if (isset($config['options']) && !empty($config['options'])) {
            $options = is_array($config['options']) ? $config['options'] : json_decode($config['options'], true);
            if (is_array($options) && count($options) > 0) {
                $question .= "\n\nOptions:\n";
                foreach ($options as $index => $option) {
                    $question .= ($index + 1) . ". " . $option . "\n";
                }
            }
        }

        // Add required/optional indicator
        if (!$node->is_required) {
            $question .= "\n\n(Optional - reply 'skip' to skip)";
        }

        return $question;
    }

    /**
     * Check if message indicates skip intent
     */
    protected function isSkipIntent(string $message): bool
    {
        $skipKeywords = ['skip', 'next', 'pass', 'छोड़ो', 'अगला'];
        $messageLower = strtolower(trim($message));

        foreach ($skipKeywords as $keyword) {
            if ($messageLower === strtolower($keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark field as skipped
     */
    protected function markAsSkipped(QuestionnaireNode $node): void
    {
        $skipped = $this->state->skipped_optional_fields ?? [];
        $skipped[] = $node->config['field_name'] ?? $node->label;
        $this->state->skipped_optional_fields = array_unique($skipped);
        $this->state->save();
    }

    /**
     * Get completion message
     */
    protected function getCompletionMessage(): string
    {
        return "Thank you! I have collected all the information. Our team will contact you soon. 🙏";
    }

    /**
     * Check if node is required
     */
    public function isNodeRequired(QuestionnaireNode $node): bool
    {
        return $node->is_required;
    }
}
