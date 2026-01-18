<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireField;
use App\Models\Admin;
use Illuminate\Http\Request;

class QuestionnaireFieldController extends Controller
{
    /**
     * Display questionnaire fields configuration
     */
    public function index(Request $request)
    {
        $adminId = $this->getAdminId();

        $fields = QuestionnaireField::where('admin_id', $adminId)
            ->orderBy('sort_order')
            ->get();

        return view('admin.workflow.fields.index', [
            'fields' => $fields,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.workflow.fields.create', [
            'fieldTypes' => $this->getFieldTypes(),
            'optionsSources' => $this->getOptionsSources(),
        ]);
    }

    /**
     * Store new field
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:50|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:100',
            'field_type' => 'required|in:text,number,select,multiselect',
            'is_required' => 'nullable',
            'is_unique_key' => 'nullable',
            'unique_key_order' => 'nullable|integer|min:1',
            'options_source' => 'required|in:manual,catalogue,dynamic',
            'options_manual' => 'nullable|array',
            'catalogue_field' => 'nullable|string|max:50',
            'validation_rules' => 'nullable|array',
            'is_active' => 'nullable',
        ]);

        $adminId = $this->getAdminId();

        // Check if field name already exists
        if (QuestionnaireField::where('admin_id', $adminId)->where('field_name', $validated['field_name'])->exists()) {
            return back()->withErrors(['field_name' => 'This field name already exists'])->withInput();
        }

        // Get max sort order
        $maxSort = QuestionnaireField::where('admin_id', $adminId)->max('sort_order') ?? 0;

        QuestionnaireField::create([
            'admin_id' => $adminId,
            'field_name' => $validated['field_name'],
            'display_name' => $validated['display_name'],
            'field_type' => $validated['field_type'],
            'is_required' => !empty($validated['is_required']),
            'sort_order' => $maxSort + 1,
            'is_unique_key' => !empty($validated['is_unique_key']),
            'unique_key_order' => $validated['unique_key_order'] ?? null,
            'options_source' => $validated['options_source'],
            'options_manual' => $validated['options_manual'] ?? null,
            'catalogue_field' => $validated['catalogue_field'] ?? null,
            'validation_rules' => $validated['validation_rules'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.workflow.fields.index')
            ->with('success', 'Field added successfully');
    }

    /**
     * Show edit form
     */
    public function edit(QuestionnaireField $field)
    {
        $this->authorizeField($field);

        return view('admin.workflow.fields.edit', [
            'field' => $field,
            'fieldTypes' => $this->getFieldTypes(),
            'optionsSources' => $this->getOptionsSources(),
        ]);
    }

    /**
     * Update field
     */
    public function update(Request $request, QuestionnaireField $field)
    {
        $this->authorizeField($field);

        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'field_type' => 'required|in:text,number,select,multiselect',
            'is_required' => 'nullable',
            'is_unique_key' => 'nullable',
            'unique_key_order' => 'nullable|integer|min:1',
            'options_source' => 'required|in:manual,catalogue,dynamic',
            'options_manual' => 'nullable|array',
            'catalogue_field' => 'nullable|string|max:50',
            'validation_rules' => 'nullable|array',
            'is_active' => 'nullable',
        ]);

        // Convert checkbox values to boolean
        $validated['is_required'] = !empty($validated['is_required']);
        $validated['is_unique_key'] = !empty($validated['is_unique_key']);
        $validated['is_active'] = !empty($validated['is_active']);

        $field->update($validated);

        return redirect()->route('admin.workflow.fields.index')
            ->with('success', 'Field updated successfully');
    }

    /**
     * Delete field
     */
    public function destroy(QuestionnaireField $field)
    {
        $this->authorizeField($field);

        $field->delete();

        return redirect()->route('admin.workflow.fields.index')
            ->with('success', 'Field deleted successfully');
    }

    /**
     * Reorder fields
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:questionnaire_fields,id',
        ]);

        $adminId = $this->getAdminId();

        foreach ($validated['order'] as $position => $id) {
            QuestionnaireField::where('id', $id)
                ->where('admin_id', $adminId)
                ->update(['sort_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle unique key status
     */
    public function toggleUniqueKey(QuestionnaireField $field)
    {
        $this->authorizeField($field);

        $field->is_unique_key = !$field->is_unique_key;

        if ($field->is_unique_key) {
            // Set unique key order
            $maxOrder = QuestionnaireField::where('admin_id', $field->admin_id)
                ->where('is_unique_key', true)
                ->max('unique_key_order') ?? 0;
            $field->unique_key_order = $maxOrder + 1;
        } else {
            $field->unique_key_order = null;
        }

        $field->save();

        return back()->with('success', 'Unique key status updated');
    }

    /**
     * Toggle unique field status (for identifying unique products like Model Number)
     */
    public function toggleUniqueField(QuestionnaireField $field)
    {
        $this->authorizeField($field);

        $field->is_unique_field = !$field->is_unique_field;
        $field->save();

        return back()->with('success', 'Unique field status updated');
    }

    /**
     * Get current tenant ID
     */
    protected function getAdminId(): int
    {
        // For now, get from admin's tenant
        // Later can be from session or authenticated user
        $admin = auth()->guard('admin')->user();
        return $admin->admin_id ?? 1;
    }

    /**
     * Authorize field belongs to tenant
     */
    protected function authorizeField(QuestionnaireField $field): void
    {
        if ($field->admin_id !== $this->getAdminId()) {
            abort(403);
        }
    }

    protected function getFieldTypes(): array
    {
        return [
            'text' => 'Text Input',
            'number' => 'Number Input',
            'select' => 'Dropdown Select',
            'multiselect' => 'Multi-Select',
        ];
    }

    protected function getOptionsSources(): array
    {
        return [
            'manual' => 'Manual Options',
            'catalogue' => 'From Catalogue',
            'dynamic' => 'Dynamic (AI)',
        ];
    }
}
