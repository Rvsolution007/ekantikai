<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogue;
use App\Models\CatalogueField;
use App\Models\ProductQuestion;
use Illuminate\Http\Request;

class DataValidationController extends Controller
{
    /**
     * Display data validation dashboard
     */
    public function index()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        // Get catalog statistics
        $catalogStats = $this->getCatalogStats($adminId);

        // Get models grouped by category
        $modelsByCategory = $this->getModelsByCategory($adminId);

        // Get field mappings
        $fieldMappings = $this->getFieldMappings($adminId);

        // Get product questions
        $productQuestions = ProductQuestion::where('admin_id', $adminId)
            ->orderBy('sort_order')
            ->get();

        return view('admin.workflow.validation.index', compact(
            'catalogStats',
            'modelsByCategory',
            'fieldMappings',
            'productQuestions'
        ));
    }

    /**
     * Get catalog statistics
     */
    private function getCatalogStats($adminId)
    {
        $totalProducts = Catalogue::where('admin_id', $adminId)->count();
        $activeProducts = Catalogue::where('admin_id', $adminId)->where('is_active', true)->count();
        $inactiveProducts = $totalProducts - $activeProducts;

        // Get categories count
        $categories = Catalogue::where('admin_id', $adminId)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.product_category')) as category")
            ->distinct()
            ->pluck('category')
            ->filter()
            ->count();

        return [
            'total' => $totalProducts,
            'active' => $activeProducts,
            'inactive' => $inactiveProducts,
            'categories' => $categories,
        ];
    }

    /**
     * Get models grouped by category
     */
    private function getModelsByCategory($adminId)
    {
        $products = Catalogue::where('admin_id', $adminId)
            ->where('is_active', true)
            ->get();

        $grouped = [];

        foreach ($products as $product) {
            $data = $product->data;
            $category = $data['product_category'] ?? $data['category'] ?? 'Uncategorized';
            $model = $data['model_code'] ?? $data['model'] ?? 'Unknown';

            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            if (!in_array($model, $grouped[$category])) {
                $grouped[$category][] = $model;
            }
        }

        // Sort models in each category
        foreach ($grouped as $category => $models) {
            sort($grouped[$category]);
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * Get field mappings (Catalogue Fields to Product Questions)
     */
    private function getFieldMappings($adminId)
    {
        $catalogueFields = CatalogueField::where('admin_id', $adminId)
            ->orderBy('sort_order')
            ->get();

        $productQuestions = ProductQuestion::where('admin_id', $adminId)
            ->orderBy('sort_order')
            ->get();

        $mappings = [];

        foreach ($catalogueFields as $field) {
            $matchedQuestion = $productQuestions->first(function ($q) use ($field) {
                return strtolower($q->field_name) === strtolower($field->field_key) ||
                    strtolower($q->catalogue_field) === strtolower($field->field_key);
            });

            $mappings[] = [
                'catalogue_field' => $field->field_name,
                'catalogue_key' => $field->field_key,
                'is_unique' => $field->is_unique,
                'question_field' => $matchedQuestion ? $matchedQuestion->field_name : null,
                'question_display' => $matchedQuestion ? $matchedQuestion->display_name : null,
                'is_mapped' => $matchedQuestion !== null,
            ];
        }

        return $mappings;
    }

    /**
     * AJAX: Lookup model code
     */
    public function lookupModel(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $modelCode = $request->input('model_code');

        if (empty($modelCode)) {
            return response()->json(['error' => 'Model code is required'], 400);
        }

        // Search in JSON data
        $products = Catalogue::where('admin_id', $adminId)
            ->where(function ($query) use ($modelCode) {
                $query->whereRaw("JSON_EXTRACT(data, '$.model_code') LIKE ?", ["%{$modelCode}%"])
                    ->orWhereRaw("JSON_EXTRACT(data, '$.model') LIKE ?", ["%{$modelCode}%"]);
            })
            ->get();

        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'data' => $product->data,
                'is_active' => $product->is_active,
                'image_url' => $product->image_url,
            ];
        }

        return response()->json([
            'count' => count($results),
            'products' => $results,
        ]);
    }

    /**
     * AJAX: Get category products
     */
    public function getCategoryProducts(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $category = $request->input('category');

        if (empty($category)) {
            return response()->json(['error' => 'Category is required'], 400);
        }

        $products = Catalogue::where('admin_id', $adminId)
            ->where('is_active', true)
            ->whereRaw("JSON_EXTRACT(data, '$.product_category') LIKE ?", ["%{$category}%"])
            ->get();

        $results = [];
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'data' => $product->data,
            ];
        }

        return response()->json([
            'count' => count($results),
            'products' => $results,
        ]);
    }

    /**
     * Validate all catalog data
     */
    public function validateAll()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $products = Catalogue::where('admin_id', $adminId)->get();
        $fields = CatalogueField::where('admin_id', $adminId)->get();

        $issues = [];

        foreach ($products as $product) {
            $productIssues = [];

            // Check required fields
            foreach ($fields as $field) {
                if ($field->is_required) {
                    $value = $product->data[$field->field_key] ?? null;
                    if (empty($value)) {
                        $productIssues[] = "Missing required field: {$field->field_name}";
                    }
                }
            }

            // Check for empty data
            if (empty($product->data) || count(array_filter($product->data)) === 0) {
                $productIssues[] = "Product has no data";
            }

            if (!empty($productIssues)) {
                $issues[] = [
                    'product_id' => $product->id,
                    'data' => $product->data,
                    'issues' => $productIssues,
                ];
            }
        }

        return response()->json([
            'total_products' => $products->count(),
            'issues_count' => count($issues),
            'issues' => $issues,
        ]);
    }
}
