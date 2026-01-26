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
                'status' => $this->checkConnectionWithDetails('catalogues')['status'],
                'issues' => $this->checkConnectionWithDetails('catalogues')['issues'],
                'count' => $this->checkConnectionWithDetails('catalogues')['count'],
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
                'status' => $this->checkConnectionWithDetails('product_questions')['status'],
                'issues' => $this->checkConnectionWithDetails('product_questions')['issues'],
                'count' => $this->checkConnectionWithDetails('product_questions')['count'],
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
                'status' => $this->checkConnectionWithDetails('global_questions')['status'],
                'issues' => $this->checkConnectionWithDetails('global_questions')['issues'],
                'count' => $this->checkConnectionWithDetails('global_questions')['count'],
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
                'status' => $this->checkConnectionWithDetails('questionnaire_nodes')['status'],
                'issues' => $this->checkConnectionWithDetails('questionnaire_nodes')['issues'],
                'count' => $this->checkConnectionWithDetails('questionnaire_nodes')['count'],
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
                'status' => $this->checkConnectionWithDetails('leads')['status'],
                'issues' => $this->checkConnectionWithDetails('leads')['issues'],
                'count' => $this->checkConnectionWithDetails('leads')['count'],
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
                'status' => $this->checkConnectionWithDetails('followup_templates')['status'],
                'issues' => $this->checkConnectionWithDetails('followup_templates')['issues'],
                'count' => $this->checkConnectionWithDetails('followup_templates')['count'],
            ],
            'ai_model' => [
                'name' => 'AI Model (Bot)',
                'icon' => '🤖',
                'description' => 'Gemini/OpenAI AI configuration (stored in settings table)',
                'tables' => ['settings'],
                'controllers' => ['AIConfigController'],
                'views' => 'superadmin/ai-config/',
                'model' => 'App\\Models\\Setting',
                'connects_to' => ['webhook', 'product_questions', 'global_questions', 'catalogue'],
                'status' => $this->checkAIConfigStatus()['status'],
                'issues' => $this->checkAIConfigStatus()['issues'],
                'count' => $this->checkAIConfigStatus()['count'],
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
                'issues' => [],
                'count' => $this->getTableCount('chat_messages'),
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
                'settings' => 'System settings (incl. AI config)',
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
     * Check if a table has data - simple version
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
     * Check connection with detailed issues
     */
    private function checkConnectionWithDetails($table)
    {
        $result = [
            'status' => 'Active',
            'issues' => [],
            'count' => 0,
            'error_message' => null,
        ];

        try {
            // Check if table exists and is accessible
            $count = DB::table($table)->count();
            $result['count'] = $count;

            if ($count === 0) {
                $result['status'] = 'Empty';
                $result['issues'][] = "Table '{$table}' is empty - no data found";
            }
        } catch (\Exception $e) {
            $result['status'] = 'Error';
            $result['error_message'] = $e->getMessage();

            // Show the ACTUAL error message - no guessing
            $result['issues'][] = "❌ Error: " . $e->getMessage();
            $result['issues'][] = "📁 " . basename($e->getFile()) . " (Line: " . $e->getLine() . ")";
        }

        return $result;
    }

    /**
     * Get table count safely
     */
    private function getTableCount($table)
    {
        try {
            return DB::table($table)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Check AI Config status specifically (stored in settings table)
     */
    private function checkAIConfigStatus()
    {
        $result = [
            'status' => 'Active',
            'issues' => [],
            'count' => 0,
        ];

        try {
            // AI config is stored in settings table with group='ai'
            $aiSettings = DB::table('settings')->where('group', 'ai')->get();
            $result['count'] = $aiSettings->count();

            if ($result['count'] === 0) {
                $result['status'] = 'Empty';
                $result['issues'][] = "⚠️ No AI settings found in settings table (group='ai')";
                $result['issues'][] = "💡 Go to SuperAdmin > AI Config to configure";
            } else {
                // Check for required settings
                $requiredKeys = ['global_ai_provider', 'global_ai_model'];
                $existingKeys = $aiSettings->pluck('key')->toArray();

                foreach ($requiredKeys as $key) {
                    if (!in_array($key, $existingKeys)) {
                        $result['issues'][] = "⚠️ Missing setting: {$key}";
                    }
                }

                // Check if API key is configured
                $provider = $aiSettings->where('key', 'global_ai_provider')->first();
                if ($provider) {
                    $providerValue = $provider->value;
                    if ($providerValue === 'google') {
                        // Check for Google API key OR Vertex AI service account
                        $apiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
                        $serviceAccount = env('GOOGLE_APPLICATION_CREDENTIALS');

                        if (empty($apiKey) && empty($serviceAccount)) {
                            $result['status'] = 'Warning';
                            $result['issues'][] = "⚠️ No Google auth found - set GEMINI_API_KEY or GOOGLE_APPLICATION_CREDENTIALS";
                        }
                        // All good - either API key or Service Account is configured
                    } elseif ($providerValue === 'openai') {
                        $openaiKey = $aiSettings->where('key', 'openai_api_key')->first();
                        if (!$openaiKey || empty($openaiKey->value)) {
                            $result['status'] = 'Warning';
                            $result['issues'][] = "⚠️ OpenAI API key not configured";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $result['status'] = 'Error';
            $result['issues'][] = "❌ Error: " . $e->getMessage();
        }

        return $result;
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
