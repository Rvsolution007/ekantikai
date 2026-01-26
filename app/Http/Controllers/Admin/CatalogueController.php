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

        // Search in all fields (product data)
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $allFieldKeys = $fields->pluck('field_key')->toArray();

            if (!empty($allFieldKeys)) {
                $query->where(function ($q) use ($search, $allFieldKeys) {
                    foreach ($allFieldKeys as $fieldKey) {
                        // Use LOWER for case-insensitive search - quote field key for JSON path
                        $jsonPath = '$."' . $fieldKey . '"';
                        $q->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data, ?))) LIKE ?", [$jsonPath, "%{$search}%"]);
                    }
                });
            }
        }

        $products = $query->orderBy('id', 'desc')->paginate(50);

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
                $jsonPath = '$."' . $field->field_key . '"';
                $exists = Catalogue::where('admin_id', $adminId)
                    ->whereRaw("JSON_EXTRACT(data, ?) = ?", [$jsonPath, $value])
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
                $jsonPath = '$."' . $field->field_key . '"';
                $exists = Catalogue::where('admin_id', $adminId)
                    ->where('id', '!=', $catalogue->id)
                    ->whereRaw("JSON_EXTRACT(data, ?) = ?", [$jsonPath, $value])
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
     * Bulk delete multiple products
     */
    public function bulkDelete(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $ids = $request->input('ids', []);

        // Handle JSON string from form
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }

        if (empty($ids)) {
            return back()->with('error', 'No products selected');
        }

        $deleted = Catalogue::where('admin_id', $adminId)
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('success', $deleted . ' products deleted successfully!');
    }

    /**
     * AJAX search for smooth experience
     */
    public function ajaxSearch(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $fields = CatalogueField::forTenant($adminId)->ordered()->get();
        $query = Catalogue::where('admin_id', $adminId);

        // Search only in unique fields
        if ($request->filled('search')) {
            $search = $request->search;
            $uniqueFields = $fields->where('is_unique', true)->pluck('field_key')->toArray();

            if (!empty($uniqueFields)) {
                $query->where(function ($q) use ($search, $uniqueFields) {
                    foreach ($uniqueFields as $fieldKey) {
                        // Quote field key for JSON path
                        $jsonPath = '$."' . $fieldKey . '"';
                        $q->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, ?)) LIKE ?", [$jsonPath, "%{$search}%"]);
                    }
                });
            }
        }

        $products = $query->orderBy('id', 'desc')->paginate(50);

        return response()->json([
            'html' => view('admin.catalogue.partials.products-table', compact('products', 'fields'))->render(),
            'total' => $products->total(),
            'pagination' => $products->hasPages() ? $products->withQueryString()->links()->render() : '',
        ]);
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

    /**
     * Import products from Excel using PhpSpreadsheet directly
     */
    public function import(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240', // CSV only, 10MB max
        ]);

        try {
            $file = $request->file('file');

            // Read file content and handle encoding
            $content = file_get_contents($file->getPathname());

            // Remove UTF-8 BOM if present
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            // Try to detect and convert encoding
            $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            // Write to temp file
            $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
            file_put_contents($tempFile, $content);

            // Read cleaned CSV file
            $handle = fopen($tempFile, 'r');
            if (!$handle) {
                unlink($tempFile);
                return back()->with('error', 'Could not open file.');
            }

            // Get catalogue fields for this admin
            $catalogueFields = CatalogueField::forTenant($adminId)->ordered()->get();

            if ($catalogueFields->isEmpty()) {
                fclose($handle);
                return back()->with('error', 'Please configure catalogue fields first.');
            }

            // Read header row
            $headerRow = fgetcsv($handle);
            if (!$headerRow) {
                fclose($handle);
                return back()->with('error', 'CSV file is empty or invalid.');
            }

            // Clean and lowercase headers
            $headers = array_map(function ($h) {
                return strtolower(trim($h ?? ''));
            }, $headerRow);

            // Build field mapping (CSV column index => field_key)
            $fieldMap = [];
            foreach ($catalogueFields as $field) {
                $fieldKeyLower = strtolower($field->field_key);
                $fieldNameLower = strtolower($field->field_name);
                $fieldKeySpaced = strtolower(str_replace('_', ' ', $field->field_key));

                // Find matching column in CSV headers
                foreach ($headers as $colIndex => $header) {
                    if ($header === $fieldKeyLower || $header === $fieldNameLower || $header === $fieldKeySpaced) {
                        $fieldMap[$colIndex] = $field->field_key;
                        break;
                    }
                }
            }

            if (empty($fieldMap)) {
                fclose($handle);
                return back()->with('error', 'No matching columns found in CSV. Check column headers match your field names.');
            }

            $successCount = 0;
            $skipCount = 0;
            $errors = [];
            $rowNumber = 1; // Header was row 1

            // Process data rows
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Build data array
                $data = [];
                $hasData = false;

                foreach ($fieldMap as $colIndex => $fieldKey) {
                    $value = isset($row[$colIndex]) ? trim($row[$colIndex] ?? '') : '';

                    // Sanitize UTF-8 - remove or replace invalid characters
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value); // Remove control characters

                    // If still not valid UTF-8, try to convert from Windows encoding
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
                    }

                    $data[$fieldKey] = $value;

                    if (!empty($value)) {
                        $hasData = true;
                    }
                }

                // Skip empty rows - but log it
                if (!$hasData) {
                    $errors[] = "Row {$rowNumber}: Empty row (no data found)";
                    $skipCount++;
                    continue;
                }

                // Validate uniqueness
                $validationError = null;
                foreach ($catalogueFields as $field) {
                    if ($field->is_unique && !empty($data[$field->field_key])) {
                        $jsonPath = '$."' . $field->field_key . '"';
                        $exists = Catalogue::where('admin_id', $adminId)
                            ->whereRaw("JSON_EXTRACT(data, ?) = ?", [$jsonPath, $data[$field->field_key]])
                            ->exists();

                        if ($exists) {
                            $validationError = "Row {$rowNumber}: {$field->field_name} '{$data[$field->field_key]}' already exists.";
                            break;
                        }
                    }
                }

                if ($validationError) {
                    $errors[] = $validationError;
                    $skipCount++;
                    continue;
                }

                // Create catalogue entry with error handling
                try {
                    Catalogue::create([
                        'admin_id' => $adminId,
                        'data' => $data,
                        'is_active' => true,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: Database error - " . $e->getMessage();
                    $skipCount++;
                }
            }

            fclose($handle);

            // Clean up temp file
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} products.";
                if ($skipCount > 0) {
                    $message .= " {$skipCount} rows skipped.";
                }

                return back()
                    ->with('success', $message)
                    ->with('import_errors', $errors);
            } else {
                return back()
                    ->with('error', 'No products imported.')
                    ->with('import_errors', $errors);
            }

        } catch (\Exception $e) {
            // Clean up temp file on error
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV template with demo data
     */
    public function downloadTemplate()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        $fields = CatalogueField::forTenant($adminId)->ordered()->get();

        if ($fields->isEmpty()) {
            return back()->with('error', 'Please configure catalogue fields first.');
        }

        // Get field keys to understand the structure
        $fieldKeys = $fields->pluck('field_key')->toArray();
        $headers = $fields->pluck('field_name')->toArray();

        // Demo data based on common catalogue structure
        $demoRows = [];

        // Check if we have standard fields and add appropriate demo data
        $hasCategory = in_array('product_category', $fieldKeys) || in_array('category', $fieldKeys);
        $hasModel = in_array('model_code', $fieldKeys) || in_array('model', $fieldKeys);
        $hasSize = in_array('size', $fieldKeys) || in_array('sizes', $fieldKeys);
        $hasFinish = in_array('finish', $fieldKeys) || in_array('finish_color', $fieldKeys) || in_array('color', $fieldKeys);

        if ($hasCategory && $hasModel) {
            // Add realistic demo data for different categories
            $demoData = [
                // Profile handles
                ['Product Category' => 'Profile handles', 'Model Code' => '029', 'Size' => '6inch,8inch,10inch,12inch', 'Finish/Color' => 'Silver,Gold,Black', 'Material' => 'Aluminium', 'Pack Per Size' => '10 pcs/box'],
                ['Product Category' => 'Profile handles', 'Model Code' => '034 BS', 'Size' => '6inch,8inch,10inch,14inch,16inch', 'Finish/Color' => 'Chrome,Matt Black', 'Material' => 'Aluminium', 'Pack Per Size' => '10 pcs/box'],
                ['Product Category' => 'Profile handles', 'Model Code' => '035 BS', 'Size' => '8inch,10inch,12inch', 'Finish/Color' => 'Gold,Champagne', 'Material' => 'Aluminium', 'Pack Per Size' => '10 pcs/box'],
                // Wardrobe handles
                ['Product Category' => 'Wardrobe handles', 'Model Code' => '9045', 'Size' => '160mm,224mm,288mm', 'Finish/Color' => 'Black,Chrome,Gold', 'Material' => 'Zinc Alloy', 'Pack Per Size' => '10 pcs/box'],
                ['Product Category' => 'Wardrobe handles', 'Model Code' => '9050', 'Size' => '200mm,300mm', 'Finish/Color' => 'Matt Black,Rose Gold', 'Material' => 'Zinc Alloy', 'Pack Per Size' => '10 pcs/box'],
                // Cabinet handles
                ['Product Category' => 'Cabinet handles', 'Model Code' => '0012', 'Size' => '96mm,128mm', 'Finish/Color' => 'Chrome,Gold', 'Material' => 'Zinc Alloy', 'Pack Per Size' => '10 pcs/box'],
                // Knob handles
                ['Product Category' => 'Knob handles', 'Model Code' => '401', 'Size' => '25mm,30mm', 'Finish/Color' => 'Gold,Antique', 'Material' => 'Brass', 'Pack Per Size' => '20 pcs/box'],
                // Main door handles
                ['Product Category' => 'Main door handles', 'Model Code' => '95', 'Size' => '10inch,12inch', 'Finish/Color' => 'Gold PVD,Black PVD', 'Material' => 'Stainless Steel', 'Pack Per Size' => '5 pairs/box'],
            ];

            // Map demo data to actual field structure
            foreach ($demoData as $demo) {
                $row = [];
                foreach ($fields as $field) {
                    $value = '';
                    $fieldNameLower = strtolower($field->field_name);
                    $fieldKeyLower = strtolower($field->field_key);

                    // Match demo data to field
                    foreach ($demo as $demoKey => $demoValue) {
                        $demoKeyLower = strtolower($demoKey);
                        if (
                            $fieldNameLower === $demoKeyLower ||
                            str_contains($fieldKeyLower, str_replace(' ', '_', $demoKeyLower)) ||
                            str_contains($demoKeyLower, str_replace('_', ' ', $fieldKeyLower))
                        ) {
                            $value = $demoValue;
                            break;
                        }
                    }

                    // If no match found, use generic sample
                    if (empty($value)) {
                        switch ($field->field_type) {
                            case 'number':
                                $value = '100';
                                break;
                            case 'select':
                                $options = $field->options ?? [];
                                $value = !empty($options) ? $options[0] : '';
                                break;
                            default:
                                $value = '';
                        }
                    }

                    $row[] = $value;
                }
                $demoRows[] = $row;
            }
        } else {
            // Generic demo data based on field types
            for ($i = 1; $i <= 3; $i++) {
                $row = [];
                foreach ($fields as $field) {
                    switch ($field->field_type) {
                        case 'text':
                            $row[] = "Sample {$field->field_name} {$i}";
                            break;
                        case 'number':
                            $row[] = (string) (100 * $i);
                            break;
                        case 'select':
                            $options = $field->options ?? [];
                            $row[] = !empty($options) ? $options[0] : 'Option1';
                            break;
                        default:
                            $row[] = "Value {$i}";
                    }
                }
                $demoRows[] = $row;
            }
        }

        // Generate CSV with BOM for Excel compatibility
        $filename = 'catalogue_template_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Add UTF-8 BOM for Excel
        fwrite($handle, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($handle, $headers);

        // Write demo data rows
        foreach ($demoRows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

