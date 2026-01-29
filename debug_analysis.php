<?php
/**
 * Debug Analysis Script - READ ONLY
 * Collects inventory data for all admins
 * NO MODIFICATIONS - ANALYSIS ONLY
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\ProductQuestion;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireConnection;
use App\Models\Catalogue;
use App\Models\GlobalQuestion;
use App\Models\LeadStatus;
use App\Models\Lead;
use App\Models\LeadProduct;

echo "=== DEBUG ANALYSIS REPORT ===\n";
echo "Generated: " . now()->toIso8601String() . "\n\n";

// Part 1: Admin List
echo "=== PART 1: INVENTORY SNAPSHOT ===\n\n";

$admins = Admin::all();
echo "Total Admins: " . $admins->count() . "\n\n";

$report = [
    'generated_at' => now()->toIso8601String(),
    'admins' => []
];

foreach ($admins as $admin) {
    echo "--- Admin ID: {$admin->id} ({$admin->name}) ---\n";

    $adminData = [
        'admin_id' => $admin->id,
        'admin_name' => $admin->name,
        'product_questions' => [],
        'unique_key_fields' => [],
        'qty_fields' => [],
        'catalogue_linked_fields' => [],
        'questionnaire_nodes' => [],
        'questionnaire_connections' => [],
        'global_questions' => [],
        'lead_statuses' => [],
        'catalogues_count' => 0,
        'leads_count' => 0,
        'lead_products_count' => 0,
        'sample_catalogue_keys' => []
    ];

    // Product Questions
    $questions = ProductQuestion::where('admin_id', $admin->id)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    echo "  Product Questions: " . $questions->count() . "\n";

    foreach ($questions as $q) {
        $qData = [
            'id' => $q->id,
            'field_name' => $q->field_name,
            'display_name' => $q->display_name,
            'field_type' => $q->field_type,
            'is_required' => $q->is_required,
            'is_unique_key' => $q->is_unique_key,
            'unique_key_order' => $q->unique_key_order,
            'is_qty_field' => $q->is_qty_field ?? false,
            'options_source' => $q->options_source,
            'catalogue_field' => $q->catalogue_field
        ];
        $adminData['product_questions'][] = $qData;

        if ($q->is_unique_key) {
            $adminData['unique_key_fields'][] = [
                'field_name' => $q->field_name,
                'order' => $q->unique_key_order
            ];
        }
        if ($q->is_qty_field ?? false) {
            $adminData['qty_fields'][] = $q->field_name;
        }
        if ($q->options_source === 'catalogue') {
            $adminData['catalogue_linked_fields'][] = [
                'field_name' => $q->field_name,
                'catalogue_field' => $q->catalogue_field
            ];
        }
    }

    // Sort unique key fields by order
    usort($adminData['unique_key_fields'], fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

    echo "  Unique Key Fields: " . count($adminData['unique_key_fields']) . "\n";
    echo "  Qty Fields: " . count($adminData['qty_fields']) . "\n";
    echo "  Catalogue Linked Fields: " . count($adminData['catalogue_linked_fields']) . "\n";

    // Questionnaire Nodes
    $nodes = QuestionnaireNode::where('admin_id', $admin->id)
        ->where('is_active', true)
        ->get();

    echo "  Questionnaire Nodes: " . $nodes->count() . "\n";

    $startNodes = [];
    foreach ($nodes as $node) {
        $nodeData = [
            'id' => $node->id,
            'node_type' => $node->node_type,
            'label' => $node->label,
            'questionnaire_field_id' => $node->questionnaire_field_id,
            'global_question_id' => $node->global_question_id,
            'lead_status_id' => $node->lead_status_id,
            'is_required' => $node->is_required,
            'ask_digit' => $node->ask_digit,
            'is_unique_field' => $node->is_unique_field
        ];
        $adminData['questionnaire_nodes'][] = $nodeData;

        if ($node->node_type === 'start') {
            $startNodes[] = $node->id;
        }
    }

    echo "  Start Nodes: " . count($startNodes) . "\n";
    $adminData['start_node_ids'] = $startNodes;

    // Questionnaire Connections
    $connections = QuestionnaireConnection::where('admin_id', $admin->id)->get();
    echo "  Questionnaire Connections: " . $connections->count() . "\n";

    foreach ($connections as $conn) {
        $adminData['questionnaire_connections'][] = [
            'id' => $conn->id,
            'source_node_id' => $conn->source_node_id,
            'target_node_id' => $conn->target_node_id,
            'priority' => $conn->priority,
            'condition' => $conn->condition
        ];
    }

    // Global Questions
    $globals = GlobalQuestion::where('admin_id', $admin->id)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    echo "  Global Questions: " . $globals->count() . "\n";

    foreach ($globals as $gq) {
        $adminData['global_questions'][] = [
            'id' => $gq->id,
            'field_name' => $gq->field_name,
            'display_name' => $gq->display_name,
            'field_type' => $gq->field_type,
            'is_required' => $gq->is_required
        ];
    }

    // Lead Statuses
    $statuses = LeadStatus::where('admin_id', $admin->id)
        ->orderBy('order')
        ->get();

    echo "  Lead Statuses: " . $statuses->count() . "\n";

    foreach ($statuses as $st) {
        $adminData['lead_statuses'][] = [
            'id' => $st->id,
            'name' => $st->name,
            'order' => $st->order,
            'is_default' => $st->is_default
        ];
    }

    // Catalogues
    $catalogues = Catalogue::where('admin_id', $admin->id)->get();
    $adminData['catalogues_count'] = $catalogues->count();
    echo "  Catalogues: " . $catalogues->count() . "\n";

    // Sample catalogue data keys
    $sampleCat = $catalogues->first();
    if ($sampleCat && $sampleCat->data) {
        $data = is_array($sampleCat->data) ? $sampleCat->data : json_decode($sampleCat->data, true);
        if (is_array($data)) {
            $adminData['sample_catalogue_keys'] = array_keys($data);
            echo "  Sample Catalogue Keys: " . implode(', ', $adminData['sample_catalogue_keys']) . "\n";
        }
    }

    // Leads
    $adminData['leads_count'] = Lead::where('admin_id', $admin->id)->count();
    echo "  Leads: " . $adminData['leads_count'] . "\n";

    // Lead Products
    $adminData['lead_products_count'] = LeadProduct::where('admin_id', $admin->id)->count();
    echo "  Lead Products: " . $adminData['lead_products_count'] . "\n";

    // Sample Lead collected_data structure
    $sampleLead = Lead::where('admin_id', $admin->id)
        ->whereNotNull('collected_data')
        ->latest()
        ->first();

    if ($sampleLead && $sampleLead->collected_data) {
        $cd = is_array($sampleLead->collected_data) ? $sampleLead->collected_data : json_decode($sampleLead->collected_data, true);
        if (is_array($cd)) {
            $adminData['sample_collected_data_structure'] = array_keys($cd);
            echo "  Sample collected_data keys: " . implode(', ', array_keys($cd)) . "\n";
        }
    }

    // Sample Lead Product data structure
    $sampleLP = LeadProduct::where('admin_id', $admin->id)
        ->whereNotNull('data')
        ->first();

    if ($sampleLP && $sampleLP->data) {
        $lpData = is_array($sampleLP->data) ? $sampleLP->data : json_decode($sampleLP->data, true);
        if (is_array($lpData)) {
            $adminData['sample_lead_product_data_keys'] = array_keys($lpData);
            echo "  Sample lead_products.data keys: " . implode(', ', array_keys($lpData)) . "\n";
        }
    }

    $report['admins'][] = $adminData;
    echo "\n";
}

// Output full report as JSON
echo "\n=== FULL REPORT JSON ===\n";
$jsonOutput = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo $jsonOutput;
echo "\n";

// Save to file
file_put_contents(__DIR__ . '/debug_report.json', $jsonOutput);
echo "\n=== Report saved to debug_report.json ===\n";
