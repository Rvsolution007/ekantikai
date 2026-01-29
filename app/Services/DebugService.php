<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\ProductQuestion;
use App\Models\QuestionnaireNode;
use App\Models\QuestionnaireConnection;
use App\Models\GlobalQuestion;
use App\Models\LeadStatus;
use App\Models\Catalogue;
use App\Models\Lead;
use App\Models\LeadProduct;
use App\Models\Product;

class DebugService
{
    /**
     * Run a full debug scan for an admin
     */
    public function runFullScan(Admin $admin): array
    {
        $checks = [];
        $errors = [];
        $warnings = [];

        // Get all data for this admin
        $questions = ProductQuestion::where('admin_id', $admin->id)->where('is_active', true)->get();
        $nodes = QuestionnaireNode::where('admin_id', $admin->id)->where('is_active', true)->get();
        $connections = QuestionnaireConnection::where('admin_id', $admin->id)->get();
        $globalQuestions = GlobalQuestion::where('admin_id', $admin->id)->where('is_active', true)->get();
        $statuses = LeadStatus::where('admin_id', $admin->id)->get();
        $catalogues = Catalogue::where('admin_id', $admin->id)->get();

        // === CHECK 1: Start Node Exists ===
        $startNodes = $nodes->where('node_type', 'start');
        if ($startNodes->count() > 0) {
            $checks[] = [
                'name' => 'Start Node Exists',
                'status' => 'pass',
                'details' => "Found {$startNodes->count()} start node(s): " . $startNodes->pluck('id')->implode(', ')
            ];
        } else {
            $errors[] = [
                'id' => 'ERR-START',
                'name' => 'No Start Node',
                'severity' => 'critical',
                'details' => 'No start node found in flowchart. Bot cannot begin conversation.',
                'fix' => 'Add a Start node in the Flowchart Builder'
            ];
        }

        // === CHECK 2: Flow is Connected ===
        if ($connections->count() > 0) {
            $checks[] = [
                'name' => 'Flow Connections',
                'status' => 'pass',
                'details' => "{$connections->count()} connections found"
            ];
        } else {
            $errors[] = [
                'id' => 'ERR-CONN',
                'name' => 'No Flow Connections',
                'severity' => 'critical',
                'details' => 'No connections between nodes. Bot cannot navigate the flowchart.',
                'fix' => 'Connect nodes in the Flowchart Builder'
            ];
        }

        // === CHECK 3: Unique Key Fields ===
        $uniqueKeys = $questions->where('is_unique_key', true)->sortBy('unique_key_order');
        if ($uniqueKeys->count() > 0) {
            $keyNames = $uniqueKeys->pluck('field_name')->implode(' → ');
            $checks[] = [
                'name' => 'Unique Key Fields Configured',
                'status' => 'pass',
                'details' => "Keys: {$keyNames}"
            ];
        } else {
            $warnings[] = [
                'id' => 'WARN-UKEY',
                'name' => 'No Unique Key Fields',
                'severity' => 'warning',
                'details' => 'No unique key fields defined. Product rows cannot be uniquely identified.',
                'fix' => 'Mark key fields (like model) as unique key in Workflow Fields'
            ];
        }

        // === CHECK 4: Qty Field ===
        $qtyFields = $questions->where('is_qty_field', true);
        if ($qtyFields->count() > 0) {
            $checks[] = [
                'name' => 'Qty Field Configured',
                'status' => 'pass',
                'details' => "Qty field: " . $qtyFields->pluck('field_name')->implode(', ')
            ];
        } else {
            $warnings[] = [
                'id' => 'WARN-QTY',
                'name' => 'No Qty Field',
                'severity' => 'warning',
                'details' => 'No quantity field marked. Bot won\'t ask for product quantity.',
                'fix' => 'Mark a field as Qty Field in Workflow Fields'
            ];
        }

        // === CHECK 5: Catalogue Data ===
        if ($catalogues->count() > 0) {
            $checks[] = [
                'name' => 'Catalogue Data Loaded',
                'status' => 'pass',
                'details' => "{$catalogues->count()} catalogue items"
            ];

            // Check catalogue keys match field names
            $sampleCat = $catalogues->first();
            if ($sampleCat && $sampleCat->data) {
                $catKeys = array_keys(is_array($sampleCat->data) ? $sampleCat->data : (json_decode($sampleCat->data, true) ?: []));
                $fieldNames = $questions->pluck('field_name')->toArray();
                $matchingKeys = array_intersect($catKeys, $fieldNames);

                if (count($matchingKeys) > 0) {
                    $checks[] = [
                        'name' => 'Catalogue Keys Match Fields',
                        'status' => 'pass',
                        'details' => "Matching keys: " . implode(', ', $matchingKeys)
                    ];
                }
            }
        } else {
            $errors[] = [
                'id' => 'ERR-CAT',
                'name' => 'No Catalogue Data',
                'severity' => 'high',
                'details' => 'No catalogue items found. Bot cannot provide product options.',
                'fix' => 'Import catalogue data via Excel upload'
            ];
        }

        // === CHECK 6: All Product Questions Have Nodes ===
        $questionIds = $questions->pluck('id')->toArray();
        $mappedFieldIds = $nodes->whereNotNull('questionnaire_field_id')->pluck('questionnaire_field_id')->toArray();
        $unmappedQuestions = $questions->whereNotIn('id', $mappedFieldIds);

        if ($unmappedQuestions->count() === 0) {
            $checks[] = [
                'name' => 'All Product Questions in Flow',
                'status' => 'pass',
                'details' => "All {$questions->count()} questions have flowchart nodes"
            ];
        } else {
            $unmappedNames = $unmappedQuestions->pluck('field_name')->implode(', ');
            $errors[] = [
                'id' => 'ERR-UNMAPPED',
                'name' => 'Questions Not in Flowchart',
                'severity' => 'high',
                'details' => "Fields without nodes: {$unmappedNames}",
                'fix' => 'Add question nodes for these fields in Flowchart Builder'
            ];
        }

        // === CHECK 7: Global Questions Integrated ===
        $mappedGlobalIds = $nodes->whereNotNull('global_question_id')->pluck('global_question_id')->toArray();
        $unmappedGlobals = $globalQuestions->whereNotIn('id', $mappedGlobalIds);

        if ($globalQuestions->count() === 0) {
            // No global questions defined - that's okay
            $checks[] = [
                'name' => 'Global Questions',
                'status' => 'pass',
                'details' => 'No global questions defined (optional)'
            ];
        } elseif ($unmappedGlobals->count() === 0) {
            $checks[] = [
                'name' => 'Global Questions in Flow',
                'status' => 'pass',
                'details' => "All {$globalQuestions->count()} global questions have nodes"
            ];
        } else {
            $unmappedNames = $unmappedGlobals->pluck('field_name')->implode(', ');
            $errors[] = [
                'id' => 'ERR-GLOBAL',
                'name' => 'Global Questions Not in Flow',
                'severity' => 'high',
                'details' => "Global questions without nodes: {$unmappedNames}",
                'fix' => 'Add global question nodes in Flowchart Builder'
            ];
        }

        // === CHECK 8: Catalogue Field Mapping ===
        $catalogueLinkedFields = $questions->where('options_source', 'catalogue');
        $missingCatalogueField = $catalogueLinkedFields->filter(function ($q) {
            return empty($q->catalogue_field);
        });

        if ($catalogueLinkedFields->count() === 0) {
            $checks[] = [
                'name' => 'Catalogue Field Mapping',
                'status' => 'pass',
                'details' => 'No fields linked to catalogue (using manual options)'
            ];
        } elseif ($missingCatalogueField->count() === 0) {
            $checks[] = [
                'name' => 'Catalogue Field Mapping',
                'status' => 'pass',
                'details' => "All catalogue-linked fields have catalogue_field set"
            ];
        } else {
            $missingNames = $missingCatalogueField->pluck('field_name')->implode(', ');
            $errors[] = [
                'id' => 'ERR-CATFIELD',
                'name' => 'Missing Catalogue Field Mapping',
                'severity' => 'critical',
                'details' => "Fields with options_source='catalogue' but no catalogue_field: {$missingNames}",
                'fix' => 'Set catalogue_field for these fields in Workflow Fields'
            ];
        }

        // === CHECK 9: Lead Statuses ===
        if ($statuses->count() > 0) {
            $checks[] = [
                'name' => 'Lead Statuses Configured',
                'status' => 'pass',
                'details' => "{$statuses->count()} statuses: " . $statuses->pluck('name')->implode(', ')
            ];
        } else {
            $warnings[] = [
                'id' => 'WARN-STATUS',
                'name' => 'No Lead Statuses',
                'severity' => 'warning',
                'details' => 'No custom lead statuses defined.',
                'fix' => 'Create lead statuses for tracking lead progress'
            ];
        }

        // === CHECK 10: Product-Model Validation (n8n parity) ===
        // This validates that each product's model exists in catalogue for its category
        // Products table uses 'product' for category, 'model' for model code
        $products = Product::whereHas('lead', function ($q) use ($admin) {
            $q->where('admin_id', $admin->id);
        })->get();
        $invalidProducts = [];

        // Build category->models map from catalogue
        $categoryModels = [];
        foreach ($catalogues as $cat) {
            $data = is_array($cat->data) ? $cat->data : (json_decode($cat->data, true) ?: []);
            $category = strtolower(trim($data['category'] ?? ''));
            $model = strtolower(trim($data['model'] ?? ''));

            if ($category && $model) {
                if (!isset($categoryModels[$category])) {
                    $categoryModels[$category] = [];
                }
                $categoryModels[$category][$model] = true;
            }
        }

        // Validate each product (direct field access, not JSON)
        foreach ($products as $prod) {
            $category = strtolower(trim($prod->product ?? ''));
            $model = strtolower(trim($prod->model ?? ''));

            if (!$category || !$model) {
                continue; // Skip incomplete products
            }

            // Check if this category exists in catalogue
            if (!isset($categoryModels[$category])) {
                $invalidProducts[] = [
                    'lead_id' => $prod->lead_id,
                    'product_id' => $prod->id,
                    'category' => $prod->product,
                    'model' => $prod->model,
                    'issue' => 'Category not found in catalogue',
                ];
                continue;
            }

            // Check if this model exists for this category
            if (!isset($categoryModels[$category][$model])) {
                $invalidProducts[] = [
                    'lead_id' => $prod->lead_id,
                    'product_id' => $prod->id,
                    'category' => $prod->product,
                    'model' => $prod->model,
                    'issue' => "Model '{$prod->model}' does not exist in '{$prod->product}' catalogue",
                    'available_in' => $this->findCategoriesForModel($categoryModels, $model),
                ];
            }
        }

        if (count($invalidProducts) === 0 && $products->count() > 0) {
            $checks[] = [
                'name' => 'Product-Model Catalogue Validation',
                'status' => 'pass',
                'details' => "All {$products->count()} products have valid category-model combinations"
            ];
        } elseif (count($invalidProducts) > 0) {
            // Group by issue type for clearer error messages
            $modelMismatches = array_filter($invalidProducts, fn($p) => str_contains($p['issue'], 'does not exist'));
            $categoryMissing = array_filter($invalidProducts, fn($p) => str_contains($p['issue'], 'Category not found'));

            if (count($modelMismatches) > 0) {
                $examples = array_slice($modelMismatches, 0, 5);
                $exampleText = implode('; ', array_map(function ($p) {
                    $availableIn = !empty($p['available_in']) ? " (exists in: " . implode(', ', $p['available_in']) . ")" : '';
                    return "{$p['category']}+{$p['model']}{$availableIn}";
                }, $examples));

                $errors[] = [
                    'id' => 'ERR-MODEL-MISMATCH',
                    'name' => 'Invalid Category-Model Combinations',
                    'severity' => 'critical',
                    'details' => count($modelMismatches) . " product(s) have models that don't exist in their category. Examples: {$exampleText}",
                    'fix' => 'Bot is assigning models to wrong categories. Check AI response parsing or catalogue field mapping.',
                    'affected_count' => count($modelMismatches),
                    'examples' => $examples,
                ];
            }

            if (count($categoryMissing) > 0) {
                $categories = array_unique(array_column($categoryMissing, 'category'));
                $errors[] = [
                    'id' => 'ERR-CATEGORY-MISSING',
                    'name' => 'Unknown Categories in Products',
                    'severity' => 'high',
                    'details' => count($categoryMissing) . " product(s) have categories not in catalogue: " . implode(', ', $categories),
                    'fix' => 'Add these categories to catalogue or fix category normalization',
                    'affected_count' => count($categoryMissing),
                ];
            }
        }

        // === Compute Badge ===
        $criticalErrors = collect($errors)->where('severity', 'critical')->count();
        $badge = $criticalErrors === 0 && count($errors) === 0 ? 'CONNECTED' : 'NOT CONNECTED';

        // === Build inventory ===
        $inventory = [
            'product_questions' => $questions->count(),
            'unique_key_fields' => $uniqueKeys->count(),
            'qty_fields' => $qtyFields->count(),
            'questionnaire_nodes' => $nodes->count(),
            'questionnaire_connections' => $connections->count(),
            'global_questions' => $globalQuestions->count(),
            'lead_statuses' => $statuses->count(),
            'catalogues' => $catalogues->count(),
            'leads' => Lead::where('admin_id', $admin->id)->count(),
            'lead_products' => LeadProduct::where('admin_id', $admin->id)->count(),
        ];

        return [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'scanned_at' => now()->toIso8601String(),
            'badge' => $badge,
            'checks_passed' => $checks,
            'checks_failed' => $errors,
            'warnings' => $warnings,
            'inventory' => $inventory,
            'summary' => [
                'total_checks' => count($checks) + count($errors) + count($warnings),
                'passed' => count($checks),
                'failed' => count($errors),
                'warnings' => count($warnings),
            ]
        ];
    }

