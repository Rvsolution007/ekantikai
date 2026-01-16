<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalogue;
use App\Models\CatalogueField;
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    /**
     * Display catalogue listing with tabs for fields and products
     */
    public function index(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return redirect()->route('admin.dashboard')->with('error', 'Tenant not configured.');
        }

        // Get fields for this tenant
        $fields = CatalogueField::forTenant($adminId)->ordered()->get();
        $fieldTypes = CatalogueField::getFieldTypes();

        // Get active tab
        $activeTab = $request->get('tab', $fields->isEmpty() ? 'fields' : 'products');

        // Get products with dynamic data
        $query = Catalogue::where('admin_id', $adminId);

        // Search in JSON data
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_SEARCH(data, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(20);

        // Get import errors from session
        $importErrors = session('import_errors', []);

        return view('admin.catalogue.index', compact(
            'fields',
            'fieldTypes',
            'products',
            'activeTab',
            'importErrors'
        ));
    }

    /**
     * Store new product with dynamic fields
     */
    public function store(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        // Get fields for validation
        $fields = CatalogueField::forTenant($adminId)->ordered()->get();

        $data = [];
        $errors = [];

        foreach ($fields as $field) {
            $value = $request->input("data.{$field->field_key}", '');
            $data[$field->field_key] = $value;

            // Validate
            $fieldErrors = $field->validateValue($value);
            if (!empty($fieldErrors)) {
                $errors = array_merge($errors, $fieldErrors);
            }

            // Check unique
            if ($field->is_unique && !empty($value)) {
                $exists = Catalogue::where('admin_id', $adminId)
                    ->whereRaw("JSON_EXTRACT(data, ?) = ?", ['$.' . $field->field_key, $value])
                    ->exists();

                if ($exists) {
                    $errors[] = "{$field->field_name} '{$value}' already exists.";
                }
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        Catalogue::create([
            'admin_id' => $adminId,
            'data' => $data,
            'is_active' => true,
        ]);

        return back()->with('success', 'Product added successfully!');
    }

    /**
     * Update product
     */
    public function update(Request $request, Catalogue $catalogue)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($catalogue->admin_id !== $adminId) {
            abort(403);
        }

        $fields = CatalogueField::forTenant($adminId)->ordered()->get();

        $data = [];
        $errors = [];

        foreach ($fields as $field) {
            $value = $request->input("data.{$field->field_key}", '');
            $data[$field->field_key] = $value;

            // Validate
            $fieldErrors = $field->validateValue($value);
            if (!empty($fieldErrors)) {
                $errors = array_merge($errors, $fieldErrors);
            }

            // Check unique (exclude current record)
            if ($field->is_unique && !empty($value)) {
                $exists = Catalogue::where('admin_id', $adminId)
                    ->where('id', '!=', $catalogue->id)
                    ->whereRaw("JSON_EXTRACT(data, ?) = ?", ['$.' . $field->field_key, $value])
                    ->exists();

                if ($exists) {
                    $errors[] = "{$field->field_name} '{$value}' already exists.";
                }
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $catalogue->update([
            'data' => $data,
        ]);

        return back()->with('success', 'Product updated successfully!');
    }

    /**
     * Delete product
     */
    public function destroy(Catalogue $catalogue)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($catalogue->admin_id !== $adminId) {
            abort(403);
        }

        $catalogue->delete();

        return back()->with('success', 'Product deleted successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(Request $request, Catalogue $catalogue)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($catalogue->admin_id !== $adminId) {
            abort(403);
        }

        $catalogue->update(['is_active' => !$catalogue->is_active]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $catalogue->is_active,
            ]);
        }

        return back()->with('success', 'Status updated.');
    }

    /**
     * Delete all products (clear catalogue)
     */
    public function clearAll()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        Catalogue::where('admin_id', $adminId)->delete();

        return back()->with('success', 'All products deleted successfully!');
    }

    /**
     * Upload image for a product
     */
    public function uploadImage(Request $request, Catalogue $catalogue)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($catalogue->admin_id !== $adminId) {
            abort(403);
        }

        // Check if admin has product images enabled
        if (!$admin->send_product_images) {
            return back()->with('error', 'Product images feature is not enabled for your account. Contact Super Admin.');
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        // Store image
        $path = $request->file('image')->store('catalogue/' . $adminId, 'public');

        // Update catalogue with image URL
        $catalogue->update([
            'image_url' => '/storage/' . $path
        ]);

        return back()->with('success', 'Product image uploaded successfully!');
    }
}
