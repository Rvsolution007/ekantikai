<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\CustomerQuestionnaireState;
use App\Models\GlobalQuestion;
use App\Models\Lead;
use App\Models\ProductQuestion;
use App\Models\QuestionTemplate;

class QuestionnaireService
{
    protected int $tenantId;
    protected Customer $customer;
    protected ?Lead $lead;
    protected ?CustomerQuestionnaireState $state = null;

    public function __construct(int $tenantId, Customer $customer, ?Lead $lead = null)
    {
        $this->tenantId = $tenantId;
        $this->customer = $customer;
        $this->lead = $lead;
        $this->state = $customer->getOrCreateState();
    }

    /**
     * Get the next question to ask
     * Returns: ['type' => 'global|field|complete', 'field' => 'field_name', 'question' => 'text', 'options' => []]
     */
    public function getNextQuestion(): array
    {
        $language = $this->customer->detected_language ?? 'hi';

        // Gate 1: Check global questions (ask first)
        $globalQuestion = $this->getNextGlobalQuestion();
        if ($globalQuestion) {
            return [
                'type' => 'global',
                'field' => $globalQuestion->field_name,
                'question' => $this->getQuestionText($globalQuestion->field_name, $language),
                'options' => $globalQuestion->options ?? [],
                'is_required' => $globalQuestion->is_required,
            ];
        }

        // Gate 2: Check questionnaire fields (in order)
        $nextField = $this->getNextField();
        if ($nextField) {
            // Get collected data for cascading filter
            $collectedData = $this->getCollectedDataForFilter();

            // Use filtered options if field fetches from catalogue
            $options = $nextField->getFilteredOptions($collectedData);
            if (empty($options)) {
                $options = $nextField->getOptions();
            }

            // For quantity fields, don't show options - direct input
            if ($nextField->is_qty_field) {
                $options = [];
            }

            return [
                'type' => 'field',
                'field' => $nextField->field_name,
                'question' => $this->getQuestionText($nextField->field_name, $language),
                'options' => $options,
                'is_required' => $nextField->is_required,
                'is_qty_field' => $nextField->is_qty_field,
                'is_unique_field' => $nextField->is_unique_field,
            ];
        }

        // All fields completed
        return [
            'type' => 'complete',
            'field' => null,
            'question' => $this->getCompletionMessage($language),
            'options' => [],
        ];
    }

    /**
     * Get collected data for cascading filter
     */
    protected function getCollectedDataForFilter(): array
    {
        $collectedData = [];

        // From state completed fields
        $completedFields = $this->state->completed_fields ?? [];
        foreach ($completedFields as $key => $value) {
            if (!str_starts_with($key, '_') && !empty($value)) {
                $collectedData[$key] = $value;
            }
        }

        // From lead collected_data
        if ($this->lead) {
            $leadData = $this->lead->collected_data ?? [];

            // Product questions category
            $productQuestions = $leadData['product_questions'] ?? [];
            foreach ($productQuestions as $key => $value) {
                if (!empty($value)) {
                    $collectedData[$key] = $value;
                }
            }

            // Workflow questions
            $workflowQuestions = $leadData['workflow_questions'] ?? [];
            foreach ($workflowQuestions as $key => $value) {
                if (!empty($value)) {
                    $collectedData[$key] = $value;
                }
            }
        }

        return $collectedData;
    }