    /**
     * Get quick badge status for all admins (for list view)
     */
    public function getAdminBadges(): array
    {
        $admins = Admin::all();
        $badges = [];

        foreach ($admins as $admin) {
            // Quick check - just count critical issues
            $hasStartNode = QuestionnaireNode::where('admin_id', $admin->id)
                ->where('node_type', 'start')
                ->where('is_active', true)
                ->exists();

            $hasConnections = QuestionnaireConnection::where('admin_id', $admin->id)->exists();
            $hasCatalogue = Catalogue::where('admin_id', $admin->id)->exists();

            $isConnected = $hasStartNode && $hasConnections && $hasCatalogue;

            $badges[$admin->id] = [
                'admin' => $admin,
                'badge' => $isConnected ? 'CONNECTED' : 'NOT CONNECTED',
                'quick_checks' => [
                    'start_node' => $hasStartNode,
                    'connections' => $hasConnections,
                    'catalogue' => $hasCatalogue,
                ]
            ];
        }

        return $badges;
    }

    /**
     * Find all categories that contain a specific model
     */
    private function findCategoriesForModel(array $categoryModels, string $model): array
    {
        $categories = [];
        $model = strtolower(trim($model));

        foreach ($categoryModels as $category => $models) {
            if (isset($models[$model])) {
                $categories[] = $category;
            }
        }

        return $categories;
    }
}
