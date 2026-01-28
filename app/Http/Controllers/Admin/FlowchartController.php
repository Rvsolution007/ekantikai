<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireConnection;
use App\Models\ProductQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlowchartController extends Controller
{
    /**
     * Display the flowchart builder
     */
    public function index()
    {
        $adminId = auth('admin')->id();

        // Get all questions for dropdown
        $productQuestions = ProductQuestion::where('admin_id', $adminId)
            ->active()
            ->ordered()
            ->get(['id', 'field_name', 'display_name']);

        $globalQuestions = \App\Models\GlobalQuestion::where('admin_id', $adminId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'field_name', 'display_name']);

        // Get lead statuses for Lead Status dropdown in flowchart
        $leadStatuses = \App\Models\LeadStatus::where('admin_id', $adminId)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'color']);

        return view('admin.workflow.flowchart.index', [
            'productQuestions' => $productQuestions,
            'globalQuestions' => $globalQuestions,
            'leadStatuses' => $leadStatuses,
        ]);
    }

    /**
     * Get flowchart data as JSON for React Flow
     */
    public function getData()
    {
        $adminId = auth('admin')->id();

        $nodes = QuestionnaireNode::where('admin_id', $adminId)
            ->active()
            ->get()
            ->map(fn($node) => $node->toReactFlowNode());

        $edges = QuestionnaireConnection::where('admin_id', $adminId)
            ->get()
            ->map(fn($edge) => $edge->toReactFlowEdge());

        return response()->json([
            'nodes' => $nodes->values(),
            'edges' => $edges->values(),
        ]);
    }

    /**
     * Save a node (create or update)
     */
    public function saveNode(Request $request)
    {
        $request->validate([
            'id' => 'nullable|string',
            'type' => 'required|in:start,question,condition,action,end',
            'position' => 'required|array',
            'position.x' => 'required|numeric',
            'position.y' => 'required|numeric',
            'data' => 'required|array',
            'data.label' => 'required|string|max:100',
        ]);

        $adminId = auth('admin')->id();
        $nodeId = $request->input('id');

        DB::beginTransaction();
        try {
            $data = [
                'admin_id' => $adminId,
                'node_type' => $request->input('type'),
                'label' => $request->input('data.label'),
                'config' => $request->input('data.config', []),
                'pos_x' => (int) $request->input('position.x'),
                'pos_y' => (int) $request->input('position.y'),
            ];

            // Handle new fields: global_question_id and lead_status_id
            if ($request->has('data.globalQuestionId')) {
                $data['global_question_id'] = $request->input('data.globalQuestionId');
            }
            if ($request->has('data.leadStatusId')) {
                $data['lead_status_id'] = $request->input('data.leadStatusId');
            }
            if ($request->has('data.fieldId')) {
                $data['questionnaire_field_id'] = $request->input('data.fieldId');
            }

            $warning = null;

            if ($nodeId && !str_starts_with($nodeId, 'new_')) {
                // Update existing node
                $node = QuestionnaireNode::where('id', $nodeId)
                    ->where('admin_id', $adminId)
                    ->firstOrFail();

                // Validate lead status ordering (warning only)
                if (isset($data['lead_status_id'])) {
                    [$isValid, $message] = $node->validateStatusId($data['lead_status_id']);
                    if (!$isValid) {
                        $warning = $message;
                    }
                }

                $node->update($data);
            } else {
                // Create new node
                $node = QuestionnaireNode::create($data);

                // If it's a question node, also create/link QuestionnaireField for sync
                if ($node->node_type === QuestionnaireNode::TYPE_QUESTION) {
                    $this->createLinkedField($node);
                }
            }

            // Sync to linked field if exists
            $node->syncToField();

            DB::commit();

            $response = [
                'success' => true,
                'node' => $node->toReactFlowNode(),
            ];

            // Include warning if status ordering issue
            if ($warning) {
                $response['warning'] = $warning;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a node and its connections
     */
    public function deleteNode(QuestionnaireNode $node)
    {
        $adminId = auth('admin')->id();

        if ($node->admin_id !== $adminId) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Delete associated connections
            QuestionnaireConnection::where('source_node_id', $node->id)
                ->orWhere('target_node_id', $node->id)
                ->delete();

            // Delete linked field if exists
            if ($node->questionnaire_field_id) {
                ProductQuestion::where('id', $node->questionnaire_field_id)->delete();
            }

            $node->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save a connection (create or update)
     */
    public function saveConnection(Request $request)
    {
        $request->validate([
            'id' => 'nullable|string',
            'source' => 'required|string',
            'target' => 'required|string',
            'sourceHandle' => 'nullable|string',
            'targetHandle' => 'nullable|string',
            'label' => 'nullable|string|max:50',
            'data' => 'nullable|array',
        ]);

        $adminId = auth('admin')->id();

        // Verify nodes belong to tenant
        $sourceNode = QuestionnaireNode::where('id', $request->source)
            ->where('admin_id', $adminId)
            ->firstOrFail();
        $targetNode = QuestionnaireNode::where('id', $request->target)
            ->where('admin_id', $adminId)
            ->firstOrFail();

        $connectionId = $request->input('id');

        $data = [
            'admin_id' => $adminId,
            'source_node_id' => $sourceNode->id,
            'target_node_id' => $targetNode->id,
            'source_handle' => $request->input('sourceHandle'),
            'target_handle' => $request->input('targetHandle'),
            'label' => $request->input('label'),
            'condition' => $request->input('data.condition'),
            'priority' => $request->input('data.priority', 0),
        ];

        if ($connectionId && !str_starts_with($connectionId, 'new_')) {
            $connection = QuestionnaireConnection::where('id', $connectionId)
                ->where('admin_id', $adminId)
                ->firstOrFail();
            $connection->update($data);
        } else {
            $connection = QuestionnaireConnection::create($data);
        }

        return response()->json([
            'success' => true,
            'edge' => $connection->toReactFlowEdge(),
        ]);
    }

    /**
     * Delete a connection
     */
    public function deleteConnection(QuestionnaireConnection $connection)
    {
        $adminId = auth('admin')->id();

        if ($connection->admin_id !== $adminId) {
            abort(403);
        }

        $connection->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Bulk save all nodes and connections
     */
    public function saveAll(Request $request)
    {
        $request->validate([
            'nodes' => 'required|array',
            'edges' => 'nullable|array',  // Allow empty edges
        ]);

        $adminId = auth('admin')->id();

        DB::beginTransaction();
        try {
            // Track old IDs to new IDs for edges
            $idMap = [];

            // BACKUP: Save existing ProductQuestion settings (is_unique_key, unique_key_order, is_unique_field, question_template)
            // These will be restored after recreating nodes to preserve admin's configuration
            $existingFieldSettings = ProductQuestion::where('admin_id', $adminId)
                ->get(['field_name', 'is_unique_key', 'unique_key_order', 'is_unique_field', 'is_required', 'options_manual', 'options_source', 'catalogue_field', 'question_template'])
                ->keyBy('field_name')
                ->toArray();

            // Delete existing nodes (cascade deletes connections)
            QuestionnaireNode::where('admin_id', $adminId)->delete();

            // Create nodes
            foreach ($request->input('nodes') as $nodeData) {
                $oldId = $nodeData['id'];
                $config = $nodeData['data']['config'] ?? [];

                $node = QuestionnaireNode::create([
                    'admin_id' => $adminId,
                    'node_type' => $nodeData['type'],
                    'label' => $nodeData['data']['label'] ?? 'Untitled',
                    'config' => $config,
                    'pos_x' => (int) ($nodeData['position']['x'] ?? 100),
                    'pos_y' => (int) ($nodeData['position']['y'] ?? 100),
                    'is_required' => $nodeData['data']['is_required'] ?? true, // Default to required
                    'ask_digit' => $config['ask_digit'] ?? 1,
                    'is_unique_field' => $config['is_unique_field'] ?? false,
                ]);

                $idMap[$oldId] = $node->id;

                // Create linked field for question nodes
                if ($node->node_type === QuestionnaireNode::TYPE_QUESTION) {
                    $this->createLinkedField($node);
                }
            }

            // Create edges with mapped IDs
            foreach ($request->input('edges', []) as $edgeData) {
                $sourceId = $idMap[$edgeData['source'] ?? ''] ?? null;
                $targetId = $idMap[$edgeData['target'] ?? ''] ?? null;

                if ($sourceId && $targetId) {
                    $edgeDataFields = $edgeData['data'] ?? [];
                    QuestionnaireConnection::create([
                        'admin_id' => $adminId,
                        'source_node_id' => $sourceId,
                        'target_node_id' => $targetId,
                        'source_handle' => $edgeData['sourceHandle'] ?? null,
                        'target_handle' => $edgeData['targetHandle'] ?? null,
                        'label' => $edgeData['label'] ?? null,
                        'condition' => $edgeDataFields['condition'] ?? null,
                        'priority' => $edgeDataFields['priority'] ?? 0,
                    ]);
                }
            }

            // RESTORE: Apply backed up ProductQuestion settings to recreated fields
            // This preserves is_unique_key, unique_key_order, is_unique_field, question_template that admin had set
            foreach ($existingFieldSettings as $fieldName => $settings) {
                ProductQuestion::where('admin_id', $adminId)
                    ->where('field_name', $fieldName)
                    ->update([
                        'is_unique_key' => $settings['is_unique_key'] ?? false,
                        'unique_key_order' => $settings['unique_key_order'],
                        'is_unique_field' => $settings['is_unique_field'] ?? false,
                        'question_template' => $settings['question_template'] ?? null,
                    ]);
            }

            // Reorder QuestionnaireFields based on flowchart
            $this->syncFieldOrderFromFlowchart($adminId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flowchart saved successfully',
                'idMap' => $idMap,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all flowchart nodes and connections
     */
    public function clearFlow()
    {
        $adminId = auth('admin')->id();

        DB::beginTransaction();
        try {
            QuestionnaireConnection::where('admin_id', $adminId)->delete();
            QuestionnaireNode::where('admin_id', $adminId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Flowchart cleared',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialize flowchart from existing QuestionnaireFields
     */
    protected function initializeFromFields(int $adminId): void
    {
        $fields = ProductQuestion::where('admin_id', $adminId)
            ->active()
            ->ordered()
            ->get();

        if ($fields->isEmpty()) {
            // Create just a start and end node
            $startNode = QuestionnaireNode::create([
                'admin_id' => $adminId,
                'node_type' => QuestionnaireNode::TYPE_START,
                'label' => 'Start',
                'pos_x' => 250,
                'pos_y' => 50,
            ]);

            $endNode = QuestionnaireNode::create([
                'admin_id' => $adminId,
                'node_type' => QuestionnaireNode::TYPE_END,
                'label' => 'End',
                'pos_x' => 250,
                'pos_y' => 200,
            ]);

            QuestionnaireConnection::create([
                'admin_id' => $adminId,
                'source_node_id' => $startNode->id,
                'target_node_id' => $endNode->id,
            ]);

            return;
        }

        // Create start node
        $startNode = QuestionnaireNode::create([
            'admin_id' => $adminId,
            'node_type' => QuestionnaireNode::TYPE_START,
            'label' => 'Start',
            'pos_x' => 250,
            'pos_y' => 50,
        ]);

        $previousNode = $startNode;
        $yPosition = 150;

        // Create question nodes from fields
        foreach ($fields as $index => $field) {
            $questionNode = QuestionnaireNode::create([
                'admin_id' => $adminId,
                'node_type' => QuestionnaireNode::TYPE_QUESTION,
                'label' => $field->display_name,
                'config' => [
                    'field_name' => $field->field_name,
                    'display_name' => $field->display_name,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'is_unique_key' => $field->is_unique_key,
                    'options' => $field->options_manual,
                    'options_source' => $field->options_source,
                    'catalogue_field' => $field->catalogue_field,
                ],
                'pos_x' => 250,
                'pos_y' => $yPosition,
                'questionnaire_field_id' => $field->id,
            ]);

            // Connect to previous node
            QuestionnaireConnection::create([
                'admin_id' => $adminId,
                'source_node_id' => $previousNode->id,
                'target_node_id' => $questionNode->id,
            ]);

            $previousNode = $questionNode;
            $yPosition += 120;
        }

        // Create end node
        $endNode = QuestionnaireNode::create([
            'admin_id' => $adminId,
            'node_type' => QuestionnaireNode::TYPE_END,
            'label' => 'Complete',
            'pos_x' => 250,
            'pos_y' => $yPosition,
        ]);

        // Connect last question to end
        QuestionnaireConnection::create([
            'admin_id' => $adminId,
            'source_node_id' => $previousNode->id,
            'target_node_id' => $endNode->id,
        ]);
    }

    /**
     * Create a linked ProductQuestion for a question node
     */
    protected function createLinkedField(QuestionnaireNode $node): void
    {
        $config = $node->config ?? [];
        $fieldName = $config['field_name'] ?? 'field_' . $node->id;

        $adminId = $node->admin_id;

        // Get max sort order
        $maxOrder = ProductQuestion::where('admin_id', $adminId)->max('sort_order') ?? 0;

        // Use updateOrCreate to avoid duplicate key errors
        $field = ProductQuestion::updateOrCreate(
            [
                'admin_id' => $adminId,
                'field_name' => $fieldName,
            ],
            [
                'display_name' => $config['display_name'] ?? $node->label,
                'question_template' => $config['question_template'] ?? null,
                'field_type' => $config['field_type'] ?? 'text',
                'is_required' => $config['is_required'] ?? false,
                'is_unique_key' => $config['is_unique_key'] ?? false,
                'options_manual' => $config['options'] ?? null,
                'options_source' => $config['options_source'] ?? 'manual',
                'catalogue_field' => $config['catalogue_field'] ?? null,
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
            ]
        );

        $node->questionnaire_field_id = $field->id;
        $node->save();
    }

    /**
     * Sync QuestionnaireField sort_order based on flowchart connections
     */
    protected function syncFieldOrderFromFlowchart(int $adminId): void
    {
        $startNode = QuestionnaireNode::getStartNode($adminId);
        if (!$startNode) {
            return;
        }

        $visited = [];
        $order = 1;
        $currentNode = $startNode;

        while ($currentNode) {
            if (in_array($currentNode->id, $visited)) {
                break; // Prevent infinite loops
            }
            $visited[] = $currentNode->id;

            if ($currentNode->questionnaire_field_id) {
                ProductQuestion::where('id', $currentNode->questionnaire_field_id)
                    ->update(['sort_order' => $order]);
                $order++;
            }

            // Move to next node (first connection)
            $nextConnection = $currentNode->outgoingConnections()->orderBy('priority')->first();
            $currentNode = $nextConnection ? $nextConnection->targetNode : null;
        }
    }
}
