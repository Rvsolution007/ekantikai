<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemConnectionsController extends Controller
{
    /**
     * Display system connections dashboard
     */
    public function index()
    {
        // Get all system connections data
        $connections = $this->getSystemConnections();
        $hardcodedItems = $this->getHardcodedItems();
        $fileConnections = $this->getFileConnections();
        $databaseConnections = $this->getDatabaseConnections();

        return view('superadmin.connections.index', compact(
            'connections',
            'hardcodedItems',
            'fileConnections',
            'databaseConnections'
        ));
    }

    /**
     * Get system component connections
     */
    private function getSystemConnections()
    {
        return [
            'catalogue' => [
                'name' => 'Catalogue',
                'icon' => '📦',
                'description' => 'Product catalog with dynamic fields',
                'tables' => ['catalogues', 'catalogue_fields'],
                'controllers' => ['CatalogueController', 'CatalogueFieldController'],
                'views' => 'admin/catalogue/',
                'model' => 'App\\Models\\Catalogue',
                'connects_to' => ['product_questions', 'webhook', 'ai_prompt'],
                'status' => $this->checkConnection('catalogues'),
            ],
            'product_questions' => [
                'name' => 'Product Questions',
                'icon' => '❓',
                'description' => 'Workflow fields like Category, Model, Size',
                'tables' => ['product_questions'],
                'controllers' => ['ProductQuestionController'],
                'views' => 'admin/workflow/fields/',
                'model' => 'App\\Models\\ProductQuestion',
                'connects_to' => ['flowchart', 'catalogue', 'ai_prompt'],
                'status' => $this->checkConnection('product_questions'),
            ],
            'global_questions' => [
                'name' => 'Global Questions',
                'icon' => '🌍',
                'description' => 'One-time questions like City, Business Name',
                'tables' => ['global_questions'],
                'controllers' => ['GlobalQuestionController'],
                'views' => 'admin/workflow/global/',
                'model' => 'App\\Models\\GlobalQuestion',
                'connects_to' => ['flowchart', 'ai_prompt'],
                'status' => $this->checkConnection('global_questions'),
            ],
            'flowchart' => [
                'name' => 'Flowchart',
                'icon' => '🔀',
                'description' => 'Visual conversation flow builder',
                'tables' => ['questionnaire_nodes', 'questionnaire_connections'],
                'controllers' => ['FlowchartController'],
                'views' => 'admin/workflow/flowchart/',
                'model' => 'App\\Models\\QuestionnaireNode',
                'connects_to' => ['product_questions', 'global_questions', 'webhook'],
                'status' => $this->checkConnection('questionnaire_nodes'),
            ],
            'leads' => [
                'name' => 'Leads',
                'icon' => '👤',
                'description' => 'Customer leads from WhatsApp',
                'tables' => ['leads', 'lead_products', 'lead_statuses'],
                'controllers' => ['LeadController', 'LeadStatusController'],
                'views' => 'admin/leads/',
                'model' => 'App\\Models\\Lead',
                'connects_to' => ['webhook', 'followups'],
                'status' => $this->checkConnection('leads'),
            ],
            'followups' => [
                'name' => 'Followups',
                'icon' => '📅',
                'description' => 'Auto followup templates',
                'tables' => ['followup_templates', 'followups'],
                'controllers' => ['FollowupController', 'FollowupTemplateController'],
                'views' => 'admin/followups/',
                'model' => 'App\\Models\\FollowupTemplate',
                'connects_to' => ['leads', 'webhook'],
                'status' => $this->checkConnection('followup_templates'),
            ],
            'ai_model' => [
                'name' => 'AI Model (Bot)',
                'icon' => '🤖',
                'description' => 'Gemini/OpenAI AI configuration',
                'tables' => ['ai_configs'],
                'controllers' => ['AIConfigController'],
                'views' => 'superadmin/ai-config/',
                'model' => 'App\\Models\\AIConfig',
                'connects_to' => ['webhook', 'product_questions', 'global_questions', 'catalogue'],
                'status' => $this->checkConnection('ai_configs'),
            ],
            'webhook' => [
                'name' => 'WhatsApp Webhook',
                'icon' => '📱',
                'description' => 'Main entry point for WhatsApp messages',
                'tables' => ['chat_messages', 'whatsapp_users'],
                'controllers' => ['WebhookController'],
                'views' => 'N/A (API only)',
                'model' => 'N/A',
                'connects_to' => ['ai_model', 'leads', 'catalogue', 'product_questions', 'flowchart'],
                'status' => 'Active',
            ],
        ];
    }

    /**
     * Get hardcoded items in the system
     */
    private function getHardcodedItems()
    {
        $items = [];

        // Check WebhookController for hardcoded values
        $webhookPath = app_path('Http/Controllers/WebhookController.php');
        if (File::exists($webhookPath)) {
            $content = File::get($webhookPath);

            // Find hardcoded field names
            if (preg_match_all('/\$fieldName\s*===?\s*[\'"](\w+)[\'"]/', $content, $matches)) {
                foreach ($matches[1] as $field) {
                    $items[] = [
                        'type' => 'Field Check',
                        'value' => $field,
                        'file' => 'WebhookController.php',
                        'concern' => 'May break if field name changes',
                        'line' => $this->findLine($content, $matches[0][0]),
                    ];
                }
            }

            // Find hardcoded prompts
            if (preg_match_all('/[\'"]([^"\']*product|category|model|size|finish|quantity)[^\'"]*[\'"]/i', $content, $matches)) {
                foreach (array_unique($matches[0]) as $idx => $match) {
                    if (strlen($match) > 20) {
                        $items[] = [
                            'type' => 'Hardcoded Text',
                            'value' => substr($match, 0, 50) . '...',
                            'file' => 'WebhookController.php',
                            'concern' => 'Prompt text hardcoded',
                            'line' => 'N/A',
                        ];
                    }
                }
            }
        }

        // Check AIService for hardcoded values
        $aiServicePath = app_path('Services/AIService.php');
        if (File::exists($aiServicePath)) {
            $content = File::get($aiServicePath);

            // Find system prompt patterns
            if (strpos($content, 'system_prompt') !== false) {
                $items[] = [
                    'type' => 'Dynamic',
                    'value' => 'System Prompt',
                    'file' => 'AIService.php',
                    'concern' => '✅ Uses admin.system_prompt',
                    'line' => 'N/A',
                ];
            }
        }

        return $items;
    }

    /**
     * Get file connections (which files connect to which)
     */
    private function getFileConnections()
    {
        return [
            [
                'source' => 'routes/api.php',
                'target' => 'WebhookController',
                'description' => 'WhatsApp webhook endpoint',
                'route' => 'POST /api/webhook',
            ],
            [
                'source' => 'WebhookController',
                'target' => 'AIService',
                'description' => 'Generates AI response',
                'method' => 'generateResponse()',
            ],
            [
                'source' => 'AIService',
                'target' => 'ProductQuestion',
                'description' => 'Builds prompt context',
                'method' => 'getWorkflowContext()',
            ],
            [
                'source' => 'AIService',
                'target' => 'GlobalQuestion',
                'description' => 'Adds global question context',
                'method' => 'getGlobalContext()',
            ],
            [
                'source' => 'AIService',
                'target' => 'Catalogue',
                'description' => 'Validates user answers',
                'method' => 'validateWithCatalogue()',
            ],
            [
                'source' => 'WebhookController',
                'target' => 'Lead',
                'description' => 'Creates/updates lead',
                'method' => 'syncWorkflowToLead()',
            ],
            [
                'source' => 'WebhookController',
                'target' => 'LeadProduct',
                'description' => 'Saves product quotation',
                'method' => 'syncWorkflowToLeadProduct()',
            ],
            [
                'source' => 'FlowchartController',
                'target' => 'QuestionnaireNode',
                'description' => 'Saves flowchart nodes',
                'method' => 'saveNode()',
            ],
        ];
    }

    /**
     * Get database table connections
     */
    private function getDatabaseConnections()
    {
        $connections = [];

        try {
            // Get table counts
            $tables = [
                'admins' => 'Admin accounts',
                'catalogues' => 'Product catalog',
                'catalogue_fields' => 'Catalog field definitions',
                'product_questions' => 'Workflow fields',
                'global_questions' => 'One-time questions',
                'questionnaire_nodes' => 'Flowchart nodes',
                'questionnaire_connections' => 'Flowchart edges',
                'leads' => 'Customer leads',
                'lead_products' => 'Product selections',
                'lead_statuses' => 'Lead pipeline stages',
                'followup_templates' => 'Auto followup templates',
                'followups' => 'Scheduled followups',
                'chat_messages' => 'Chat history',
                'whatsapp_users' => 'WhatsApp contacts',
                'ai_configs' => 'AI configuration',
            ];

            foreach ($tables as $table => $description) {
                try {
                    $count = DB::table($table)->count();
                    $connections[] = [
                        'table' => $table,
                        'description' => $description,
                        'count' => $count,
                        'status' => 'Active',
                    ];
                } catch (\Exception $e) {
                    $connections[] = [
                        'table' => $table,
                        'description' => $description,
                        'count' => 0,
                        'status' => 'Missing',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Database connection issue
        }

        return $connections;
    }

    /**
     * Check if a table has data
     */
    private function checkConnection($table)
    {
        try {
            $count = DB::table($table)->count();
            return $count > 0 ? 'Active' : 'Empty';
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Find line number in content
     */
    private function findLine($content, $search)
    {
        $pos = strpos($content, $search);
        if ($pos === false)
            return 'N/A';
        return substr_count(substr($content, 0, $pos), "\n") + 1;
    }

    /**
     * API: Get real-time connection status
     */
    public function status()
    {
        $connections = $this->getSystemConnections();
        $status = [];

        foreach ($connections as $key => $conn) {
            $status[$key] = [
                'name' => $conn['name'],
                'status' => $conn['status'],
            ];
        }

        return response()->json($status);
    }

    /**
     * API: Validate a specific connection
     */
    public function validateConnection(Request $request)
    {
        $component = $request->input('component');
        $connections = $this->getSystemConnections();

        if (!isset($connections[$component])) {
            return response()->json(['error' => 'Unknown component'], 404);
        }

        $conn = $connections[$component];
        $issues = [];

        // Check tables exist
        foreach ($conn['tables'] as $table) {
            try {
                DB::table($table)->limit(1)->get();
            } catch (\Exception $e) {
                $issues[] = "Table '{$table}' not accessible";
            }
        }

        // Check model exists
        if ($conn['model'] !== 'N/A' && !class_exists($conn['model'])) {
            $issues[] = "Model '{$conn['model']}' not found";
        }

        return response()->json([
            'component' => $component,
            'name' => $conn['name'],
            'status' => empty($issues) ? 'OK' : 'Issues Found',
            'issues' => $issues,
        ]);
    }
}