    /**
     * Process user response
     */
    public function processResponse(string $fieldName, string $value): array
    {
        // Check if it's a global question
        $globalQuestion = GlobalQuestion::where('tenant_id', $this->tenantId)
            ->where('field_name', $fieldName)
            ->first();

        if ($globalQuestion) {
            // Save global field
            $this->customer->setGlobalField($fieldName, $value);
            $this->customer->markGlobalAsked($fieldName);

            // Update state
            if ($fieldName === 'city') {
                $this->state->city_asked = true;
            } elseif ($fieldName === 'purpose' || $fieldName === 'purpose_of_purchase') {
                $this->state->purpose_asked = true;
            }
            $this->state->save();

            // Save to lead's collected_data
            if ($this->lead) {
                $this->lead->addCollectedData($fieldName, $value, 'global_questions');
            }

            return [
                'action' => 'global_saved',
                'field' => $fieldName,
                'value' => $value,
            ];
        }

        // It's a questionnaire field
        $field = ProductQuestion::where('tenant_id', $this->tenantId)
            ->where('field_name', $fieldName)
            ->first();

        if ($field) {
            // Save to state
            $this->state->setCompletedField($fieldName, $value);
            $this->state->current_field = $fieldName;
            $this->state->save();

            // Check if all unique key fields are complete
            if ($this->areUniqueKeyFieldsComplete()) {
                return $this->saveProduct();
            }

            return [
                'action' => 'field_saved',
                'field' => $fieldName,
                'value' => $value,
            ];
        }

        return [
            'action' => 'unknown_field',
            'field' => $fieldName,
        ];
    }

    /**
     * Check if all unique key fields are complete
     */
    protected function areUniqueKeyFieldsComplete(): bool
    {
        $uniqueFields = ProductQuestion::where('tenant_id', $this->tenantId)
            ->where('is_unique_key', true)
            ->pluck('field_name')
            ->toArray();

        foreach ($uniqueFields as $field) {
            if (empty($this->state->getCompletedField($field))) {
                return false;
            }
        }

        return !empty($uniqueFields);
    }

    /**
     * Save product when unique key fields are complete
     */
    protected function saveProduct(): array
    {
        $fieldValues = $this->state->completed_fields ?? [];
        $uniqueKey = CustomerProduct::buildUniqueKey($this->tenantId, $fieldValues);
        $lineKey = CustomerProduct::buildLineKey($this->customer->phone, $this->tenantId, $fieldValues);

        // Check if product exists
        $existingProduct = CustomerProduct::findByUniqueKey($this->tenantId, $this->customer->id, $uniqueKey);

        if ($existingProduct) {
            // Update existing product
            $existingProduct->field_values = $fieldValues;
            $existingProduct->line_key = $lineKey;
            $existingProduct->save();

            return [
                'action' => 'product_updated',
                'product_id' => $existingProduct->id,
                'unique_key' => $uniqueKey,
            ];
        }

        // Create new product
        $product = CustomerProduct::create([
            'tenant_id' => $this->tenantId,
            'customer_id' => $this->customer->id,
            'field_values' => $fieldValues,
            'unique_key' => $uniqueKey,
            'line_key' => $lineKey,
            'status' => 'pending',
        ]);

        // Save product data to lead
        if ($this->lead) {
            $this->lead->addProductData($fieldValues);
        }

        return [
            'action' => 'product_created',
            'product_id' => $product->id,
            'unique_key' => $uniqueKey,
        ];
    }

    /**
     * Get next global question to ask
     */
    protected function getNextGlobalQuestion(): ?GlobalQuestion
    {
        $globalQuestions = GlobalQuestion::where('tenant_id', $this->tenantId)
            ->active()
            ->ordered()
            ->get();

        foreach ($globalQuestions as $question) {
            $fieldName = $question->field_name;

            // Check if already asked
            if ($this->customer->wasGlobalAsked($fieldName)) {
                continue;
            }

            // Check if already has value
            if ($this->customer->getGlobalField($fieldName)) {
                continue;
            }

            // Check trigger position
            if ($question->trigger_position === 'first') {
                return $question;
            }

            // Check if should ask after specific field
            if ($question->trigger_position === 'after_field') {
                $triggerField = $question->trigger_after_field;
                if ($this->state->getCompletedField($triggerField)) {
                    return $question;
                }
            }
        }

        return null;
    }

