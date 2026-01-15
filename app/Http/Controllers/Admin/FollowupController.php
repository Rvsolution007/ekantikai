<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Followup;
use Illuminate\Http\Request;

class FollowupController extends Controller
{
    /**
     * Display followups listing
     */
    public function index(Request $request)
    {
        $query = Followup::with('lead.whatsappUser');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        // Filter due today
        if ($request->boolean('due_today')) {
            $query->whereDate('next_followup_at', today());
        }

        // Filter overdue
        if ($request->boolean('overdue')) {
            $query->where('next_followup_at', '<', now())
                ->where('status', 'pending');
        }

        $followups = $query->orderBy('next_followup_at', 'asc')->paginate(20);

        // Stats
        $stats = [
            'pending' => Followup::where('status', 'pending')->count(),
            'due_today' => Followup::where('status', 'pending')
                ->whereDate('next_followup_at', today())->count(),
            'overdue' => Followup::where('status', 'pending')
                ->where('next_followup_at', '<', now())->count(),
        ];

        return view('admin.followups.index', compact('followups', 'stats'));
    }

    /**
     * Mark followup as complete
     */
    public function markComplete(Request $request, Followup $followup)
    {
        $followup->markCompleted();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Marked as complete.']);
        }

        return back()->with('success', 'Followup marked as complete.');
    }

    /**
     * Reschedule followup
     */
    public function reschedule(Request $request, Followup $followup)
    {
        $request->validate([
            'hours' => 'required|integer|min:1|max:720',
        ]);

        $followup->scheduleNext($request->hours);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Rescheduled.']);
        }

        return back()->with('success', 'Followup rescheduled.');
    }
}
