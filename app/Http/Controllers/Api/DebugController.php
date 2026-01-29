<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadProduct;
use App\Services\ProductConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    /**
     * Test multi-value splitting logic
     * URL: /api/debug/multi-value-test?lead_id=123
     */
    public function testMultiValueSplit(Request $request)
    {
        $leadId = $request->input('lead_id');

        if (!$leadId) {
            return response()->json(['error' => 'lead_id required'], 400);
        }

        $lead = Lead::find($leadId);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found'], 404);
        }

        // Get current LeadProducts
        $currentProducts = LeadProduct::where('lead_id', $leadId)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'category' => $p->category,
                'model' => $p->model,
                'size' => $p->size,
                'finish' => $p->finish,
                'unique_key' => $p->unique_key,
                'created_at' => $p->created_at->toDateTimeString(),
            ];
        });

        // Get workflow_questions from lead
        $workflowQuestions = $lead->collected_data['workflow_questions'] ?? [];

        // Get product_confirmations from lead
        $productConfirmations = $lead->product_confirmations ?? [];

        return response()->json([
            'lead_id' => $leadId,
            'admin_id' => $lead->admin_id,

            '1_workflow_questions' => [
                'description' => 'Data stored from smart extraction (for flowchart progress)',
                'data' => $workflowQuestions,
            ],

            '2_product_confirmations' => [
                'description' => 'Data from AI product confirmations (stored in lead)',
                'count' => count($productConfirmations),
                'data' => $productConfirmations,
            ],

            '3_current_lead_products' => [
                'description' => 'Actual rows in lead_products table (Product Quotation)',
                'count' => count($currentProducts),
                'data' => $currentProducts,
            ],

            '4_diagnosis' => $this->diagnose($workflowQuestions, $productConfirmations, $currentProducts),
        ]);
    }

    /**
     * Diagnose the issue
     */
    private function diagnose($workflowQuestions, $productConfirmations, $currentProducts)
    {
        $issues = [];

        // Check if workflow_questions has combined value
        $category = $workflowQuestions['category'] ?? '';
        if (str_contains($category, ',') || str_contains(strtolower($category), ' or ')) {
            $issues[] = "❌ workflow_questions.category has combined value: '$category'";
        }

        // Check product_confirmations
        if (count($productConfirmations) === 0) {
            $issues[] = "⚠️ No product_confirmations stored in lead";
        } else {
            foreach ($productConfirmations as $i => $pc) {
                $cat = $pc['category'] ?? '';
                if (str_contains($cat, ',') || str_contains(strtolower($cat), ' or ')) {
                    $issues[] = "❌ product_confirmations[$i].category has combined value: '$cat'";
                }
            }
        }

        // Check lead_products
        foreach ($currentProducts as $product) {
            $cat = $product['category'] ?? '';
            if (str_contains($cat, ',') || str_contains(strtolower($cat), ' or ')) {
                $issues[] = "❌ LeadProduct ID {$product['id']} has combined category: '$cat'";
            }
        }

        if (empty($issues)) {
            return ['status' => '✅ No combined values detected', 'issues' => []];
        }

        return [
            'status' => '❌ Issues found',
            'issues' => $issues,
            'recommendation' => 'Delete this lead and test with fresh message',
        ];
    }

    /**
     * Simulate multi-value split processing
     * URL: /api/debug/simulate-split?category=Profile handles, Knob handles
     */
    public function simulateSplit(Request $request)
    {
        $category = $request->input('category', 'Profile handles, Knob handles');

        // Create mock confirmations
        $mockConfirmations = [
            ['category' => $category]
        ];

        $service = app(ProductConfirmationService::class);

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('splitMultiValueConfirmations');
        $method->setAccessible(true);

        $splitResult = $method->invoke($service, $mockConfirmations);

        return response()->json([
            'input' => $mockConfirmations,
            'after_split' => $splitResult,
            'split_count' => count($splitResult),
            'expected' => 'Should be 2 separate confirmations',
        ]);
    }

    /**
     * Force recreate LeadProducts from product_confirmations
     * URL: /api/debug/force-recreate?lead_id=123
     */
    public function forceRecreate(Request $request)
    {
        $leadId = $request->input('lead_id');

        if (!$leadId) {
            return response()->json(['error' => 'lead_id required'], 400);
        }

        $lead = Lead::find($leadId);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found'], 404);
        }

        // Delete existing LeadProducts
        $deleted = LeadProduct::where('lead_id', $leadId)->delete();

        // Get product_confirmations
        $productConfirmations = $lead->product_confirmations ?? [];

        // FALLBACK: If product_confirmations is empty, generate from workflow_questions
        if (empty($productConfirmations)) {
            $workflowQ = $lead->collected_data['workflow_questions'] ?? [];

            if (!empty($workflowQ['category'])) {
                $category = $workflowQ['category'];
                $splitPattern = '/\s*(?:,|\s+or\s+|\s+and\s+|\s+aur\s+)\s*/i';

                // Check if combined value
                if (preg_match($splitPattern, $category)) {
                    $categories = preg_split($splitPattern, $category);
                    $categories = array_filter(array_map('trim', $categories));

                    foreach ($categories as $singleCategory) {
                        $confirmation = $workflowQ;
                        $confirmation['category'] = $singleCategory;
                        $productConfirmations[] = $confirmation;
                    }
                } else {
                    $productConfirmations[] = $workflowQ;
                }

                // Save the generated confirmations back to lead
                $lead->product_confirmations = $productConfirmations;
                $lead->save();

                Log::info('Force Recreate: Generated product_confirmations from workflow_questions', [
                    'lead_id' => $leadId,
                    'generated' => $productConfirmations,
                ]);
            }
        }

        $service = app(ProductConfirmationService::class);
        $results = $service->processConfirmations($lead, $productConfirmations);

        // Get new count
        $newProducts = LeadProduct::where('lead_id', $leadId)->get();

        return response()->json([
            'lead_id' => $leadId,
            'deleted_count' => $deleted,
            'product_confirmations_processed' => count($productConfirmations),
            'new_products_created' => count($newProducts),
            'new_products' => $newProducts->map(fn($p) => [
                'id' => $p->id,
                'category' => $p->category,
            ]),
            'processing_results' => $results,
            'source' => empty($lead->product_confirmations) ? 'workflow_questions (fallback)' : 'product_confirmations',
        ]);
    }
}
