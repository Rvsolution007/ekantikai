<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireField;
use App\Models\GlobalQuestion;
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
            ->where('is_active', true)
            ->count();

        $globalQuestions = GlobalQuestion::where('admin_id', $adminId)
            ->where('is_active', true)
            ->count();

        // Determine connection statuses
        $nodes = [
            'bot' => [
                'id' => 'bot',
                'label' => 'WhatsApp Bot',
                'icon' => '🤖',
                'connected' => !empty($admin->whatsapp_instance) && $admin->whatsapp_connected,
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
                'instance' => $admin->whatsapp_instance ?? 'Not connected',
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

        if (empty($admin->whatsapp_instance)) {
            return ['type' => 'warning', 'code' => 'BOT_002', 'message' => 'WhatsApp instance not configured'];
        }

        if (!$admin->whatsapp_connected) {
            return ['type' => 'warning', 'code' => 'BOT_003', 'message' => 'WhatsApp instance configured but not connected: ' . $admin->whatsapp_instance];
        }

        return ['type' => 'success', 'code' => 'BOT_OK', 'message' => 'WhatsApp Bot connected: ' . $admin->whatsapp_instance];
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
        $fields = GlobalQuestion::where('admin_id', $adminId)
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
                // Use incoming connections relationship
                $hasIncoming = $node->incomingConnections()->exists();

                if (!$hasIncoming) {
                    $orphanCount++;
                }
            }

            // Check for dead ends (question nodes without outgoing connections)
            if ($node->node_type === 'question') {
                $hasOutgoing = $node->outgoingConnections()->exists();
                if (!$hasOutgoing) {
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

    /**
     * Get detailed information for a specific node type
     */
    public function getNodeDetails(int $adminId, string $nodeType): array
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return ['error' => 'Admin not found'];
        }

        switch ($nodeType) {
            case 'bot':
                return $this->getBotDetails($admin);
            case 'flowchart':
                return $this->getFlowchartDetails($adminId);
            case 'catalogue':
                return $this->getCatalogueDetails($adminId);
            case 'leads':
                return $this->getLeadsDetails($adminId);
            case 'chats':
                return $this->getChatsDetails($adminId);
            case 'product_questions':
                return $this->getProductQuestionsDetails($adminId);
            case 'global_questions':
                return $this->getGlobalQuestionsDetails($adminId);
            default:
                return ['error' => 'Unknown node type'];
        }
    }

    private function getBotDetails($admin): array
    {
        return [
            'title' => 'WhatsApp Bot Configuration',
            'icon' => '🤖',
            'status' => !empty($admin->whatsapp_instance) && $admin->whatsapp_connected ? 'connected' : 'disconnected',
            'fields' => [
                ['label' => 'Instance Name', 'value' => $admin->whatsapp_instance ?? 'Not set', 'connected' => !empty($admin->whatsapp_instance)],
                ['label' => 'API URL', 'value' => $admin->whatsapp_api_url ? 'Configured' : 'Not set', 'connected' => !empty($admin->whatsapp_api_url)],
                ['label' => 'API Key', 'value' => $admin->whatsapp_api_key ? '••••••••' : 'Not set', 'connected' => !empty($admin->whatsapp_api_key)],
                ['label' => 'Connection Status', 'value' => $admin->whatsapp_connected ? 'Connected' : 'Disconnected', 'connected' => $admin->whatsapp_connected],
                ['label' => 'AI Model', 'value' => $admin->ai_model ?? 'Default', 'connected' => true],
                ['label' => 'System Prompt', 'value' => !empty($admin->ai_system_prompt) ? 'Configured' : 'Not set', 'connected' => !empty($admin->ai_system_prompt)],
            ],
        ];
    }

    private function getFlowchartDetails(int $adminId): array
    {
        $nodes = QuestionnaireNode::where('admin_id', $adminId)->get();
        $connections = \App\Models\QuestionnaireConnection::whereHas('sourceNode', fn($q) => $q->where('admin_id', $adminId))->count();

        $nodesByType = $nodes->groupBy('node_type');

        $fields = [];
        $fields[] = ['label' => 'Total Nodes', 'value' => $nodes->count(), 'connected' => $nodes->count() > 0];
        $fields[] = ['label' => 'Total Connections', 'value' => $connections, 'connected' => $connections > 0];
        $fields[] = ['label' => 'Start Nodes', 'value' => $nodesByType->get('start', collect())->count(), 'connected' => $nodesByType->get('start', collect())->count() > 0];
        $fields[] = ['label' => 'Question Nodes', 'value' => $nodesByType->get('question', collect())->count(), 'connected' => $nodesByType->get('question', collect())->count() > 0];
        $fields[] = ['label' => 'End Nodes', 'value' => $nodesByType->get('end', collect())->count(), 'connected' => true];

        // List individual nodes
        $nodesList = $nodes->map(fn($n) => [
            'id' => $n->id,
            'label' => $n->label,
            'type' => $n->node_type,
            'hasOutgoing' => $n->outgoingConnections()->exists(),
            'hasIncoming' => $n->incomingConnections()->exists() || $n->node_type === 'start',
            'fieldLinked' => !empty($n->questionnaire_field_id),
        ])->toArray();

        return [
            'title' => 'Flowchart Builder',
            'icon' => '📊',
            'status' => $nodes->count() > 0 ? 'connected' : 'disconnected',
            'fields' => $fields,
            'nodes' => $nodesList,
        ];
    }

    private function getCatalogueDetails(int $adminId): array
    {
        $products = Catalogue::where('admin_id', $adminId)->count();
        $fields = CatalogueField::where('admin_id', $adminId)->get();

        $fieldsList = $fields->map(fn($f) => [
            'label' => $f->display_name ?? $f->field_key,
            'value' => $f->field_key,
            'connected' => true,
        ])->toArray();

        return [
            'title' => 'Catalogue Data',
            'icon' => '📦',
            'status' => $products > 0 ? 'connected' : 'disconnected',
            'fields' => array_merge([
                ['label' => 'Total Products', 'value' => $products, 'connected' => $products > 0],
                ['label' => 'Catalogue Fields', 'value' => $fields->count(), 'connected' => $fields->count() > 0],
            ], $fieldsList),
        ];
    }

    private function getLeadsDetails(int $adminId): array
    {
        $leads = Lead::where('admin_id', $adminId)->get();
        $byStage = $leads->groupBy('stage');
        $byQuality = $leads->groupBy('lead_quality');

        return [
            'title' => 'Leads',
            'icon' => '👥',
            'status' => $leads->count() > 0 ? 'connected' : 'disconnected',
            'fields' => [
                ['label' => 'Total Leads', 'value' => $leads->count(), 'connected' => $leads->count() > 0],
                ['label' => 'New', 'value' => $byStage->get('new_lead', collect())->count(), 'connected' => true],
                ['label' => 'Qualified', 'value' => $byStage->get('qualified', collect())->count(), 'connected' => true],
                ['label' => 'Hot Leads', 'value' => $byQuality->get('hot', collect())->count(), 'connected' => true],
                ['label' => 'Warm Leads', 'value' => $byQuality->get('warm', collect())->count(), 'connected' => true],
                ['label' => 'Cold Leads', 'value' => $byQuality->get('cold', collect())->count(), 'connected' => true],
            ],
        ];
    }

    private function getChatsDetails(int $adminId): array
    {
        $chats = WhatsappChat::where('admin_id', $adminId)->count();
        $customers = Customer::where('admin_id', $adminId)->count();
        $today = WhatsappChat::where('admin_id', $adminId)->whereDate('created_at', today())->count();

        return [
            'title' => 'Chat History',
            'icon' => '💬',
            'status' => $chats > 0 ? 'connected' : 'disconnected',
            'fields' => [
                ['label' => 'Total Messages', 'value' => $chats, 'connected' => $chats > 0],
                ['label' => 'Total Customers', 'value' => $customers, 'connected' => $customers > 0],
                ['label' => 'Messages Today', 'value' => $today, 'connected' => true],
            ],
        ];
    }

    private function getProductQuestionsDetails(int $adminId): array
    {
        $fields = QuestionnaireField::where('admin_id', $adminId)
            ->get();

        $catalogueFields = CatalogueField::where('admin_id', $adminId)->pluck('field_key')->toArray();

        $fieldsList = $fields->map(function ($f) use ($catalogueFields, $adminId) {
            $inFlowchart = QuestionnaireNode::where('admin_id', $adminId)
                ->where('questionnaire_field_id', $f->id)
                ->exists();
            $matchesCatalogue = in_array($f->field_key, $catalogueFields);

            return [
                'label' => $f->display_name ?? $f->field_name,
                'value' => $f->field_key,
                'connected' => $f->is_active && $inFlowchart,
                'inFlowchart' => $inFlowchart,
                'matchesCatalogue' => $matchesCatalogue,
                'active' => $f->is_active,
            ];
        })->toArray();

        return [
            'title' => 'Product Questions',
            'icon' => '🏷️',
            'status' => $fields->where('is_active', true)->count() > 0 ? 'connected' : 'disconnected',
            'fields' => array_merge([
                ['label' => 'Total Fields', 'value' => $fields->count(), 'connected' => $fields->count() > 0],
                ['label' => 'Active Fields', 'value' => $fields->where('is_active', true)->count(), 'connected' => $fields->where('is_active', true)->count() > 0],
            ], $fieldsList),
        ];
    }

    private function getGlobalQuestionsDetails(int $adminId): array
    {
        $fields = GlobalQuestion::where('admin_id', $adminId)
            ->get();

        $fieldsList = $fields->map(function ($f) {
            return [
                'label' => $f->display_name ?? $f->question_name,
                'value' => $f->field_name ?? 'N/A',
                'connected' => $f->is_active,
                'active' => $f->is_active,
            ];
        })->toArray();

        return [
            'title' => 'Global Questions',
            'icon' => '🌐',
            'status' => $fields->where('is_active', true)->count() > 0 ? 'connected' : 'disconnected',
            'fields' => array_merge([
                ['label' => 'Total Questions', 'value' => $fields->count(), 'connected' => $fields->count() > 0],
                ['label' => 'Active Questions', 'value' => $fields->where('is_active', true)->count(), 'connected' => $fields->where('is_active', true)->count() > 0],
            ], $fieldsList),
        ];
    }
}
