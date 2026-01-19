<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireField;
use App\Models\CatalogueField;
use App\Models\Catalogue;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\WhatsappChat;
use Illuminate\Support\Facades\Log;

class BotFlowTesterService
{
    /**
     * Run complete flow validation for an admin's bot setup
     */
    public function runFlowValidation(int $adminId): array
    {
        $errors = [];
        $warnings = [];
        $success = [];

        // 1. Validate Bot Connection
        $botStatus = $this->validateBotConnection($adminId);
        $this->categorizeResult($botStatus, $errors, $warnings, $success);

        // 2. Validate Flowchart
        $flowchartStatus = $this->validateFlowchart($adminId);
        $this->categorizeResult($flowchartStatus, $errors, $warnings, $success);

        // 3. Validate Catalogue
        $catalogueStatus = $this->validateCatalogue($adminId);
        $this->categorizeResult($catalogueStatus, $errors, $warnings, $success);

        // 4. Validate Product Questions
        $productQStatus = $this->validateProductQuestions($adminId);
        $this->categorizeResult($productQStatus, $errors, $warnings, $success);

        // 5. Validate Global Questions
        $globalQStatus = $this->validateGlobalQuestions($adminId);
        $this->categorizeResult($globalQStatus, $errors, $warnings, $success);

        // 6. Validate Node Connections
        $connectionStatus = $this->validateNodeConnections($adminId);
        $this->categorizeResult($connectionStatus, $errors, $warnings, $success);

        // 7. Validate Catalogue-Question Integration
        $integrationStatus = $this->validateCatalogueQuestionIntegration($adminId);
        $this->categorizeResult($integrationStatus, $errors, $warnings, $success);

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'success' => $success,
            'summary' => [
                'total_checks' => count($errors) + count($warnings) + count($success),
                'passed' => count($success),
                'warnings' => count($warnings),
                'failed' => count($errors),
            ]
        ];
    }

    /**
     * Get complete connection status for flowchart visualization
     */
    public function getConnectionsData(int $adminId): array
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return ['error' => 'Admin not found'];
        }

        // Get counts
        $flowchartCount = QuestionnaireNode::where('admin_id', $adminId)->count();
        $catalogueCount = Catalogue::where('admin_id', $adminId)->count();
        $leadsCount = Lead::where('admin_id', $adminId)->count();
        $chatsCount = WhatsappChat::where('admin_id', $adminId)->count();
        $customersCount = Customer::where('admin_id', $adminId)->count();

        $productQuestions = QuestionnaireField::where('admin_id', $adminId)
            ->where('field_type', 'product')
            ->where('is_active', true)
            ->count();

        $globalQuestions = QuestionnaireField::where('admin_id', $adminId)
            ->where('field_type', 'global')
            ->where('is_active', true)
            ->count();

        // Determine connection statuses
        $nodes = [
            'bot' => [
                'id' => 'bot',
                'label' => 'WhatsApp Bot',
                'icon' => '🤖',
                'connected' => !empty($admin->whatsapp_instance_name),
                'count' => $customersCount,
                'subtitle' => $customersCount . ' users',
                'x' => 400,
                'y' => 50,
            ],
            'flowchart' => [
                'id' => 'flowchart',
                'label' => 'Flowchart Builder',
                'icon' => '📊',
                'connected' => $flowchartCount > 0,
                'count' => $flowchartCount,
                'subtitle' => $flowchartCount . ' nodes',
                'x' => 200,
                'y' => 180,
            ],
            'catalogue' => [
                'id' => 'catalogue',
                'label' => 'Catalogue Data',
                'icon' => '📦',
                'connected' => $catalogueCount > 0,
                'count' => $catalogueCount,
                'subtitle' => $catalogueCount . ' products',
                'x' => 600,
                'y' => 180,
            ],
            'leads' => [
                'id' => 'leads',
                'label' => 'Leads',
                'icon' => '👥',
                'connected' => $leadsCount > 0,
                'count' => $leadsCount,
                'subtitle' => $leadsCount . ' leads',
                'x' => 150,
                'y' => 320,
            ],
            'chats' => [
                'id' => 'chats',
                'label' => 'Chat History',
                'icon' => '💬',
                'connected' => $chatsCount > 0,
                'count' => $chatsCount,
                'subtitle' => $chatsCount . ' messages',
                'x' => 650,
                'y' => 320,
            ],
            'product_questions' => [
                'id' => 'product_questions',
                'label' => 'Product Questions',
                'icon' => '🏷️',
                'connected' => $productQuestions > 0,
                'count' => $productQuestions,
                'subtitle' => $productQuestions . ' fields',
                'x' => 100,
                'y' => 460,
            ],
            'global_questions' => [
                'id' => 'global_questions',
                'label' => 'Global Questions',
                'icon' => '🌐',
                'connected' => $globalQuestions > 0,
                'count' => $globalQuestions,
                'subtitle' => $globalQuestions . ' fields',
                'x' => 300,
                'y' => 460,
            ],
        ];

        // Define connections between nodes
        $connections = [
            ['from' => 'bot', 'to' => 'flowchart', 'active' => $flowchartCount > 0],
            ['from' => 'bot', 'to' => 'catalogue', 'active' => $catalogueCount > 0],
            ['from' => 'bot', 'to' => 'leads', 'active' => $leadsCount > 0],
            ['from' => 'bot', 'to' => 'chats', 'active' => $chatsCount > 0],
            ['from' => 'flowchart', 'to' => 'product_questions', 'active' => $productQuestions > 0],
            ['from' => 'flowchart', 'to' => 'global_questions', 'active' => $globalQuestions > 0],
            ['from' => 'product_questions', 'to' => 'catalogue', 'active' => $productQuestions > 0 && $catalogueCount > 0],
        ];

        return [
            'nodes' => $nodes,
            'connections' => $connections,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'instance' => $admin->whatsapp_instance_name ?? 'Not connected',
            ]
        ];
    }

    /**
     * Validate bot connection
     */
    private function validateBotConnection(int $adminId): array
    {
        $admin = Admin::find($adminId);

        if (!$admin) {
            return ['type' => 'error', 'code' => 'BOT_001', 'message' => 'Admin not found'];
        }

        if (empty($admin->whatsapp_instance_name)) {
            return ['type' => 'warning', 'code' => 'BOT_002', 'message' => 'WhatsApp instance not configured'];
        }

        return ['type' => 'success', 'code' => 'BOT_OK', 'message' => 'WhatsApp Bot connected: ' . $admin->whatsapp_instance_name];
    }

    /**
     * Validate flowchart setup
     */
    private function validateFlowchart(int $adminId): array
    {
        $nodeCount = QuestionnaireNode::where('admin_id', $adminId)->count();

        if ($nodeCount === 0) {
            return ['type' => 'error', 'code' => 'FLOW_001', 'message' => 'No flowchart nodes created. Bot cannot ask questions.'];
        }

        $startNode = QuestionnaireNode::where('admin_id', $adminId)
            ->where('node_type', 'start')
            ->first();

        if (!$startNode) {
            return ['type' => 'warning', 'code' => 'FLOW_002', 'message' => 'No START node found. Flow may not work correctly.'];
        }

        return ['type' => 'success', 'code' => 'FLOW_OK', 'message' => "Flowchart configured with {$nodeCount} nodes"];
    }

    /**
     * Validate catalogue
     */
    private function validateCatalogue(int $adminId): array
    {
        $productCount = Catalogue::where('admin_id', $adminId)->count();

        if ($productCount === 0) {
            return ['type' => 'warning', 'code' => 'CAT_001', 'message' => 'No products in catalogue. Product search will not work.'];
        }

        $fieldCount = CatalogueField::where('admin_id', $adminId)->count();
        if ($fieldCount === 0) {
            return ['type' => 'warning', 'code' => 'CAT_002', 'message' => 'No catalogue fields defined.'];
        }

        return ['type' => 'success', 'code' => 'CAT_OK', 'message' => "{$productCount} products with {$fieldCount} fields"];
    }

    /**
     * Validate product questions
     */
    private function validateProductQuestions(int $adminId): array
    {
        $fields = QuestionnaireField::where('admin_id', $adminId)
            ->where('field_type', 'product')
            ->where('is_active', true)
            ->get();

        if ($fields->isEmpty()) {
            return ['type' => 'warning', 'code' => 'PQ_001', 'message' => 'No product questions configured.'];
        }

        // Check if any field is connected to flowchart
        $connectedCount = 0;
        foreach ($fields as $field) {
            $nodeExists = QuestionnaireNode::where('admin_id', $adminId)
                ->where('questionnaire_field_id', $field->id)
                ->exists();
            if ($nodeExists) {
                $connectedCount++;
            }
        }

        if ($connectedCount === 0) {
            return ['type' => 'warning', 'code' => 'PQ_002', 'message' => 'Product questions not connected to flowchart.'];
        }

        return ['type' => 'success', 'code' => 'PQ_OK', 'message' => "{$fields->count()} product questions, {$connectedCount} in flowchart"];
    }

    /**
     * Validate global questions
     */
    private function validateGlobalQuestions(int $adminId): array
    {
        $fields = QuestionnaireField::where('admin_id', $adminId)
            ->where('field_type', 'global')
            ->where('is_active', true)
            ->count();

        if ($fields === 0) {
            return ['type' => 'warning', 'code' => 'GQ_001', 'message' => 'No global questions configured.'];
        }

        return ['type' => 'success', 'code' => 'GQ_OK', 'message' => "{$fields} global questions active"];
    }

    /**
     * Validate node connections in flowchart
     */
    private function validateNodeConnections(int $adminId): array
    {
        $nodes = QuestionnaireNode::where('admin_id', $adminId)->get();

        if ($nodes->isEmpty()) {
            return ['type' => 'warning', 'code' => 'NC_001', 'message' => 'No nodes to validate.'];
        }

        $orphanCount = 0;
        $deadEndCount = 0;

        foreach ($nodes as $node) {
            // Check for orphan nodes (not connected from anywhere except start)
            if ($node->node_type !== 'start') {
                $hasIncoming = QuestionnaireNode::where('admin_id', $adminId)
                    ->where('next_node_id', $node->id)
                    ->exists();

                if (!$hasIncoming) {
                    // Check conditions
                    $hasConditionIncoming = QuestionnaireNode::where('admin_id', $adminId)
                        ->whereJsonContains('conditions', ['next_node_id' => $node->id])
                        ->exists();

                    if (!$hasConditionIncoming) {
                        $orphanCount++;
                    }
                }
            }

            // Check for dead ends (question nodes without next)
            if ($node->node_type === 'question' && !$node->next_node_id) {
                $hasConditions = !empty($node->conditions);
                if (!$hasConditions) {
                    $deadEndCount++;
                }
            }
        }

        if ($orphanCount > 0 || $deadEndCount > 0) {
            $issues = [];
            if ($orphanCount > 0)
                $issues[] = "{$orphanCount} orphan nodes";
            if ($deadEndCount > 0)
                $issues[] = "{$deadEndCount} dead-end nodes";
            return ['type' => 'warning', 'code' => 'NC_002', 'message' => 'Flow issues: ' . implode(', ', $issues)];
        }

        return ['type' => 'success', 'code' => 'NC_OK', 'message' => 'All nodes properly connected'];
    }

    /**
     * Validate catalogue-question integration
     */
    private function validateCatalogueQuestionIntegration(int $adminId): array
    {
        $productFields = QuestionnaireField::where('admin_id', $adminId)
            ->where('field_type', 'product')
            ->where('is_active', true)
            ->get();

        if ($productFields->isEmpty()) {
            return ['type' => 'warning', 'code' => 'INT_001', 'message' => 'No product fields to integrate.'];
        }

        $catalogueFields = CatalogueField::where('admin_id', $adminId)->pluck('field_key')->toArray();

        if (empty($catalogueFields)) {
            return ['type' => 'warning', 'code' => 'INT_002', 'message' => 'No catalogue fields to match.'];
        }

        $matchedCount = 0;
        foreach ($productFields as $field) {
            if (in_array($field->field_key, $catalogueFields)) {
                $matchedCount++;
            }
        }

        if ($matchedCount === 0) {
            return ['type' => 'warning', 'code' => 'INT_003', 'message' => 'Product questions not matching catalogue fields.'];
        }

        return ['type' => 'success', 'code' => 'INT_OK', 'message' => "{$matchedCount}/{$productFields->count()} questions match catalogue fields"];
    }

    /**
     * Helper to categorize results
     */
    private function categorizeResult(array $result, array &$errors, array &$warnings, array &$success): void
    {
        switch ($result['type']) {
            case 'error':
                $errors[] = $result;
                break;
            case 'warning':
                $warnings[] = $result;
                break;
            case 'success':
                $success[] = $result;
                break;
        }
    }
}
