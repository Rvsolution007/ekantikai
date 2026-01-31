<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;

class ProjectStructureController extends Controller
{
    // SuperAdmin sidebar items
    protected $superadminItems = [
        [
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'name' => 'Admins',
            'slug' => 'admins',
            'icon' => 'building',
        ],
        [
            'name' => 'Payments',
            'slug' => 'payments',
            'icon' => 'payment',
        ],
        [
            'name' => 'Credits',
            'slug' => 'credits',
            'icon' => 'credits',
        ],
        [
            'name' => 'AI Config',
            'slug' => 'ai-config',
            'icon' => 'ai',
        ],
        [
            'name' => 'Settings',
            'slug' => 'settings',
            'icon' => 'settings',
        ],
        [
            'name' => 'Connections',
            'slug' => 'connections',
            'icon' => 'connections',
        ],
        [
            'name' => 'Debug',
            'slug' => 'debug',
            'icon' => 'debug',
        ],
    ];

    // Admin panel sidebar items (what shows when admin logs in)
    protected $adminItems = [
        [
            'name' => 'Dashboard',
            'slug' => 'dashboard',
            'icon' => 'dashboard',
        ],
        [
            'name' => 'Leads',
            'slug' => 'leads',
            'icon' => 'leads',
        ],
        [
            'name' => 'Lead Statuses',
            'slug' => 'lead-status',
            'icon' => 'lead-status',
            'parent' => 'leads',
        ],
        [
            'name' => 'Clients',
            'slug' => 'clients',
            'icon' => 'clients',
        ],
        [
            'name' => 'Users',
            'slug' => 'users',
            'icon' => 'users',
        ],
        [
            'name' => 'Chats',
            'slug' => 'chats',
            'icon' => 'chats',
        ],
        [
            'name' => 'Catalogue',
            'slug' => 'catalogue',
            'icon' => 'catalogue',
        ],
        [
            'name' => 'Followups',
            'slug' => 'followups',
            'icon' => 'followups',
        ],
        [
            'name' => 'Templates',
            'slug' => 'followup-templates',
            'icon' => 'templates',
            'parent' => 'followups',
        ],
        [
            'name' => 'Credits',
            'slug' => 'credits',
            'icon' => 'credits',
        ],
        [
            'name' => 'Workflow',
            'slug' => 'workflow',
            'icon' => 'workflow',
            'hasSubItems' => true,
        ],
        [
            'name' => 'Settings',
            'slug' => 'settings',
            'icon' => 'settings',
        ],
    ];

    // Workflow sub-items
    protected $workflowSubItems = [
        [
            'name' => 'Product Questions',
            'slug' => 'product-questions',
            'icon' => 'questions',
        ],
        [
            'name' => 'Flowchart',
            'slug' => 'flowchart',
            'icon' => 'flowchart',
        ],
        [
            'name' => 'Global Questions',
            'slug' => 'global-questions',
            'icon' => 'global',
        ],
        [
            'name' => 'Templates',
            'slug' => 'templates',
            'icon' => 'templates',
        ],
    ];

    public function index()
    {
        return view('superadmin.project-structure.index', [
            'sidebarItems' => $this->superadminItems
        ]);
    }

    public function show($module)
    {
        // Find the module
        $currentModule = collect($this->superadminItems)->firstWhere('slug', $module);

        if (!$currentModule) {
            abort(404);
        }

        // If it's "admins", show admin sidebar items
        $subItems = [];
        if ($module === 'admins') {
            $subItems = $this->adminItems;
        }

        return view('superadmin.project-structure.show', [
            'currentModule' => $currentModule,
            'subItems' => $subItems,
        ]);
    }

    public function showSub($module, $submodule)
    {
        // Find parent module
        $currentModule = collect($this->superadminItems)->firstWhere('slug', $module);

        if (!$currentModule) {
            abort(404);
        }

        // Find sub module
        $subItem = null;
        if ($module === 'admins') {
            $subItem = collect($this->adminItems)->firstWhere('slug', $submodule);
        }

        if (!$subItem) {
            abort(404);
        }

        // If workflow, show workflow sub-items
        if ($submodule === 'workflow') {
            return view('superadmin.project-structure.workflow', [
                'currentModule' => $currentModule,
                'subItem' => $subItem,
                'workflowItems' => $this->workflowSubItems,
            ]);
        }

        // For now, just show empty page as user requested
        return view('superadmin.project-structure.sub', [
            'currentModule' => $currentModule,
            'subItem' => $subItem,
        ]);
    }

