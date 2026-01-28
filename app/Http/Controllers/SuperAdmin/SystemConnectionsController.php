<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ProductQuestion;
use App\Models\CatalogueField;
use App\Models\QuestionnaireNode;
use App\Models\Catalogue;
use App\Models\Lead;
use App\Models\LeadProduct;
use App\Models\LeadStatus;
use App\Models\GlobalQuestion;
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
        // Get module structure data
        $moduleStructure = $this->getModuleStructure();

        // Get per-admin connection data
        $adminConnections = $this->getAdminConnections();

        // Get hardcoded field connections
        $fieldConnections = $this->getFieldLevelConnections();

        return view('superadmin.connections.index', compact(
            'moduleStructure',
            'adminConnections',
            'fieldConnections'
        ));
    }

    /**
     * Get per-admin bot configuration and connection status
     */
    private function getAdminConnections(): array
    {
        $admins = Admin::all();
        $data = [];

        foreach ($admins as $admin) {
            $productQuestions = ProductQuestion::where('admin_id', $admin->id)->active()->get();
            $flowchartNodes = QuestionnaireNode::where('admin_id', $admin->id)->active()->get();
            $catalogueCount = Catalogue::where('admin_id', $admin->id)->where('is_active', true)->count();
            $leadStatuses = LeadStatus::where('admin_id', $admin->id)->count();
            $globalQuestions = GlobalQuestion::where('admin_id', $admin->id)->active()->count();

            // Calculate connection scores
            $hasFlowchart = $flowchartNodes->count() > 0;
            $hasProducts = $productQuestions->count() > 0;
            $hasCatalogue = $catalogueCount > 0;

            // Check field-level connections
            $fieldConnectionStatus = [];
            foreach ($productQuestions as $pq) {
                // Try to find CatalogueField by product_question_id first
                $catalogueField = CatalogueField::where('product_question_id', $pq->id)->first();

                // Fallback: check by field_key if not linked yet
                if (!$catalogueField) {
                    $fieldKey = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::lower($pq->field_name));
                    $catalogueField = CatalogueField::where('admin_id', $pq->admin_id)
                        ->where('field_key', $fieldKey)
                        ->first();
                }

                $flowchartNode = $flowchartNodes->firstWhere('questionnaire_field_id', $pq->id);

                $fieldConnectionStatus[] = [
                    'field_name' => $pq->field_name,
                    'display_name' => $pq->display_name,
                    'product_question_id' => $pq->id,
                    'catalogue_field' => [
                        'connected' => $catalogueField !== null,
                        'id' => $catalogueField?->id,
                        'field_key' => $catalogueField?->field_key,
                    ],
                    'flowchart_node' => [
                        'connected' => $flowchartNode !== null,
                        'id' => $flowchartNode?->id,
                        'label' => $flowchartNode?->label,
                    ],
                    'is_unique_key' => $pq->is_unique_key,
                    'is_unique_field' => $pq->is_unique_field,
                    'is_qty_field' => $pq->is_qty_field,
                    'options_source' => $pq->options_source,
                ];
            }

            $data[] = [
                'admin_id' => $admin->id,
                'admin_name' => $admin->name ?? $admin->business_name ?? "Admin #{$admin->id}",
                'summary' => [
                    'product_questions' => $productQuestions->count(),
                    'flowchart_nodes' => $flowchartNodes->count(),
                    'catalogue_items' => $catalogueCount,
                    'lead_statuses' => $leadStatuses,
                    'global_questions' => $globalQuestions,
                ],
                'status' => [
                    'flowchart' => $hasFlowchart ? 'connected' : 'disconnected',
                    'product_questions' => $hasProducts ? 'connected' : 'disconnected',
                    'catalogue' => $hasCatalogue ? 'connected' : 'disconnected',
                    'overall' => ($hasFlowchart && $hasProducts && $hasCatalogue) ? 'fully_connected' : 'partial',
                ],
                'field_connections' => $fieldConnectionStatus,
            ];
        }

        return $data;
    }

    /**
     * Get field-level hardcoded connections
     * Shows the data flow path for each field
     */
    private function getFieldLevelConnections(): array
    {
        return [
            [
                'section' => 'Product Question → Catalogue Sync',
                'description' => 'ProductQuestion field_name syncs to CatalogueField',
                'connections' => [
                    [
                        'from' => 'ProductQuestion.field_name',
                        'to' => 'CatalogueField.field_name',
                        'status' => 'auto_sync',
                        'trigger' => 'ProductQuestion::saved() event',
                    ],
                    [
                        'from' => 'ProductQuestion.is_required',
                        'to' => 'CatalogueField.is_required',
                        'status' => 'auto_sync',
                        'trigger' => 'ProductQuestion::saved() event',
                    ],
                ],
            ],
            [
                'section' => 'Flowchart → ProductQuestion Link',
                'description' => 'QuestionnaireNode links to ProductQuestion for bot questions',
                'connections' => [
                    [
                        'from' => 'QuestionnaireNode.questionnaire_field_id',
                        'to' => 'ProductQuestion.id',
                        'status' => 'foreign_key',
                        'trigger' => 'Manual selection in Flowchart Editor',
                    ],
                    [
                        'from' => 'QuestionnaireNode.global_question_id',
                        'to' => 'GlobalQuestion.id',
                        'status' => 'foreign_key',
                        'trigger' => 'Manual selection in Flowchart Editor',
                    ],
                ],
            ],
            [
                'section' => 'Bot → Catalogue Options',
                'description' => 'Bot fetches filtered options from Catalogue based on collected_data',
                'connections' => [
                    [
                        'from' => 'Lead.collected_data',
                        'to' => 'Catalogue.data (JSON query)',
                        'status' => 'dynamic_filter',
                        'trigger' => 'ProductQuestion::getFilteredOptions()',
                    ],
                    [
                        'from' => 'ProductQuestion.catalogue_field',
                        'to' => 'Catalogue.data->{field_name}',
                        'status' => 'field_mapping',
                        'trigger' => 'Options fetch during bot conversation',
                    ],
                ],
            ],
            [
                'section' => 'Lead → LeadProduct',
                'description' => 'When all unique keys are complete, product is saved',
                'connections' => [
                    [
                        'from' => 'Lead.collected_data.product_questions',
                        'to' => 'LeadProduct.data (JSON)',
                        'status' => 'data_copy',
                        'trigger' => 'Lead::addProductFromCollectedData()',
                    ],
                    [
                        'from' => 'ProductQuestion.is_unique_key fields',
                        'to' => 'LeadProduct.unique_key',
                        'status' => 'composite_key',
                        'trigger' => 'LeadProduct::generateUniqueKey()',
                    ],
                ],
            ],
            [
                'section' => 'Flowchart → Lead Status',
                'description' => 'When node is answered, lead status is updated',
                'connections' => [
                    [
                        'from' => 'QuestionnaireNode.lead_status_id',
                        'to' => 'Lead.lead_status_id',
                        'status' => 'status_update',
                        'trigger' => 'Bot processes answer for node with status',
                    ],
                ],
            ],
        ];
    }



    /**
     * Get detailed module structure with field mappings
     */
    private function getModuleStructure()
    {
        return [
            'workflow' => [
                'name' => 'Workflow',
                'icon' => '🔄',
                'submodules' => [
                    'product_questions' => [
                        'name' => 'A. Product Questions',
                        'description' => 'Questions asked during product inquiry (Category, Model, Size, etc.)',
                        'frontend' => 'admin/workflow/fields/index.blade.php',
                        'route' => '/admin/workflow/fields',
                        'controller' => 'ProductQuestionController',
                        'model' => 'ProductQuestion',
                        'database' => 'product_questions',
                        'status' => $this->checkConnection('product_questions'),
                        'fields' => [
                            ['ui' => 'Field Name', 'db_column' => 'field_name', 'db_table' => 'product_questions', 'type' => 'string', 'used_in' => ['leads.collected_data', 'AI Prompt Context']],
                            ['ui' => 'Display Name', 'db_column' => 'display_name', 'db_table' => 'product_questions', 'type' => 'string', 'used_in' => ['UI Labels']],
                            ['ui' => 'Field Type', 'db_column' => 'field_type', 'db_table' => 'product_questions', 'type' => 'enum', 'used_in' => ['Input Validation']],
                            ['ui' => 'Is Required', 'db_column' => 'is_required', 'db_table' => 'product_questions', 'type' => 'boolean', 'used_in' => ['Workflow Logic']],
                            ['ui' => 'Is Unique Key', 'db_column' => 'is_unique_key', 'db_table' => 'product_questions', 'type' => 'boolean', 'used_in' => ['Product Matching']],
                            ['ui' => 'Options Source', 'db_column' => 'options_source', 'db_table' => 'product_questions', 'type' => 'enum', 'used_in' => ['Catalogue Connection']],
                            ['ui' => 'Sort Order', 'db_column' => 'sort_order', 'db_table' => 'product_questions', 'type' => 'integer', 'used_in' => ['Question Order']],
                        ],
                        'connections' => [
                            ['target' => 'Flowchart', 'via' => 'questionnaire_field_id', 'logic' => 'Links node to question field'],
                            ['target' => 'Catalogue', 'via' => 'options_source=catalogue', 'logic' => 'Gets dropdown options from catalogue'],
                            ['target' => 'Leads', 'via' => 'field_name → collected_data keys', 'logic' => 'Stores user answers'],
                        ],
                    ],
                    'global_questions' => [
                        'name' => 'B. Global Questions',
                        'description' => 'One-time questions asked once per customer (City, Purpose)',
                        'frontend' => 'admin/workflow/global-questions/index.blade.php',
                        'route' => '/admin/workflow/global-questions',
                        'controller' => 'GlobalQuestionController',
                        'model' => 'GlobalQuestion',
                        'database' => 'global_questions',
                        'status' => $this->checkConnection('global_questions'),
                        'fields' => [
                            ['ui' => 'Field Name', 'db_column' => 'field_name', 'db_table' => 'global_questions', 'type' => 'string', 'used_in' => ['Customer Profile']],
                            ['ui' => 'Question Text', 'db_column' => 'question_text', 'db_table' => 'global_questions', 'type' => 'text', 'used_in' => ['WhatsApp Message']],
                            ['ui' => 'Trigger Position', 'db_column' => 'trigger_position', 'db_table' => 'global_questions', 'type' => 'enum', 'used_in' => ['When to ask']],
                            ['ui' => 'Is Active', 'db_column' => 'is_active', 'db_table' => 'global_questions', 'type' => 'boolean', 'used_in' => ['Show/Hide']],
                        ],
                        'connections' => [
                            ['target' => 'Customer', 'via' => 'global_fields JSON', 'logic' => 'Stores one-time answers'],
                            ['target' => 'AI Prompt', 'via' => 'Context building', 'logic' => 'Provides customer context'],
                        ],
                    ],
                    'flowchart' => [
                        'name' => 'C. Flowchart',
                        'description' => 'Visual conversation flow builder with nodes and connections',
                        'frontend' => 'admin/workflow/flowchart/index.blade.php',
                        'route' => '/admin/workflow/flowchart',
                        'controller' => 'FlowchartController',
                        'model' => 'QuestionnaireNode',
                        'database' => 'questionnaire_nodes, questionnaire_connections',
                        'status' => $this->checkConnection('questionnaire_nodes'),
                        'fields' => [
                            ['ui' => 'Node Label', 'db_column' => 'label', 'db_table' => 'questionnaire_nodes', 'type' => 'string', 'used_in' => ['UI Display']],
                            ['ui' => 'Node Type', 'db_column' => 'node_type', 'db_table' => 'questionnaire_nodes', 'type' => 'enum', 'used_in' => ['start/question/end']],
                            ['ui' => 'Position X', 'db_column' => 'pos_x', 'db_table' => 'questionnaire_nodes', 'type' => 'float', 'used_in' => ['Canvas Position']],
                            ['ui' => 'Position Y', 'db_column' => 'pos_y', 'db_table' => 'questionnaire_nodes', 'type' => 'float', 'used_in' => ['Canvas Position']],
                            ['ui' => 'Connected Question', 'db_column' => 'questionnaire_field_id', 'db_table' => 'questionnaire_nodes', 'type' => 'FK', 'used_in' => ['Link to ProductQuestion']],
                            ['ui' => 'Connection', 'db_column' => 'source_node_id → target_node_id', 'db_table' => 'questionnaire_connections', 'type' => 'FK', 'used_in' => ['Flow Path']],
                        ],
                        'connections' => [
                            ['target' => 'Product Questions', 'via' => 'questionnaire_field_id', 'logic' => 'Each question node links to a field'],
                            ['target' => 'QuestionnaireService', 'via' => 'getNextQuestionFromFlowchart()', 'logic' => 'Determines question order'],
                        ],
                    ],
                ],
            ],
            'catalogue' => [
                'name' => 'Catalogue',
                'icon' => '📦',
                'submodules' => [
                    'catalogue' => [
                        'name' => 'Product Catalogue',
                        'description' => 'Product database with dynamic fields for validation',
                        'frontend' => 'admin/catalogue/index.blade.php',
                        'route' => '/admin/catalogue',
                        'controller' => 'CatalogueController',
                        'model' => 'Catalogue',
                        'database' => 'catalogues, catalogue_fields',
                        'status' => $this->checkConnection('catalogues'),
                        'fields' => [
                            ['ui' => 'Product Data', 'db_column' => 'data (JSON)', 'db_table' => 'catalogues', 'type' => 'json', 'used_in' => ['Dynamic Fields Storage']],
                            ['ui' => 'Is Active', 'db_column' => 'is_active', 'db_table' => 'catalogues', 'type' => 'boolean', 'used_in' => ['Show in Search']],
                            ['ui' => 'Field Name', 'db_column' => 'field_name', 'db_table' => 'catalogue_fields', 'type' => 'string', 'used_in' => ['Column Definition']],
                            ['ui' => 'Field Type', 'db_column' => 'field_type', 'db_table' => 'catalogue_fields', 'type' => 'enum', 'used_in' => ['Input Type']],
                        ],
                        'connections' => [
                            ['target' => 'Product Questions', 'via' => 'options_source=catalogue', 'logic' => 'Provides dropdown options'],
                            ['target' => 'AI Prompt', 'via' => 'Catalogue search', 'logic' => 'Validates user answers against products'],
                            ['target' => 'Lead Products', 'via' => 'Product matching', 'logic' => 'Links to quotation items'],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'name' => 'Settings',
                'icon' => '⚙️',
                'submodules' => [
                    'whatsapp' => [
                        'name' => 'A. WhatsApp Connection',
                        'description' => 'WhatsApp Business API connection settings',
                        'frontend' => 'admin/settings/index.blade.php (WhatsApp Section)',
                        'route' => '/admin/settings',
                        'controller' => 'SettingController',
                        'model' => 'Admin',
                        'database' => 'admins',
                        'status' => $this->checkConnection('admins'),
                        'fields' => [
                            ['ui' => 'Instance ID', 'db_column' => 'whatsapp_instance_id', 'db_table' => 'admins', 'type' => 'string', 'used_in' => ['API Calls']],
                            ['ui' => 'Access Token', 'db_column' => 'whatsapp_token', 'db_table' => 'admins', 'type' => 'encrypted', 'used_in' => ['API Auth']],
                            ['ui' => 'Phone Number', 'db_column' => 'whatsapp_phone', 'db_table' => 'admins', 'type' => 'string', 'used_in' => ['Sender ID']],
                        ],
                        'connections' => [
                            ['target' => 'Webhook', 'via' => 'Instance ID', 'logic' => 'Receives messages'],
                            ['target' => 'Chat Messages', 'via' => 'API', 'logic' => 'Sends responses'],
                        ],
                    ],
                    'ai_config' => [
                        'name' => 'B. AI Configuration',
                        'description' => 'AI model settings and system prompt',
                        'frontend' => 'admin/settings/index.blade.php (AI Section)',
                        'route' => '/admin/settings',
                        'controller' => 'SettingController',
                        'model' => 'Admin',
                        'database' => 'admins, settings',
                        'status' => $this->checkConnection('settings'),
                        'fields' => [
                            ['ui' => 'System Prompt', 'db_column' => 'ai_system_prompt', 'db_table' => 'admins', 'type' => 'text', 'used_in' => ['AI Personality']],
                            ['ui' => 'AI Provider', 'db_column' => "value (key='global_ai_provider')", 'db_table' => 'settings', 'type' => 'string', 'used_in' => ['google/openai']],
                            ['ui' => 'AI Model', 'db_column' => "value (key='global_ai_model')", 'db_table' => 'settings', 'type' => 'string', 'used_in' => ['gemini-2.0-flash']],
                        ],
                        'connections' => [
                            ['target' => 'AIService', 'via' => 'generateResponse()', 'logic' => 'Used as system prompt'],
                        ],
                    ],
                    'business_info' => [
                        'name' => 'C. Business Information',
                        'description' => 'Business name, address, contact details',
                        'frontend' => 'admin/settings/index.blade.php (Business Section)',
                        'route' => '/admin/settings',
                        'controller' => 'SettingController',
                        'model' => 'Admin',
                        'database' => 'admins',
                        'status' => $this->checkConnection('admins'),
                        'fields' => [
                            ['ui' => 'Business Name', 'db_column' => 'business_name', 'db_table' => 'admins', 'type' => 'string', 'used_in' => ['Greeting Message']],
                            ['ui' => 'Business Phone', 'db_column' => 'business_phone', 'db_table' => 'admins', 'type' => 'string', 'used_in' => ['Contact Info']],
                        ],
                        'connections' => [
                            ['target' => 'AI Prompt', 'via' => 'Context', 'logic' => 'Business name in responses'],
                        ],
                    ],
                    'bot_control' => [
                        'name' => 'D. Bot Control',
                        'description' => 'Enable/disable bot, auto-reply settings',
                        'frontend' => 'admin/settings/index.blade.php (Bot Section)',
                        'route' => '/admin/settings',
                        'controller' => 'SettingController',
                        'model' => 'Admin',
                        'database' => 'admins',
                        'status' => $this->checkConnection('admins'),
                        'fields' => [
                            ['ui' => 'Bot Enabled', 'db_column' => 'bot_enabled', 'db_table' => 'admins', 'type' => 'boolean', 'used_in' => ['Global On/Off']],
                            ['ui' => 'Auto Reply', 'db_column' => 'auto_reply_enabled', 'db_table' => 'admins', 'type' => 'boolean', 'used_in' => ['Auto Response']],
                            ['ui' => 'Working Hours', 'db_column' => 'working_hours', 'db_table' => 'admins', 'type' => 'json', 'used_in' => ['Time-based replies']],
                        ],
                        'connections' => [
                            ['target' => 'Webhook', 'via' => 'bot_enabled check', 'logic' => 'Controls if bot responds'],
                        ],
                    ],
                    'lead_settings' => [
                        'name' => 'E. Lead Settings',
                        'description' => 'Lead status pipeline configuration',
                        'frontend' => 'admin/lead-status/index.blade.php',
                        'route' => '/admin/lead-status',
                        'controller' => 'LeadStatusController',
                        'model' => 'LeadStatus',
                        'database' => 'lead_statuses',
                        'status' => $this->checkConnection('lead_statuses'),
                        'fields' => [
                            ['ui' => 'Status Name', 'db_column' => 'name', 'db_table' => 'lead_statuses', 'type' => 'string', 'used_in' => ['Pipeline Stage']],
                            ['ui' => 'Color', 'db_column' => 'color', 'db_table' => 'lead_statuses', 'type' => 'string', 'used_in' => ['UI Display']],
                            ['ui' => 'Sort Order', 'db_column' => 'sort_order', 'db_table' => 'lead_statuses', 'type' => 'integer', 'used_in' => ['Pipeline Order']],
                            ['ui' => 'Is Default', 'db_column' => 'is_default', 'db_table' => 'lead_statuses', 'type' => 'boolean', 'used_in' => ['New Lead Status']],
                        ],
                        'connections' => [
                            ['target' => 'Leads', 'via' => 'lead_status_id', 'logic' => 'Assigns status to lead'],
                            ['target' => 'Flowchart', 'via' => 'lead_status_id in node config', 'logic' => 'Auto-assign status'],
                        ],
                    ],
                    'followup_settings' => [
                        'name' => 'F. Followup Settings',
                        'description' => 'Auto followup templates and triggers',
                        'frontend' => 'admin/followup-templates/index.blade.php',
                        'route' => '/admin/followup-templates',
                        'controller' => 'FollowupTemplateController',
                        'model' => 'FollowupTemplate',
                        'database' => 'followup_templates',
                        'status' => $this->checkConnection('followup_templates'),
                        'fields' => [
                            ['ui' => 'Template Name', 'db_column' => 'name', 'db_table' => 'followup_templates', 'type' => 'string', 'used_in' => ['Template ID']],
                            ['ui' => 'Message', 'db_column' => 'message', 'db_table' => 'followup_templates', 'type' => 'text', 'used_in' => ['WhatsApp Message']],
                            ['ui' => 'Delay Hours', 'db_column' => 'delay_hours', 'db_table' => 'followup_templates', 'type' => 'integer', 'used_in' => ['When to send']],
                            ['ui' => 'Trigger On', 'db_column' => 'trigger_on', 'db_table' => 'followup_templates', 'type' => 'enum', 'used_in' => ['lead_created/status_changed']],
                        ],
                        'connections' => [
                            ['target' => 'Leads', 'via' => 'trigger_on event', 'logic' => 'Creates followup on trigger'],
                            ['target' => 'Followups', 'via' => 'template_id', 'logic' => 'Scheduled message'],
                        ],
                    ],
                ],
            ],
            'leads' => [
                'name' => 'Leads',
                'icon' => '👤',
                'submodules' => [
                    'leads' => [
                        'name' => 'Lead Management',
                        'description' => 'Customer leads from WhatsApp conversations',
                        'frontend' => 'admin/leads/index.blade.php',
                        'route' => '/admin/leads',
                        'controller' => 'LeadController',
                        'model' => 'Lead',
                        'database' => 'leads, lead_products',
                        'status' => $this->checkConnection('leads'),
                        'fields' => [
                            ['ui' => 'Phone', 'db_column' => 'phone', 'db_table' => 'leads', 'type' => 'string', 'used_in' => ['Contact']],
                            ['ui' => 'Name', 'db_column' => 'name', 'db_table' => 'leads', 'type' => 'string', 'used_in' => ['Display']],
                            ['ui' => 'Collected Data', 'db_column' => 'collected_data (JSON)', 'db_table' => 'leads', 'type' => 'json', 'used_in' => ['Product Question Answers']],
                            ['ui' => 'Status', 'db_column' => 'lead_status_id', 'db_table' => 'leads', 'type' => 'FK', 'used_in' => ['Pipeline Stage']],
                            ['ui' => 'Product Items', 'db_column' => 'data (JSON)', 'db_table' => 'lead_products', 'type' => 'json', 'used_in' => ['Quotation Items']],
                        ],
                        'connections' => [
                            ['target' => 'Product Questions', 'via' => 'collected_data keys', 'logic' => 'Stores field answers'],
                            ['target' => 'Lead Status', 'via' => 'lead_status_id', 'logic' => 'Pipeline stage'],
                            ['target' => 'Followups', 'via' => 'lead_id', 'logic' => 'Scheduled messages'],
                            ['target' => 'Customer', 'via' => 'customer_id', 'logic' => 'Links to WhatsApp user'],
                        ],
                    ],
                ],
            ],
            'followups' => [
                'name' => 'Followups',
                'icon' => '📅',
                'submodules' => [
                    'followups' => [
                        'name' => 'Scheduled Followups',
                        'description' => 'Pending and completed followup messages',
                        'frontend' => 'admin/followups/index.blade.php',
                        'route' => '/admin/followups',
                        'controller' => 'FollowupController',
                        'model' => 'Followup',
                        'database' => 'followups',
                        'status' => $this->checkConnection('followups'),
                        'fields' => [
                            ['ui' => 'Lead', 'db_column' => 'lead_id', 'db_table' => 'followups', 'type' => 'FK', 'used_in' => ['Target Customer']],
                            ['ui' => 'Template', 'db_column' => 'template_id', 'db_table' => 'followups', 'type' => 'FK', 'used_in' => ['Message Content']],
                            ['ui' => 'Scheduled At', 'db_column' => 'scheduled_at', 'db_table' => 'followups', 'type' => 'datetime', 'used_in' => ['When to Send']],
                            ['ui' => 'Status', 'db_column' => 'status', 'db_table' => 'followups', 'type' => 'enum', 'used_in' => ['pending/completed/cancelled']],
                            ['ui' => 'Sent At', 'db_column' => 'sent_at', 'db_table' => 'followups', 'type' => 'datetime', 'used_in' => ['Actual Send Time']],
                        ],
                        'connections' => [
                            ['target' => 'Leads', 'via' => 'lead_id', 'logic' => 'Customer to message'],
                            ['target' => 'Templates', 'via' => 'template_id', 'logic' => 'Message content'],
                            ['target' => 'WhatsApp', 'via' => 'API', 'logic' => 'Sends message'],
                        ],
                    ],
                ],
            ],
        ];
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
        $webhookPath = app_path('Http/Controllers/Api/WebhookController.php');
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
                $allSettings = DB::table('settings')->get();

                if ($provider) {
                    $providerValue = $provider->value;
                    if ($providerValue === 'google') {
                        // Check for: 1) GEMINI_API_KEY, 2) GOOGLE_APPLICATION_CREDENTIALS, 3) Vertex AI settings
                        $apiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
                        $serviceAccountEnv = env('GOOGLE_APPLICATION_CREDENTIALS');
                        $vertexEmail = $allSettings->where('key', 'vertex_service_email')->first();
                        $vertexKey = $allSettings->where('key', 'vertex_private_key')->first();
                        $hasVertexSettings = $vertexEmail && !empty($vertexEmail->value) && $vertexKey && !empty($vertexKey->value);

                        if (empty($apiKey) && empty($serviceAccountEnv) && !$hasVertexSettings) {
                            $result['status'] = 'Warning';
                            $result['issues'][] = "⚠️ No Google auth found - configure Vertex AI in AI Config or set GEMINI_API_KEY";
                        }
                        // All good - one of the auth methods is configured
                    } elseif ($providerValue === 'openai') {
                        $openaiKey = $allSettings->where('key', 'openai_api_key')->first();
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
