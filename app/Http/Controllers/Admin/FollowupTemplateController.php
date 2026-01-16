<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowupTemplate;
use App\Models\QuestionnaireField;
use Illuminate\Http\Request;

class FollowupTemplateController extends Controller
{
    /**
     * Display all followup templates
     */
    public function index()
    {
        $admin = auth('admin')->user();

        $templates = FollowupTemplate::where('admin_id', $admin->id)
            ->ordered()
            ->get();

        return view('admin.followup-templates.index', compact('templates'));
    }

    /**
     * Show form for creating a new template
     */
    public function create()
    {
        $admin = auth('admin')->user();
        $availableFields = FollowupTemplate::getAvailableFields($admin->id);

        return view('admin.followup-templates.create', compact('availableFields'));
    }

    /**
     * Store a new template
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message_template' => 'required|string|max:2000',
            'delay_minutes' => 'required|integer|min:1|max:10080', // Max 7 days
        ]);

        // Get max order
        $maxOrder = FollowupTemplate::where('admin_id', $admin->id)->max('order') ?? 0;

        FollowupTemplate::create([
            'admin_id' => $admin->id,
            'name' => $validated['name'],
            'message_template' => $validated['message_template'],
            'delay_minutes' => $validated['delay_minutes'],
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.followup-templates.index')
            ->with('success', 'Followup template created successfully.');
    }

    /**
     * Show form for editing a template
     */
    public function edit(FollowupTemplate $followupTemplate)
    {
        $this->authorize('update', $followupTemplate);

        $admin = auth('admin')->user();
        $availableFields = FollowupTemplate::getAvailableFields($admin->id);

        return view('admin.followup-templates.edit', compact('followupTemplate', 'availableFields'));
    }

    /**
     * Update a template
     */
    public function update(Request $request, FollowupTemplate $followupTemplate)
    {
        $this->authorize('update', $followupTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message_template' => 'required|string|max:2000',
            'delay_minutes' => 'required|integer|min:1|max:10080',
            'is_active' => 'boolean',
        ]);

        $followupTemplate->update($validated);

        return redirect()->route('admin.followup-templates.index')
            ->with('success', 'Followup template updated successfully.');
    }

    /**
     * Delete a template
     */
    public function destroy(FollowupTemplate $followupTemplate)
    {
        $this->authorize('delete', $followupTemplate);

        $followupTemplate->delete();

        return redirect()->route('admin.followup-templates.index')
            ->with('success', 'Followup template deleted successfully.');
    }

    /**
     * Toggle template active status
     */
    public function toggle(FollowupTemplate $followupTemplate)
    {
        $this->authorize('update', $followupTemplate);

        $followupTemplate->update([
            'is_active' => !$followupTemplate->is_active,
        ]);

        return back()->with('success', 'Template status updated.');
    }

    /**
     * Preview template with sample data
     */
    public function preview(FollowupTemplate $followupTemplate)
    {
        $this->authorize('view', $followupTemplate);

        return response()->json([
            'preview' => $followupTemplate->preview(),
        ]);
    }

    /**
     * Reorder templates (for drag-drop)
     */
    public function reorder(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:followup_templates,id',
        ]);

        foreach ($validated['order'] as $index => $templateId) {
            FollowupTemplate::where('id', $templateId)
                ->where('admin_id', $admin->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get available placeholder fields
     */
    public function getFields()
    {
        $admin = auth('admin')->user();
        $fields = FollowupTemplate::getAvailableFields($admin->id);

        return response()->json($fields);
    }

    /**
     * Duplicate a template
     */
    public function duplicate(FollowupTemplate $followupTemplate)
    {
        $this->authorize('view', $followupTemplate);

        $admin = auth('admin')->user();
        $maxOrder = FollowupTemplate::where('admin_id', $admin->id)->max('order') ?? 0;

        $newTemplate = $followupTemplate->replicate();
        $newTemplate->name = $followupTemplate->name . ' (Copy)';
        $newTemplate->order = $maxOrder + 1;
        $newTemplate->save();

        return redirect()->route('admin.followup-templates.index')
            ->with('success', 'Template duplicated successfully.');
    }
}
