<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display leads listing
     */
    public function index(Request $request)
    {
        $adminId = auth('admin')->id();

        $query = Lead::with(['whatsappUser', 'customer', 'assignedAdmin'])
            ->where('admin_id', $adminId);

        // Filter by stage
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by quality
        if ($request->filled('quality')) {
            $query->where('lead_quality', $request->quality);
        }

        // Filter by assigned admin
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by user name or number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('whatsappUser', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%");
                })->orWhereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->latest()->paginate(20);
        $admins = SuperAdmin::where('is_active', true)->get();

        // Stats (filtered by admin_id)
        $stats = [
            'total' => Lead::where('admin_id', $adminId)->count(),
            'new' => Lead::where('admin_id', $adminId)->where('stage', 'New Lead')->count(),
            'qualified' => Lead::where('admin_id', $adminId)->where('stage', 'Qualified')->count(),
            'confirmed' => Lead::where('admin_id', $adminId)->where('stage', 'Confirm')->count(),
            'lost' => Lead::where('admin_id', $adminId)->where('stage', 'Lose')->count(),
        ];

        return view('admin.leads.index', compact('leads', 'admins', 'stats'));
    }

    /**
     * Show lead details
     */
    public function show(Lead $lead)
    {
        $lead->load('whatsappUser', 'customer', 'products', 'followups', 'assignedAdmin');

        // Get chat history - try customer first, then whatsappUser
        $chats = collect();
        if ($lead->customer) {
            // Get chats from chat_messages table via customer
            $chats = \DB::table('chat_messages')
                ->where('customer_id', $lead->customer_id)
                ->orderBy('created_at', 'asc')
                ->get();
        } elseif ($lead->whatsappUser) {
            $chats = $lead->whatsappUser->chats()->orderBy('created_at', 'asc')->get();
        }

        return view('admin.leads.show', compact('lead', 'chats'));
    }

    /**
     * Update lead stage
     */
    public function updateStage(Request $request, Lead $lead)
    {
        $request->validate([
            'stage' => 'required|in:New Lead,Qualified,Confirm,Lose',
        ]);

        $lead->updateStage($request->stage);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead stage updated.']);
        }

        return back()->with('success', 'Lead stage updated successfully.');
    }

    /**
     * Assign lead to admin
     */
    public function assign(Request $request, Lead $lead)
    {
        $request->validate([
            'admin_id' => 'nullable|exists:super_admins,id',
        ]);

        $lead->update(['assigned_to' => $request->admin_id]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead assigned.']);
        }

        return back()->with('success', 'Lead assigned successfully.');
    }

    /**
     * Export leads to CSV
     */
    public function export(Request $request)
    {
        $leads = Lead::with('whatsappUser', 'products')->get();

        $filename = 'leads_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Phone Number',
                'Name',
                'City',
                'Stage',
                'Quality',
                'Purpose',
                'Products',
                'Created At',
                'Confirmed At'
            ]);

            foreach ($leads as $lead) {
                $products = $lead->products->map(function ($p) {
                    return "{$p->product} - {$p->model}";
                })->implode('; ');

                fputcsv($file, [
                    $lead->id,
                    $lead->whatsappUser->number ?? '',
                    $lead->whatsappUser->name ?? '',
                    $lead->whatsappUser->city ?? '',
                    $lead->stage,
                    $lead->lead_quality,
                    $lead->purpose_of_purchase ?? '',
                    $products,
                    $lead->created_at->format('Y-m-d H:i'),
                    $lead->confirmed_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update lead notes
     */
    public function update(Request $request, Lead $lead)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $lead->update(['notes' => $request->notes]);

        return back()->with('success', 'Notes updated successfully.');
    }
}
