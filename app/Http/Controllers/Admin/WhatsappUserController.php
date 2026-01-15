<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappUser;
use Illuminate\Http\Request;

class WhatsappUserController extends Controller
{
    /**
     * Display users listing
     */
    public function index(Request $request)
    {
        $query = WhatsappUser::with('latestLead');

        // Filter by bot status
        if ($request->filled('bot_status')) {
            $query->where('bot_enabled', $request->bot_status === 'enabled');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $users = $query->latest('last_activity_at')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show(WhatsappUser $user)
    {
        $user->load('leads.products', 'chats');

        $chats = $user->chats()->orderBy('created_at', 'desc')->take(100)->get()->reverse();

        return view('admin.users.show', compact('user', 'chats'));
    }

    /**
     * Toggle bot enabled/disabled
     */
    public function toggleBot(Request $request, WhatsappUser $user)
    {
        $user->update([
            'bot_enabled' => !$user->bot_enabled,
            'pause_reason' => !$user->bot_enabled ? null : 'manually_disabled',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'bot_enabled' => $user->bot_enabled,
                'message' => $user->bot_enabled ? 'Bot enabled' : 'Bot disabled',
            ]);
        }

        return back()->with('success', 'Bot status updated.');
    }

    /**
     * Update user details
     */
    public function update(Request $request, WhatsappUser $user)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'conversation_mode' => 'nullable|in:ai_bot,human_only,hybrid',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }
}
