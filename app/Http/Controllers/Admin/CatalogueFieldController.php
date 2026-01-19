<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogueField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogueFieldController extends Controller
{
    /**
     * Store a new field
     */
    public function store(Request $request)
    {
        $request->validate([
            'field_name' => 'required|string|max:100',
            'field_type' => 'required|in:text,number,select',
            'is_unique' => 'boolean',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        $fieldKey = CatalogueField::generateFieldKey($request->field_name);

        // Check if field key already exists for this tenant
        $exists = CatalogueField::where('admin_id', $adminId)
            ->where('field_key', $fieldKey)
            ->exists();

        if ($exists) {
            return back()->with('error', 'A field with this name already exists.');
        }

        // Get max sort order
        $maxOrder = CatalogueField::where('admin_id', $adminId)->max('sort_order') ?? 0;

        // Parse options for select type
        $options = null;
        if ($request->field_type === 'select' && $request->options) {
            $options = array_map('trim', explode(',', $request->options));
            $options = array_filter($options);
        }

        CatalogueField::create([
            'admin_id' => $adminId,
            'field_name' => $request->field_name,
            'field_key' => $fieldKey,
            'field_type' => $request->field_type,
            'is_unique' => $request->boolean('is_unique'),
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $maxOrder + 1,
            'options' => $options,
        ]);

        return back()->with('success', 'Field created successfully!');
    }

    /**
     * Update a field
     */
    public function update(Request $request, CatalogueField $field)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($field->admin_id !== $adminId) {
            abort(403);
        }

        $request->validate([
            'field_name' => 'required|string|max:100',
            'field_type' => 'required|in:text,number,select',
            'is_unique' => 'boolean',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ]);

        // Parse options for select type
        $options = null;
        if ($request->field_type === 'select' && $request->options) {
            $options = array_map('trim', explode(',', $request->options));
            $options = array_filter($options);
        }

        $field->update([
            'field_name' => $request->field_name,
            'field_type' => $request->field_type,
            'is_unique' => $request->boolean('is_unique'),
            'is_required' => $request->boolean('is_required'),
            'options' => $options,
        ]);

        return back()->with('success', 'Field updated successfully!');
    }

    /**
     * Delete a field
     */
    public function destroy(CatalogueField $field)
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if ($field->admin_id !== $adminId) {
            abort(403);
        }

        $field->delete();

        return back()->with('success', 'Field deleted successfully!');
    }

    /**
     * Reorder fields
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        foreach ($request->order as $index => $fieldId) {
            CatalogueField::where('id', $fieldId)
                ->where('admin_id', $adminId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Sync fields from Questionnaire (Workflow) Product Questions
     */
    public function syncFromQuestionnaire()
    {
        $admin = auth()->guard('admin')->user();
        $adminId = $admin->admin_id ?? $admin->id;

        if (!$adminId) {
            return back()->with('error', 'Tenant not found.');
        }

        // Get all questionnaire fields for this tenant
        $questionnaireFields = \App\Models\ProductQuestion::where('admin_id', $adminId)
            ->where('is_active', true)
            ->ordered()
            ->get();

        if ($questionnaireFields->isEmpty()) {
            return back()->with('error', 'No workflow fields found. Please create product questions in Workflow first.');
        }

        $synced = 0;
        $skipped = 0;
        $maxOrder = CatalogueField::where('admin_id', $adminId)->max('sort_order') ?? 0;

        foreach ($questionnaireFields as $qField) {
            $fieldKey = CatalogueField::generateFieldKey($qField->field_name);

            // Check if already exists
            $exists = CatalogueField::where('admin_id', $adminId)
                ->where('field_key', $fieldKey)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Map questionnaire field type to catalogue field type
            $fieldType = 'text';
            if ($qField->field_type === 'number') {
                $fieldType = 'number';
            } elseif ($qField->options_source === 'manual' && !empty($qField->options_manual)) {
                $fieldType = 'select';
            }

            // Create catalogue field
            CatalogueField::create([
                'admin_id' => $adminId,
                'field_name' => $qField->display_name ?: $qField->field_name,
                'field_key' => $fieldKey,
                'field_type' => $fieldType,
                'is_unique' => $qField->is_unique_field ?? false,
                'is_required' => $qField->is_required ?? false,
                'sort_order' => ++$maxOrder,
                'options' => $qField->options_manual ?? null,
            ]);

            $synced++;
        }

        $message = "Synced {$synced} fields from Workflow.";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} existing fields.";
        }

        return back()->with('success', $message);
    }
}
