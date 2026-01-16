<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use App\Models\Lead;
use App\Models\QuestionnaireNode;
use Illuminate\Http\Request;

class LeadStatusController extends Controller
{
    /**
     * Display lead statuses with Kanban view
     */
    public function index()
    {
        $admin = auth('admin')->user();

        $statuses = LeadStatus::where('admin_id', $admin->id)
            ->ordered()
            ->withCount('leads')
            ->get();

        return view('admin.lead-status.index', compact('statuses'));
    }

    /**
     * Get Kanban board data
     */
    public function kanban()
    {
        $admin = auth('admin')->user();

        $statuses = LeadStatus::where('admin_id', $admin->id)
            ->active()
            ->ordered()
            ->get();

        // Get default status (first one)
        $defaultStatus = $statuses->first();
        $defaultStatusId = $defaultStatus ? $defaultStatus->id : null;

        // Get all open leads
        $allLeads = Lead::where('admin_id', $admin->id)
            ->where('status', 'open')
            ->with(['customer', 'leadStatus'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Group leads by status, putting leads without status into default
        $leads = collect();
        foreach ($statuses as $status) {
            $leads[$status->id] = $allLeads->filter(function ($lead) use ($status, $defaultStatusId) {
                // If lead has no status, assign to default
                if (!$lead->lead_status_id) {
                    return $status->id === $defaultStatusId;
                }
                return $lead->lead_status_id === $status->id;
            });
        }

        return view('admin.lead-status.kanban', compact('statuses', 'leads'));
    }

    /**
     * Show form for creating a new status
     */
    public function create()
    {
        $admin = auth('admin')->user();

        $questions = QuestionnaireNode::where('admin_id', $admin->id)
            ->where('node_type', 'question')
            ->with('questionnaireField')
            ->get();

        return view('admin.lead-status.create', compact('questions'));
    }

    /**
     * Store a new status
     */
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'connected_question_ids' => 'nullable|array',
            'connected_question_ids.*' => 'exists:questionnaire_nodes,id',
        ]);

        // Get max order
        $maxOrder = LeadStatus::where('admin_id', $admin->id)->max('order') ?? 0;

        LeadStatus::create([
            'admin_id' => $admin->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'order' => $maxOrder + 1,
            'connected_question_ids' => $validated['connected_question_ids'] ?? [],
        ]);

        return redirect()->route('admin.lead-status.index')
            ->with('success', 'Lead status created successfully.');
    }

    /**
     * Show form for editing a status
     */
    public function edit(LeadStatus $leadStatus)
    {
        $this->authorize('update', $leadStatus);

        $admin = auth('admin')->user();

        $questions = QuestionnaireNode::where('admin_id', $admin->id)
            ->where('node_type', 'question')
            ->with('questionnaireField')
            ->get();

        return view('admin.lead-status.edit', compact('leadStatus', 'questions'));
    }

    /**
     * Update a status
     */
    public function update(Request $request, LeadStatus $leadStatus)
    {
        $this->authorize('update', $leadStatus);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
            'connected_question_ids' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $leadStatus->update($validated);

        return redirect()->route('admin.lead-status.index')
            ->with('success', 'Lead status updated successfully.');
    }

    /**
     * Delete a status
     */
    public function destroy(LeadStatus $leadStatus)
    {
        $this->authorize('delete', $leadStatus);

        // Don't allow deletion of default status
        if ($leadStatus->is_default) {
            return back()->with('error', 'Cannot delete the default status.');
        }

        // Move leads to default status
        $defaultStatus = LeadStatus::getDefault($leadStatus->admin_id);
        Lead::where('lead_status_id', $leadStatus->id)
            ->update(['lead_status_id' => $defaultStatus?->id]);

        $leadStatus->delete();

        return redirect()->route('admin.lead-status.index')
            ->with('success', 'Lead status deleted successfully.');
    }

    /**
     * Reorder statuses (for drag-drop)
     */
    public function reorder(Request $request)
    {
        $admin = auth('admin')->user();

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:lead_statuses,id',
        ]);

        foreach ($validated['order'] as $index => $statusId) {
            LeadStatus::where('id', $statusId)
                ->where('admin_id', $admin->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Move lead to different status (for Kanban drag-drop)
     */
    public function moveLead(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'status_id' => 'required|exists:lead_statuses,id',
        ]);

        $lead = Lead::find($validated['lead_id']);
        $this->authorize('update', $lead);

        $lead->updateLeadStatus($validated['status_id']);

        return response()->json([
            'success' => true,
            'lead' => $lead->fresh()->load('customer', 'leadStatus'),
        ]);
    }

    /**
     * API: Get statuses for this admin
     */
    public function apiList()
    {
        $admin = auth('admin')->user();

        $statuses = LeadStatus::where('admin_id', $admin->id)
            ->active()
            ->ordered()
            ->get(['id', 'name', 'color', 'order']);

        return response()->json($statuses);
    }
}
