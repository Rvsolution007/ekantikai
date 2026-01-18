<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Customer;
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

    /**
     * Show form to create a new client
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a new client
     */
    public function store(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }

        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['phone_number' => $phone, 'admin_id' => $admin->id],
            [
                'name' => $validated['name'],
                'admin_id' => $admin->id,
            ]
        );

        // Update customer global fields with client info
        $globalFields = $customer->global_fields ?? [];
        if (!empty($validated['city']))
            $globalFields['city'] = $validated['city'];
        if (!empty($validated['name']))
            $globalFields['name'] = $validated['name'];
        $customer->global_fields = $globalFields;
        $customer->name = $validated['name'];
        $customer->save();

        // Create client
        $client = Client::create([
            'admin_id' => $admin->id,
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'phone' => $phone,
            'business_name' => $validated['business_name'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client created successfully!');
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
