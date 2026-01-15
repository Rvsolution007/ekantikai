<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientCredit;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    /**
     * Display credits listing
     */
    public function index(Request $request)
    {
        $query = ClientCredit::query();

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_number', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $credits = $query->orderBy('updated_at', 'desc')->paginate(20);

        // Stats
        $stats = [
            'total_users' => ClientCredit::count(),
            'active_users' => ClientCredit::where('is_active', true)->count(),
            'total_credits' => ClientCredit::sum('total_credit'),
            'used_credits' => ClientCredit::sum('usage_credit'),
        ];

        return view('admin.credits.index', compact('credits', 'stats'));
    }

    /**
     * Add credits to user
     */
    public function addCredits(Request $request, ClientCredit $credit)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $credit->addExtraCredit($request->amount);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Credits added.']);
        }

        return back()->with('success', 'Credits added successfully.');
    }
}