    /**
     * Get next questionnaire field
     */
    protected function getNextField(): ?ProductQuestion
    {
        $fields = ProductQuestion::where('tenant_id', $this->tenantId)
            ->active()
            ->ordered()
            ->get();

        foreach ($fields as $field) {
            // Skip if already completed
            if ($this->state->getCompletedField($field->field_name)) {
                continue;
            }

            // Check for global question that should be asked after this field
            $globalQuestion = $this->getGlobalQuestionAfterField($field->field_name);
            if ($globalQuestion) {
                return null; // Will be handled by getNextGlobalQuestion
            }

            // If required or unique key field, return it
            if ($field->is_required || $field->is_unique_key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Check if there's a pending global question after a field
     */
    protected function getGlobalQuestionAfterField(string $fieldName): ?GlobalQuestion
    {
        $completedField = $this->state->getCompletedField($fieldName);
        if (!$completedField) {
            return null;
        }

        return GlobalQuestion::where('tenant_id', $this->tenantId)
            ->where('trigger_position', 'after_field')
            ->where('trigger_after_field', $fieldName)
            ->active()
            ->get()
            ->filter(
                fn($question) =>
                !$this->customer->wasGlobalAsked($question->field_name)
                && !$this->customer->getGlobalField($question->field_name)
            )
            ->first();
    }

    /**
     * Get question text from template
     */
    protected function getQuestionText(string $fieldName, string $language = 'hi'): string
    {
        return QuestionTemplate::getQuestionText($this->tenantId, $fieldName, $language);
    }

    /**
     * Get completion message
     */
    protected function getCompletionMessage(string $language = 'hi'): string
    {
        $messages = [
            'hi' => 'Dhanyavaad! Aapka order note ho gaya hai.',
            'en' => 'Thank you! Your order has been noted.',
            'gu' => 'આભાર! તમારો ઓર્ડર નોંધાઈ ગયો છે.',
        ];

        return $messages[$language] ?? $messages['en'];
    }

    /**
     * Reset questionnaire state for new product
     */
    public function reset(): void
    {
        $this->state->reset();
    }

    /**
     * Get current state summary
     */
    public function getStateSummary(): array
    {
        return [
            'current_field' => $this->state->current_field,
            'completed_fields' => $this->state->completed_fields ?? [],
            'global_fields' => $this->customer->global_fields ?? [],
            'global_asked' => $this->customer->global_asked ?? [],
            'pending_products' => $this->customer->products()->pending()->count(),
            'confirmed_products' => $this->customer->products()->confirmed()->count(),
        ];
    }

    // ================================================
    // FLOWCHART-BASED PROCESSING (NEW)
    // ================================================

    /**
     * Check if tenant has flowchart nodes configured
     */
    public function hasFlowchartNodes(): bool
    {
        return \App\Models\QuestionnaireNode::where('admin_id', $this->tenantId)
            ->where('node_type', 'start')
            ->exists();
    }

    /**
     * Get current node ID from customer state
     */
    protected function getCurrentNodeId(): ?int
    {
        return $this->state->completed_fields['_current_node_id'] ?? null;
    }

    /**
     * Set current node ID in customer state
     */
    protected function setCurrentNodeId(int $nodeId): void
    {
        $fields = $this->state->completed_fields ?? [];
        $fields['_current_node_id'] = $nodeId;
        $this->state->completed_fields = $fields;
        $this->state->save();
    }

    /**
     * Get next question from flowchart
     */
    public function getNextQuestionFromFlowchart(): array
    {
        $language = $this->customer->detected_language ?? 'hi';

        // Get current node, or start node if none
        $currentNodeId = $this->getCurrentNodeId();

        if (!$currentNodeId) {
            // Find start node
            $startNode = \App\Models\QuestionnaireNode::getStartNode($this->tenantId);
            if (!$startNode) {
                return $this->getNextQuestion(); // Fallback to linear
            }

            // Move to first connected node
            $nextNode = $startNode->getNextNode();
            if (!$nextNode) {
                return [
                    'type' => 'complete',
                    'field' => null,
                    'question' => $this->getCompletionMessage($language),
                    'options' => [],
                ];
            }

            $this->setCurrentNodeId($nextNode->id);
            $currentNodeId = $nextNode->id;
        }

        $currentNode = \App\Models\QuestionnaireNode::find($currentNodeId);
        if (!$currentNode) {
            return $this->getNextQuestion(); // Fallback
        }

        // Process based on node type
        switch ($currentNode->node_type) {
            case \App\Models\QuestionnaireNode::TYPE_QUESTION:
                $config = $currentNode->config ?? [];

                // Get ask_digit from node or config (for optional questions)
                $askDigit = $currentNode->ask_digit ?? $config['ask_digit'] ?? 1;
                $isRequired = $currentNode->is_required ?? ($config['is_required'] ?? true);

                // Get lead_status_id from node (new column) or config (legacy)
                $leadStatusId = $currentNode->lead_status_id ?? $config['lead_status_id'] ?? null;

                // Check if this is a GlobalQuestion node
                if ($currentNode->global_question_id) {
                    $globalQuestion = $currentNode->globalQuestion;
                    if ($globalQuestion) {
                        return [
                            'type' => 'flowchart_global',
                            'node_id' => $currentNode->id,
                            'field' => $globalQuestion->field_name,
                            'question' => $globalQuestion->question ?? $this->getQuestionText($globalQuestion->field_name, $language),
                            'options' => $globalQuestion->options ?? [],
                            'is_required' => $isRequired,
                            'ask_digit' => $askDigit,
                            'lead_status_id' => $leadStatusId,
                            'is_global' => true,
                        ];
                    }
                }

                // Check if linked to ProductQuestion
                $productQuestion = $currentNode->questionnaireField;
                $options = $config['options'] ?? [];
                $fieldName = $config['field_name'] ?? 'field_' . $currentNode->id;
                $isQtyField = false;
                $isUniqueField = $currentNode->is_unique_field ?? ($config['is_unique_field'] ?? false);

                if ($productQuestion) {
                    $fieldName = $productQuestion->field_name;
                    $isQtyField = $productQuestion->is_qty_field;
                    $isUniqueField = $isUniqueField || $productQuestion->is_unique_field;

                    // Get filtered options for cascading filter
                    $collectedData = $this->getCollectedDataForFilter();
                    $filteredOptions = $productQuestion->getFilteredOptions($collectedData);

                    if (!empty($filteredOptions)) {
                        $options = $filteredOptions;
                    } elseif (empty($options)) {
                        $options = $productQuestion->getOptions();
                    }

                    // For quantity fields, don't show options - direct input
                    if ($isQtyField) {
                        $options = [];
                    }
                }

                return [
                    'type' => 'flowchart',
                    'node_id' => $currentNode->id,
                    'field' => $fieldName,
                    'question' => $config['question_text'] ?? $this->getQuestionText($fieldName, $language),
                    'options' => $options,
                    'is_required' => $isRequired,
                    'ask_digit' => $askDigit, // How many times to ask optional question
                    'lead_status_id' => $leadStatusId, // Status to set after answering
                    'is_unique_field' => $isUniqueField,
                    'is_qty_field' => $isQtyField,
                ];

            case \App\Models\QuestionnaireNode::TYPE_CONDITION:
                // Auto-process condition and move to next node
                $lastAnswer = $this->state->completed_fields['_last_answer'] ?? null;
                $nextNode = $currentNode->getNextNode($lastAnswer);

                if ($nextNode) {
                    $this->setCurrentNodeId($nextNode->id);
                    return $this->getNextQuestionFromFlowchart(); // Recursive call
                }
                return [
                    'type' => 'complete',
                    'field' => null,
                    'question' => $this->getCompletionMessage($language),
                    'options' => [],
                ];

            case \App\Models\QuestionnaireNode::TYPE_ACTION:
                $config = $currentNode->config ?? [];
                $message = $config['message'] ?? '';

                // Move to next node
                $nextNode = $currentNode->getNextNode();
                if ($nextNode) {
                    $this->setCurrentNodeId($nextNode->id);
                }

                return [
                    'type' => 'action',
                    'node_id' => $currentNode->id,
                    'field' => null,
                    'question' => $message,
                    'options' => [],
                    'action_type' => $config['action_type'] ?? 'message',
                ];

            case \App\Models\QuestionnaireNode::TYPE_END:
                // Reset for next conversation
                $this->resetFlowchart();
                return [
                    'type' => 'complete',
                    'field' => null,
                    'question' => $this->getCompletionMessage($language),
                    'options' => [],
                ];

            default:
                // Move to next
                $nextNode = $currentNode->getNextNode();
                if ($nextNode) {
                    $this->setCurrentNodeId($nextNode->id);
                    return $this->getNextQuestionFromFlowchart();
                }
                return [
                    'type' => 'complete',
                    'field' => null,
                    'question' => $this->getCompletionMessage($language),
                    'options' => [],
                ];
        }
    }

    /**
     * Process flowchart response
     */
    public function processFlowchartResponse(string $answer): array
    {
        $currentNodeId = $this->getCurrentNodeId();
        if (!$currentNodeId) {
            return ['action' => 'no_node'];
        }

        $currentNode = \App\Models\QuestionnaireNode::find($currentNodeId);
        if (!$currentNode) {
            return ['action' => 'node_not_found'];
        }

        // Store answer in state
        $fields = $this->state->completed_fields ?? [];
        $fields['_last_answer'] = $answer;

        if ($currentNode->node_type === \App\Models\QuestionnaireNode::TYPE_QUESTION) {
            $config = $currentNode->config ?? [];

            // Handle GlobalQuestion nodes
            if ($currentNode->global_question_id) {
                $globalQuestion = $currentNode->globalQuestion;
                if ($globalQuestion) {
                    $fieldName = $globalQuestion->field_name;
                    $fields[$fieldName] = $answer;

                    // Save to customer global field
                    $this->customer->setGlobalField($fieldName, $answer);
                    $this->customer->markGlobalAsked($fieldName);

                    // Save to lead's collected_data under global_questions
                    if ($this->lead) {
                        $this->lead->addCollectedData($fieldName, $answer, 'global_questions');
                    }
                }
            } else {
                // Regular ProductQuestion node
                $fieldName = $config['field_name'] ?? 'field_' . $currentNode->id;

                // If linked to ProductQuestion, use its field_name
                if ($currentNode->questionnaire_field_id) {
                    $productQuestion = $currentNode->questionnaireField;
                    if ($productQuestion) {
                        $fieldName = $productQuestion->field_name;
                    }
                }

                $fields[$fieldName] = $answer;

                // Also save to state completed fields
                $this->state->setCompletedField($fieldName, $answer);

                // Save to lead's collected_data under product_questions
                if ($this->lead) {
                    $this->lead->addCollectedData($fieldName, $answer, 'product_questions');
                }
            }

            // Update lead status if configured (from node column or config)
            $leadStatusId = $currentNode->lead_status_id ?? $config['lead_status_id'] ?? null;
            if ($leadStatusId && $this->lead) {
                $this->lead->update(['lead_status_id' => $leadStatusId]);
            }
        }

        $this->state->completed_fields = $fields;
        $this->state->save();

        // Move to next node based on answer
        $nextNode = $currentNode->getNextNode($answer);

        if ($nextNode) {
            $this->setCurrentNodeId($nextNode->id);
            return [
                'action' => 'moved_to_next',
                'from_node' => $currentNode->id,
                'to_node' => $nextNode->id,
                'answer' => $answer,
            ];
        }

        // No next node - complete
        $this->resetFlowchart();
        return [
            'action' => 'completed',
            'answer' => $answer,
        ];
    }

    /**
     * Reset flowchart state
     */
    public function resetFlowchart(): void
    {
        $fields = $this->state->completed_fields ?? [];
        unset($fields['_current_node_id']);
        unset($fields['_last_answer']);
        $this->state->completed_fields = $fields;
        $this->state->save();
    }

    /**
     * Smart get next question - uses flowchart if available, otherwise linear
     */
    public function getNextQuestionSmart(): array
    {
        if ($this->hasFlowchartNodes()) {
            return $this->getNextQuestionFromFlowchart();
        }
        return $this->getNextQuestion();
    }

    /**
     * Smart process response - uses flowchart if available, otherwise linear
     */
    public function processResponseSmart(string $fieldName, string $value): array
    {
        if ($this->hasFlowchartNodes()) {
            return $this->processFlowchartResponse($value);
        }
        return $this->processResponse($fieldName, $value);
    }
}