    /**
     * Show Product Questions with hardcoded demo data and references
     */
    public function showProductQuestions()
    {
        // Hardcoded demo fields - ye Live project jaise fields hain
        $demoFields = [
            [
                'name' => 'Product Category',
                'field_name' => 'category',
                'type' => 'Text',
                'is_unique_key' => true,
                'unique_key_order' => 1,
                'is_qty_field' => false,
                'is_unique_field' => false,
            ],
            [
                'name' => 'Model Number',
                'field_name' => 'model',
                'type' => 'Text',
                'is_unique_key' => true,
                'unique_key_order' => 2,
                'is_qty_field' => false,
                'is_unique_field' => true,
            ],
            [
                'name' => 'Size',
                'field_name' => 'size',
                'type' => 'Text',
                'is_unique_key' => true,
                'unique_key_order' => 3,
                'is_qty_field' => false,
                'is_unique_field' => false,
            ],
            [
                'name' => 'Finish/Color',
                'field_name' => 'finish',
                'type' => 'Text',
                'is_unique_key' => true,
                'unique_key_order' => 4,
                'is_qty_field' => false,
                'is_unique_field' => false,
            ],
            [
                'name' => 'Packaging',
                'field_name' => 'packaging',
                'type' => 'Text',
                'is_unique_key' => false,
                'unique_key_order' => null,
                'is_qty_field' => false,
                'is_unique_field' => false,
            ],
            [
                'name' => 'Qty',
                'field_name' => 'qty',
                'type' => 'Number',
                'is_unique_key' => false,
                'unique_key_order' => null,
                'is_qty_field' => true,
                'is_unique_field' => false,
            ],
            [
                'name' => 'City',
                'field_name' => 'city',
                'type' => 'Text',
                'is_unique_key' => false,
                'unique_key_order' => null,
                'is_qty_field' => false,
                'is_unique_field' => false,
            ],
        ];

        // Hardcoded references - jaha ye settings use ho rahi hain
        $references = [
            'is_unique_key' => [
                [
                    'service' => 'UniqueKeyService',
                    'file' => 'app/Services/UniqueKeyService.php',
                    'ui_reference' => 'Cart / Order Summary',
                    'logic' => 'Product ko unique identify karta hai. Jaise: category|model|size|finish = "Profile handles|9007|8 inch|gold"',
                ],
                [
                    'service' => 'AIService',
                    'file' => 'app/Services/AIService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'AI ko bolta hai ki user se OPTIONS puche (ASK OPTIONS). User manually type nahi karega, options se select karega.',
                ],
                [
                    'service' => 'ProductConfirmationService',
                    'file' => 'app/Services/ProductConfirmationService.php',
                    'ui_reference' => 'Order Confirmation',
                    'logic' => 'Cart mein duplicate product check karta hai. Same unique key = same product.',
                ],
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Flowchart Questions',
                    'logic' => 'Product filter karne ke liye use hota hai. Pehle category select, phir model filter, etc.',
                ],
                [
                    'service' => 'DebugService',
                    'file' => 'app/Services/DebugService.php',
                    'ui_reference' => 'SuperAdmin Debug Page',
                    'logic' => 'Admin ka unique key setup status dikhata hai.',
                ],
            ],
            'is_qty_field' => [
                [
                    'service' => 'AIService',
                    'file' => 'app/Services/AIService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'AI ko bolta hai ki user se INPUT maange (ASK INPUT). "Kitni quantity chahiye?" - user manually type karega.',
                ],
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Flowchart Questions',
                    'logic' => 'Field ko qty type ka mark karta hai. Flowchart mein special handling.',
                ],
                [
                    'service' => 'DebugService',
                    'file' => 'app/Services/DebugService.php',
                    'ui_reference' => 'SuperAdmin Debug Page',
                    'logic' => 'Admin ke qty fields ki count dikhata hai.',
                ],
            ],
            'is_unique_field' => [
                [
                    'service' => 'ProductConfirmationService',
                    'file' => 'app/Services/ProductConfirmationService.php',
                    'ui_reference' => 'Order / Cart',
                    'logic' => 'Model number se exact product identify karta hai. Jab user sirf model bole to direct product mil jaye.',
                ],
                [
                    'service' => 'AIService',
                    'file' => 'app/Services/AIService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'AI ko bolta hai ki yeh UNIQUE IDENTIFIER hai. Model number unique hota hai har product ke liye.',
                ],
                [
                    'service' => 'LeadProduct Model',
                    'file' => 'app/Models/LeadProduct.php',
                    'ui_reference' => 'Lead Details Page',
                    'logic' => 'Product identify karne ke liye use hota hai jab lead mein products display ho.',
                ],
                [
                    'service' => 'FlowchartController',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Workflow Flowchart',
                    'logic' => 'Flowchart save karte waqt unique_field settings backup karta hai.',
                ],
            ],
        ];

        return view('superadmin.project-structure.product-questions', [
            'demoFields' => $demoFields,
            'references' => $references,
        ]);
    }

    /**
     * Show Global Questions with hardcoded demo data and references
     */
    public function showGlobalQuestions()
    {
        // Hardcoded demo Global Questions - ye Live project jaise fields hain
        $demoFields = [
            [
                'name' => 'Customer Name',
                'field_name' => 'customer_name',
                'question_type' => 'text',
                'trigger_position' => 'first',
                'trigger_after_field' => null,
                'add_question' => 'Aapka naam kya hai?',
            ],
            [
                'name' => 'City',
                'field_name' => 'city',
                'question_type' => 'select',
                'trigger_position' => 'after_field',
                'trigger_after_field' => 'customer_name',
                'add_question' => 'Aap konse city se ho?',
            ],
            [
                'name' => 'Business Type',
                'field_name' => 'business_type',
                'question_type' => 'select',
                'trigger_position' => 'after_field',
                'trigger_after_field' => 'city',
                'add_question' => 'Aap dealer ho ya contractor?',
            ],
        ];

        // Hardcoded references - jaha ye settings use ho rahi hain
        $references = [
            'trigger_position' => [
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'Decide karta hai ki Global Question kab puche - "first" = sabse pehle, "after_field" = kisi field ke baad',
                ],
                [
                    'service' => 'BotFlowTesterService',
                    'file' => 'app/Services/BotFlowTesterService.php',
                    'ui_reference' => 'SuperAdmin Bot Tester',
                    'logic' => 'Bot flow validate karta hai ki Global Questions sahi position pe hain ya nahi.',
                ],
            ],
            'trigger_after_field' => [
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'Jab trigger_position = "after_field" ho, to yeh field bolta hai ki konse ProductQuestion ke baad Global Question puche.',
                ],
                [
                    'service' => 'GlobalQuestion Model',
                    'file' => 'app/Models/GlobalQuestion.php',
                    'ui_reference' => 'Admin Global Questions Page',
                    'logic' => 'shouldAskAfter() method check karta hai ki yeh question specific field ke baad puchna hai ya nahi.',
                ],
            ],
            'add_question' => [
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'Yeh actual question text hai jo bot user se puchega. Custom template support karta hai.',
                ],
                [
                    'service' => 'DebugService',
                    'file' => 'app/Services/DebugService.php',
                    'ui_reference' => 'SuperAdmin Debug Page',
                    'logic' => 'Admin ke Global Questions count aur setup status dikhata hai.',
                ],
            ],
            'question_type' => [
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => '"text" = user manually type karega, "select" = options mein se choose karega.',
                ],
                [
                    'service' => 'BotFlowTesterService',
                    'file' => 'app/Services/BotFlowTesterService.php',
                    'ui_reference' => 'SuperAdmin Bot Tester',
                    'logic' => 'Question type ke hisaab se response format validate karta hai.',
                ],
            ],
        ];

        return view('superadmin.project-structure.global-questions', [
            'demoFields' => $demoFields,
            'references' => $references,
        ]);
    }

    /**
     * Show Flowchart with hardcoded demo data and references
     */
    public function showFlowchart()
    {
        // Demo Node Types - ye Live project ke flowchart mein use hote hain
        $demoNodes = [
            [
                'type' => 'start',
                'icon' => '▶️',
                'name' => 'Start Node',
                'description' => 'Flow ka entry point. Har flowchart ek Start node se shuru hota hai.',
                'example' => 'Conversation start hone pe yahan se flow begin hota hai.',
            ],
            [
                'type' => 'question',
                'icon' => '❓',
                'name' => 'Question Node',
                'description' => 'User se question puchne ke liye. Product Questions ya Global Questions link hote hain.',
                'example' => 'Category kya chahiye? Model konsa? Size kitna?',
            ],
            [
                'type' => 'condition',
                'icon' => '⚡',
                'name' => 'Condition Node',
                'description' => 'Branching logic ke liye. User ke response ke basis pe different paths.',
                'example' => 'Agar user ne "Profile Handle" select kiya to ek path, "Knob Handle" ke liye dusra path.',
            ],
            [
                'type' => 'action',
                'icon' => '⚙️',
                'name' => 'Action Node',
                'description' => 'Koi action execute karne ke liye. Lead status change, message send, etc.',
                'example' => 'Lead status ko "Hot Lead" mein change karo.',
            ],
            [
                'type' => 'end',
                'icon' => '🏁',
                'name' => 'End Node',
                'description' => 'Flow complete hone ka indication. Multiple end nodes ho sakte hain.',
                'example' => 'Order complete, conversation end.',
            ],
        ];

        // Demo Connections - nodes kaise connect hote hain
        $demoConnections = [
            [
                'source' => 'Start',
                'target' => 'Category Question',
                'label' => 'default',
                'description' => 'Flow begin hota hai',
            ],
            [
                'source' => 'Category Question',
                'target' => 'Model Question',
                'label' => 'answered',
                'description' => 'Category select hone ke baad Model pucho',
            ],
            [
                'source' => 'Model Question',
                'target' => 'Size Question',
                'label' => 'answered',
                'description' => 'Model select hone ke baad Size pucho',
            ],
            [
                'source' => 'Size Question',
                'target' => 'Qty Question',
                'label' => 'answered',
                'description' => 'Size ke baad Quantity pucho',
            ],
            [
                'source' => 'Qty Question',
                'target' => 'End',
                'label' => 'complete',
                'description' => 'Product selection complete',
            ],
        ];

        // References - jaha flowchart logic use hota hai
        $references = [
            'node_types' => [
                [
                    'service' => 'QuestionnaireNode Model',
                    'file' => 'app/Models/QuestionnaireNode.php',
                    'ui_reference' => 'Admin Flowchart Builder',
                    'logic' => 'Node types define karta hai: TYPE_START, TYPE_QUESTION, TYPE_CONDITION, TYPE_ACTION, TYPE_END',
                ],
                [
                    'service' => 'FlowchartController',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Workflow Flowchart',
                    'logic' => 'Nodes create, update, delete karta hai. saveNode() method main handler hai.',
                ],
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'Flowchart traverse karta hai runtime pe. Start node se shuru hokar connections follow karta hai.',
                ],
            ],
            'connections' => [
                [
                    'service' => 'QuestionnaireConnection Model',
                    'file' => 'app/Models/QuestionnaireConnection.php',
                    'ui_reference' => 'Admin Flowchart Builder',
                    'logic' => 'source_node_id se target_node_id ko connect karta hai. Edges/Lines represent karta hai.',
                ],
                [
                    'service' => 'FlowchartController',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Workflow Flowchart',
                    'logic' => 'saveConnection() aur deleteConnection() methods edges manage karti hain.',
                ],
                [
                    'service' => 'QuestionnaireService',
                    'file' => 'app/Services/QuestionnaireService.php',
                    'ui_reference' => 'Chatbot Conversation',
                    'logic' => 'outgoingConnections() se next node find karta hai. Priority based routing support hai.',
                ],
            ],
            'sync_logic' => [
                [
                    'service' => 'FlowchartController::createLinkedField',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Workflow Flowchart',
                    'logic' => 'Question node banate waqt automatically ProductQuestion create karta hai.',
                ],
                [
                    'service' => 'FlowchartController::syncFieldOrderFromFlowchart',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Workflow Flowchart',
                    'logic' => 'Flowchart save hone pe ProductQuestion ki sort_order update karta hai flowchart sequence ke hisaab se.',
                ],
                [
                    'service' => 'QuestionnaireNode::syncToField',
                    'file' => 'app/Models/QuestionnaireNode.php',
                    'ui_reference' => 'Product Questions List',
                    'logic' => 'Node ka label, config ProductQuestion mein sync karta hai.',
                ],
            ],
            'save_operations' => [
                [
                    'service' => 'FlowchartController::saveAll',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Save Flow Button',
                    'logic' => 'Poora flowchart ek saath save karta hai. Old nodes delete, new create, edges map karta hai.',
                ],
                [
                    'service' => 'FlowchartController::getData',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Page Load',
                    'logic' => 'Saved flowchart data React Flow format mein return karta hai.',
                ],
                [
                    'service' => 'FlowchartController::clearFlow',
                    'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                    'ui_reference' => 'Clear Button',
                    'logic' => 'Poora flowchart delete kar deta hai - nodes aur connections dono.',
                ],
            ],
        ];

        // Demo Node Properties - Right sidebar mein jo fields dikhte hain jab node select karo
        $demoNodeProperties = [
            [
                'field' => 'Label',
                'type' => 'Text Input',
                'description' => 'Sirf display ke liye. Flowchart canvas pe node ka naam dikhata hai.',
                'connection' => '❌ Koi connection nahi - Sirf UI display',
                'connected_file' => 'N/A',
            ],
            [
                'field' => 'Select Question',
                'type' => 'Dropdown',
                'description' => 'Product Questions ya Global Questions mein se select karo. Jo select hoga uski SAARI SETTINGS connected rehti hain (is_unique_key, is_qty_field, options, etc.)',
                'connection' => '✅ Connected → ProductQuestion / GlobalQuestion model ki settings inherit hoti hain',
                'connected_file' => 'app/Models/ProductQuestion.php, app/Models/GlobalQuestion.php',
            ],
            [
                'field' => 'Ask Question Format',
                'type' => 'Textarea',
                'description' => 'Yahan question likhna hai jo bot puchega. Koi bhi language mein likh sakte ho. AI isko enhance karke NATURAL TONE mein puchega. Purpose same rehta hai, bus human-like ho jata hai. User ki language mein translate hota hai, yahan ki language mein nahi.',
                'connection' => '✅ Connected → AIService mein enhance hota hai → QuestionnaireService mein use hota hai',
                'connected_file' => 'app/Services/AIService.php, app/Services/QuestionnaireService.php',
            ],
            [
                'field' => 'Field Type',
                'type' => 'Dropdown',
                'description' => 'Required = Bot ko user se ZAROOR answer lena hai, tabhi next question pe jayega. Optional = Bot sirf EK BAAR puchega, agar user skip kare to next pe chala jayega.',
                'connection' => '✅ Connected → is_required field QuestionnaireNode mein → Bot logic mein check hota hai',
                'connected_file' => 'app/Models/QuestionnaireNode.php, app/Services/QuestionnaireService.php',
            ],
            [
                'field' => 'Lead Status',
                'type' => 'Dropdown',
                'description' => 'Is question ke baad lead ka status kya hoga. IMPORTANT: Agar Node 2 mein Status 3 set kiya, to Node 3,4,5 mein sirf Status 3,4,5 ka option aayega - niche ka status nahi aa sakta (ordering maintained).',
                'connection' => '✅ Connected → LeadStatus model → validateStatusId() method ordering check karta hai',
                'connected_file' => 'app/Models/LeadStatus.php, app/Models/QuestionnaireNode.php (validateStatusId)',
            ],
            [
                'field' => 'Delete Node',
                'type' => 'Button',
                'description' => 'Node delete karta hai. Saath mein connected ProductQuestion bhi delete + connected edges bhi remove ho jaati hain.',
                'connection' => '✅ Connected → ProductQuestion delete + QuestionnaireConnection delete',
                'connected_file' => 'app/Http/Controllers/Admin/FlowchartController.php (deleteNode)',
            ],
        ];

        // Node Properties References - kaha use hote hain WITH CONNECTED FILES
        $nodePropertiesReferences = [
            [
                'field' => 'Label',
                'service' => 'FlowchartController::saveNode',
                'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                'connected_to' => 'Sirf UI display, koi backend logic nahi',
                'logic' => '❌ node.label mein store hota hai, canvas pe render hota hai.',
                'is_connected' => false,
            ],
            [
                'field' => 'Select Question',
                'service' => 'ProductQuestion / GlobalQuestion Model',
                'file' => 'app/Models/ProductQuestion.php, app/Models/GlobalQuestion.php',
                'connected_to' => 'questionnaire_field_id → ProductQuestion.id ya global_question_id → GlobalQuestion.id',
                'logic' => '✅ is_unique_key ✅ is_qty_field ✅ is_unique_field ✅ options_source ✅ catalogue_field - sab inherit',
                'is_connected' => true,
            ],
            [
                'field' => 'Ask Question Format',
                'service' => 'AIService::enhanceQuestionTemplate',
                'file' => 'app/Services/AIService.php',
                'connected_to' => 'question_template → AI prompt → Enhanced human-like question',
                'logic' => '✅ AI enhance ✅ Natural tone ✅ User language translate ✅ Bot puchta hai',
                'is_connected' => true,
            ],
            [
                'field' => 'Field Type (Required/Optional)',
                'service' => 'QuestionnaireService::processNode',
                'file' => 'app/Services/QuestionnaireService.php',
                'connected_to' => 'is_required → Bot decision → Move to next or retry',
                'logic' => '✅ Required: Answer tak retry ✅ Optional: 1 baar pucho, skip to next',
                'is_connected' => true,
            ],
            [
                'field' => 'Lead Status',
                'service' => 'QuestionnaireNode::validateStatusId',
                'file' => 'app/Models/QuestionnaireNode.php',
                'connected_to' => 'lead_status_id → LeadStatus.id → Lead.status automatic update',
                'logic' => '✅ Status update ✅ Ordering validate ✅ Neeche ka status block',
                'is_connected' => true,
            ],
            [
                'field' => 'Delete Node',
                'service' => 'FlowchartController::deleteNode',
                'file' => 'app/Http/Controllers/Admin/FlowchartController.php',
                'connected_to' => 'Node → ProductQuestion → QuestionnaireConnection sab delete',
                'logic' => '✅ Node delete ✅ ProductQuestion delete ✅ Edges remove',
                'is_connected' => true,
            ],
        ];

        return view('superadmin.project-structure.flowchart', [
            'demoNodes' => $demoNodes,
            'demoConnections' => $demoConnections,
            'references' => $references,
            'demoNodeProperties' => $demoNodeProperties,
            'nodePropertiesReferences' => $nodePropertiesReferences,
        ]);
    }

    /**
     * Show Catalogue with Workflow connections
     */
    public function showCatalogue()
    {
        // Demo CatalogueField fields - ye Live project ke catalogue mein use hote hain
        $demoCatalogueFields = [
            [
                'field' => 'field_name',
                'type' => 'string',
                'description' => 'Catalogue column ka display name jo UI mein dikhta hai.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.field_name → Auto sync hota hai',
                'connected_file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'field' => 'field_key',
                'type' => 'string',
                'description' => 'Database mein store hone wala key (snake_case). field_name se auto generate.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.field_name → Str::snake() → field_key',
                'connected_file' => 'app/Models/CatalogueField.php (generateFieldKey)',
            ],
            [
                'field' => 'field_type',
                'type' => 'enum',
                'description' => 'Data type: text, number, select. ProductQuestion field_type se map hota hai.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.field_type → mapFieldType() → CatalogueField.field_type',
                'connected_file' => 'app/Models/ProductQuestion.php (mapFieldType)',
            ],
            [
                'field' => 'is_unique',
                'type' => 'boolean',
                'description' => 'Unique identifier field hai ya nahi (jaise Model Number). ProductQuestion.is_unique_key se sync.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.is_unique_key → CatalogueField.is_unique',
                'connected_file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'field' => 'is_required',
                'type' => 'boolean',
                'description' => 'Required field hai ya nahi. ProductQuestion.is_required se sync.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.is_required → CatalogueField.is_required',
                'connected_file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'field' => 'sort_order',
                'type' => 'integer',
                'description' => 'Column ka order. ProductQuestion.sort_order se sync.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.sort_order → CatalogueField.sort_order',
                'connected_file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'field' => 'options',
                'type' => 'array',
                'description' => 'Dropdown options (select type ke liye). ProductQuestion.options_manual se sync.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.options_manual → CatalogueField.options',
                'connected_file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'field' => 'product_question_id',
                'type' => 'foreign_key',
                'description' => 'Direct link to ProductQuestion. One-to-One relationship.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.id → CatalogueField.product_question_id',
                'connected_file' => 'app/Models/ProductQuestion.php (catalogueFieldRecord)',
            ],
        ];

        // Workflow → Catalogue Connections
        $workflowConnections = [
            [
                'source' => 'Flowchart Node',
                'target' => 'ProductQuestion',
                'connection_type' => '✅ One-to-One',
                'field' => 'questionnaire_field_id',
                'description' => 'Node select question dropdown se ProductQuestion link hota hai.',
                'logic' => '✅ Node create → ProductQuestion link ✅ Node delete → ProductQuestion delete option',
            ],
            [
                'source' => 'ProductQuestion',
                'target' => 'CatalogueField',
                'connection_type' => '✅ Auto Sync',
                'field' => 'product_question_id',
                'description' => 'ProductQuestion save hote hi CatalogueField auto create/update.',
                'logic' => '✅ Auto sync on save ✅ Auto delete on delete ✅ All fields mapped',
            ],
            [
                'source' => 'ProductQuestion',
                'target' => 'Catalogue Data',
                'connection_type' => '✅ Options Source',
                'field' => 'catalogue_field',
                'description' => 'options_source="catalogue" hone pe catalogue data se options fetch.',
                'logic' => '✅ Dynamic options ✅ Filtered by previous answers ✅ Case-insensitive match',
            ],
            [
                'source' => 'CatalogueField',
                'target' => 'Catalogue Import',
                'connection_type' => '✅ Column Mapping',
                'field' => 'field_key',
                'description' => 'Excel import mein columns CatalogueField se map hote hain.',
                'logic' => '✅ Column headers → field_key match ✅ Data validation ✅ Type checking',
            ],
        ];

        // Error/Disconnect Scenarios - CORRECT LOGIC
        $errorScenarios = [
            [
                'scenario' => 'ProductQuestion without CatalogueField',
                'error_type' => '⚠️ Warning',
                'cause' => 'ProductQuestion ke saare fields (except qty) Catalogue mein sync hone chahiye. GlobalQuestion fields Catalogue mein nahi jaate.',
                'impact' => 'Catalogue import mein column missing hoga, data save nahi hoga.',
                'fix' => 'ProductQuestion fields (except qty) auto-sync hote hain Catalogue mein. GlobalQuestion ignore karo.',
                'file' => 'app/Models/ProductQuestion.php (syncToCatalogueField)',
            ],
            [
                'scenario' => 'Flowchart → ProductQuestion → Catalogue Flow',
                'error_type' => '✅ Normal Flow',
                'cause' => 'User ne Flowchart Q1 answer kiya → Bot ne ProductQuestion se match kiya → Catalogue mein same field search kiya.',
                'impact' => 'Agar match mila to Lead mein save, fir next question. Same process repeat.',
                'fix' => 'Ye correct flow hai: Flowchart Q → ProductQuestion → Catalogue field search → Validate → Save to Lead → Next Q.',
                'file' => 'app/Services/QuestionnaireService.php',
            ],
            [
                'scenario' => 'Flowchart Question delete but Catalogue data exists',
                'error_type' => '✅ Ignore - Normal',
                'cause' => 'Flowchart question delete kiya but Catalogue mein data already imported hai.',
                'impact' => 'Catalogue data ko ignore karo - wo imported products hai. Flowchart rule-wise kaam karta hai.',
                'fix' => 'Flowchart question → ProductQuestion se match → Catalogue same field search → Same row filtered data as options.',
                'file' => 'app/Models/ProductQuestion.php (getFilteredOptions)',
            ],
            [
                'scenario' => 'Flowchart Node without ProductQuestion link',
                'error_type' => '❌ Error - Save Block',
                'cause' => 'Select Question dropdown mein koi question select nahi kiya.',
                'impact' => 'Flowchart save nahi hona chahiye jab tak Select Question set na ho.',
                'fix' => 'Save karte time validation add karo - "Select Question required hai" error dikhao jab tak select na ho.',
                'file' => 'app/Http/Controllers/Admin/FlowchartController.php (saveNode)',
            ],
            [
                'scenario' => 'Catalogue data case mismatch',
                'error_type' => '⚠️ Warning - Case Insensitive Fix',
                'cause' => '"Profile Handle" vs "profile handle" vs "PROFILE HANDLE" - case different hai.',
                'impact' => 'Filter aur validation fail ho sakta hai.',
                'fix' => 'Case-insensitive comparison use karo (LOWER function). Ye Catalogue aur ProductQuestion dono ke liye apply.',
                'file' => 'app/Models/ProductQuestion.php (getFilteredOptions - LOWER SQL)',
            ],
        ];

        // References - kaha use hota hai
        $references = [
            [
                'service' => 'ProductQuestion::syncToCatalogueField',
                'file' => 'app/Models/ProductQuestion.php',
                'connected_to' => 'CatalogueField create/update',
                'logic' => '✅ Auto sync on ProductQuestion::saved event. All fields mapped.',
            ],
            [
                'service' => 'ProductQuestion::booted (deleted)',
                'file' => 'app/Models/ProductQuestion.php',
                'connected_to' => 'CatalogueField cascade delete',
                'logic' => '✅ ProductQuestion delete → CatalogueField delete (by id and field_key both).',
            ],
            [
                'service' => 'ProductQuestion::getFilteredOptions',
                'file' => 'app/Models/ProductQuestion.php',
                'connected_to' => 'Catalogue data → Bot options',
                'logic' => '✅ Cascading filter based on previous answers. Case-insensitive matching.',
            ],
            [
                'service' => 'CatalogueMatchingService',
                'file' => 'app/Services/CatalogueMatchingService.php',
                'connected_to' => 'Product search → Lead data',
                'logic' => '✅ User answers → Catalogue match → Product recommendations.',
            ],
            [
                'service' => 'CatalogueImport',
                'file' => 'app/Imports/CatalogueImport.php',
                'connected_to' => 'Excel → Catalogue data',
                'logic' => '✅ Column headers → CatalogueField.field_key match → Data import.',
            ],
        ];

        return view('superadmin.project-structure.catalogue', [
            'demoCatalogueFields' => $demoCatalogueFields,
            'workflowConnections' => $workflowConnections,
            'errorScenarios' => $errorScenarios,
            'references' => $references,
        ]);
    }

    /**
     * Show Leads Section - Product Quotation Documentation
     */
    public function showLeads()
    {
        // Lead Model Fields
        $leadFields = [
            [
                'field' => 'id',
                'type' => 'integer',
                'description' => 'Lead ka unique ID.',
                'connection' => '✅ Connected',
                'connected_to' => 'Primary Key - LeadProduct.lead_id se link',
            ],
            [
                'field' => 'admin_id',
                'type' => 'foreign_key',
                'description' => 'Kis Admin (tenant) ka lead hai.',
                'connection' => '✅ Connected',
                'connected_to' => 'Admin.id → Multi-tenant support',
            ],
            [
                'field' => 'customer_id',
                'type' => 'foreign_key',
                'description' => 'Customer ka reference (WhatsApp user details).',
                'connection' => '✅ Connected',
                'connected_to' => 'Customer.id → Name, Phone, WhatsApp number',
            ],
            [
                'field' => 'stage',
                'type' => 'enum',
                'description' => 'Lead ka current stage: New Lead, Qualified, Confirm, Lose.',
                'connection' => '✅ Connected',
                'connected_to' => 'UI Pipeline display → Stage change triggers Client creation',
            ],
            [
                'field' => 'collected_data',
                'type' => 'JSON',
                'description' => 'Bot ke saare answers yahan store hote hain as JSON.',
                'connection' => '✅ Connected',
                'connected_to' => 'workflow_questions, global_questions, products arrays',
            ],
            [
                'field' => 'lead_score',
                'type' => 'integer',
                'description' => 'Auto-calculated score based on products, answers.',
                'connection' => '✅ Connected',
                'connected_to' => 'Lead::calculateScore() → Quality (hot/warm/cold)',
            ],
            [
                'field' => 'bot_active',
                'type' => 'boolean',
                'description' => 'Kya bot abhi active hai is lead ke liye.',
                'connection' => '✅ Connected',
                'connected_to' => 'QuestionnaireService → Bot flow control',
            ],
        ];

        // Product Quotation Table Fields (LeadProduct)
        $productQuotationFields = [
            [
                'field' => 'lead_id',
                'type' => 'foreign_key',
                'description' => 'Kis lead ka product hai.',
                'connection' => '✅ Connected',
                'connected_to' => 'Lead.id → One lead has many products',
            ],
            [
                'field' => 'category / model / size / finish',
                'type' => 'string columns',
                'description' => 'Direct columns for common product fields.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion fields → Same field names sync',
            ],
            [
                'field' => 'data (JSON)',
                'type' => 'JSON',
                'description' => 'Dynamic fields jo ProductQuestion se aate hain.',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.field_name → All workflow answers store here',
            ],
            [
                'field' => 'unique_key',
                'type' => 'string',
                'description' => 'Combination of unique key fields (model|size|finish).',
                'connection' => '✅ Connected',
                'connected_to' => 'ProductQuestion.is_unique_key fields → Generates unique identifier',
            ],
            [
                'field' => 'source',
                'type' => 'enum',
                'description' => 'Product kahan se aaya: bot, manual, import.',
                'connection' => '✅ Connected',
                'connected_to' => 'UI display → Shows source badge',
            ],
        ];

        // Data Flow - Workflow se Product Quotation tak
        $dataFlow = [
            [
                'step' => '1',
                'source' => 'WhatsApp Message',
                'target' => 'WebhookController',
                'description' => 'User ka message webhook pe aata hai.',
                'connection' => '✅',
                'file' => 'app/Http/Controllers/Api/WebhookController.php',
            ],
            [
                'step' => '2',
                'source' => 'WebhookController',
                'target' => 'QuestionnaireService',
                'description' => 'Message process karta hai, flowchart node find karta hai.',
                'connection' => '✅',
                'file' => 'app/Services/QuestionnaireService.php',
            ],
            [
                'step' => '3',
                'source' => 'QuestionnaireService',
                'target' => 'Lead.collected_data',
                'description' => 'Answer ko workflow_questions mein save karta hai.',
                'connection' => '✅',
                'file' => 'app/Models/Lead.php (addCollectedData)',
            ],
            [
                'step' => '4',
                'source' => 'Lead.collected_data',
                'target' => 'LeadProduct',
                'description' => 'Jab unique keys complete → LeadProduct row create.',
                'connection' => '✅',
                'file' => 'app/Models/LeadProduct.php (createFromCollectedData)',
            ],
            [
                'step' => '5',
                'source' => 'LeadProduct',
                'target' => 'Product Quotation UI',
                'description' => 'Admin panel mein Lead detail page pe table display.',
                'connection' => '✅',
                'file' => 'resources/views/admin/leads/show.blade.php',
            ],
        ];

        // Product Quotation Logic
        $quotationLogic = [
            [
                'scenario' => 'Table Columns Kahan Se Aate Hain?',
                'logic' => 'ProductQuestion → active fields → display_name as column header',
                'connected' => '✅ Connected',
                'file' => 'LeadController::show() → $productFields',
            ],
            [
                'scenario' => 'Data Kahan Se Aata Hai?',
                'logic' => 'LeadProduct.data JSON + direct columns (category, model, size, finish)',
                'connected' => '✅ Connected',
                'file' => 'LeadProduct::toProductArray()',
            ],
            [
                'scenario' => 'Fallback Columns',
                'logic' => 'Agar ProductQuestion empty → CatalogueField se columns fetch',
                'connected' => '✅ Connected',
                'file' => 'LeadController::show() → CatalogueField fallback',
            ],
            [
                'scenario' => 'Product Delete Kaise Hota Hai?',
                'logic' => 'Passcode verify → LeadProduct row delete',
                'connected' => '✅ Connected',
                'file' => 'LeadController::deleteProduct()',
            ],
            [
                'scenario' => 'Product Edit Kaise Hota Hai?',
                'logic' => 'LeadProduct.data JSON update → save',
                'connected' => '✅ Connected',
                'file' => 'LeadController::updateProduct()',
            ],
        ];

        // Error Scenarios
        $errorScenarios = [
            [
                'scenario' => 'Product Quotation table empty dikha',
                'error_type' => '⚠️ Warning',
                'cause' => 'lead_products table mein koi row nahi ya ProductQuestion define nahi.',
                'fix' => 'Workflow complete karo - unique keys filled hone pe row create hoga.',
            ],
            [
                'scenario' => 'Column headers missing',
                'error_type' => '⚠️ Warning',
                'cause' => 'ProductQuestion mein koi active field nahi.',
                'fix' => 'Workflow → Product Questions mein fields add karo.',
            ],
            [
                'scenario' => 'Data values missing in cells',
                'error_type' => '⚠️ Warning',
                'cause' => 'LeadProduct.data mein wo field nahi ya user ne answer nahi diya.',
                'fix' => 'Bot flow check karo - sab questions answered hone chahiye.',
            ],
            [
                'scenario' => 'Duplicate products showing',
                'error_type' => '❌ Error',
                'cause' => 'unique_key same hai multiple rows mein.',
                'fix' => 'unique_key generation logic check karo - is_unique_key fields correct honi chahiye.',
            ],
        ];

        // References
        $references = [
            [
                'service' => 'Lead Model',
                'file' => 'app/Models/Lead.php',
                'connected_to' => 'LeadProduct, Customer, collected_data',
                'logic' => '✅ Main lead data, stage tracking, score calculation',
            ],
            [
                'service' => 'LeadProduct Model',
                'file' => 'app/Models/LeadProduct.php',
                'connected_to' => 'Lead, ProductQuestion',
                'logic' => '✅ Product Quotation table rows, data JSON storage',
            ],
            [
                'service' => 'LeadController',
                'file' => 'app/Http/Controllers/Admin/LeadController.php',
                'connected_to' => 'Product Quotation UI',
                'logic' => '✅ show() fetches productFields, CRUD for products',
            ],
            [
                'service' => 'WebhookController',
                'file' => 'app/Http/Controllers/Api/WebhookController.php',
                'connected_to' => 'QuestionnaireService',
                'logic' => '✅ Syncs workflow answers to LeadProduct',
            ],
            [
                'service' => 'QuestionnaireService',
                'file' => 'app/Services/QuestionnaireService.php',
                'connected_to' => 'Lead.collected_data',
                'logic' => '✅ Processes bot answers, saves to lead',
            ],
        ];

        return view('superadmin.project-structure.leads', [
            'leadFields' => $leadFields,
            'productQuotationFields' => $productQuotationFields,
            'dataFlow' => $dataFlow,
            'quotationLogic' => $quotationLogic,
            'errorScenarios' => $errorScenarios,
            'references' => $references,
        ]);
    }
}
