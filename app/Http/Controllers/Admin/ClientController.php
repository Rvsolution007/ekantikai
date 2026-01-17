<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $query = Client::where('admin_id', $admin->id)
            ->with(['customer', 'lead']);

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(20);

        $stats = [
            'total' => Client::where('admin_id', $admin->id)->count(),
            'this_month' => Client::where('admin_id', $admin->id)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        return view('admin.clients.index', compact('clients', 'stats'));
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['customer', 'lead']);

        // Get all leads for this client's customer
        $leads = $client->customer->leads()->with('leadStatus')->latest()->get();

        return view('admin.clients.show', compact('client', 'leads'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully!');
    }
}
