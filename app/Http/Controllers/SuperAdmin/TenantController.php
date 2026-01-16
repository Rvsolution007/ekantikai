<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::with('credits');

        // Filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('company_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->plan) {
            $query->where('subscription_plan', $request->plan);
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $tenants = $query->latest()->paginate(15);

        $stats = [
            'total' => Admin::count(),
            'active' => Admin::where('is_active', true)->count(),
            'trial' => Admin::where('subscription_plan', 'free')->count(),
            'paid' => Admin::whereIn('subscription_plan', ['basic', 'pro', 'enterprise'])->count(),
        ];

        return view('superadmin.tenants.index', compact('tenants', 'stats'));
    }

    public function create()
    {
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'subscription_plan' => 'required|in:free,basic,pro,enterprise',
            'initial_credits' => 'nullable|numeric|min:0',
            'admin_email' => 'required|email|unique:super_admins,email',
            'admin_password' => 'required|min:6',
        ]);

        // Create Tenant
        $tenant = Admin::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'subscription_plan' => $request->subscription_plan,
            'trial_ends_at' => $request->subscription_plan === 'free' ? now()->addDays(14) : null,
            'is_active' => true,
        ]);

        // Add initial credits if specified
        if ($request->initial_credits && $tenant->credits) {
            $tenant->credits->addCredits($request->initial_credits);
        }

        // Create Tenant Admin
        SuperAdmin::create([
            'admin_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role' => 'admin',
            'is_super_admin' => false,
        ]);

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Client created successfully!');
    }

    public function show(Admin $admin)
    {
        $admin->load(['credits', 'payments', 'admins', 'aiAgents', 'workflows']);

        $stats = [
            'total_leads' => $admin->leads()->count(),
            'total_chats' => $admin->chats()->count(),
            'ai_agents' => $admin->aiAgents()->count(),
            'workflows' => $admin->workflows()->count(),
        ];

        return view('superadmin.tenants.show', compact('admin', 'stats'));
    }

    public function edit(Admin $admin)
    {
        return view('superadmin.tenants.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'domain' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'subscription_plan' => 'required|in:free,basic,pro,enterprise',
            'status' => 'required|in:active,trial,suspended,inactive',
            'subscription_ends_at' => 'nullable|date',
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'domain' => $request->domain,
            'address' => $request->address,
            'subscription_plan' => $request->subscription_plan,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
            'subscription_ends_at' => $request->subscription_ends_at,
        ]);

        // Update WhatsApp settings if provided
        if ($request->whatsapp_api_url || $request->whatsapp_api_key || $request->whatsapp_instance) {
            $settings = $admin->settings ?? [];
            $settings['whatsapp_api_url'] = $request->whatsapp_api_url;
            $settings['whatsapp_api_key'] = $request->whatsapp_api_key;
            $settings['whatsapp_instance'] = $request->whatsapp_instance;
            $admin->update(['settings' => $settings]);
        }

        return redirect()->route('superadmin.tenants.show', $admin)
            ->with('success', 'Client updated successfully!');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('success', 'Client deleted successfully!');
    }

    public function addCredits(Request $request, Admin $admin)
    {
        $request->validate([
            'credits' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $credits = $request->credits;

        if ($admin->credits) {
            $admin->credits->addCredits($credits);
        }

        // Create payment record
        $admin->payments()->create([
            'amount' => 0, // Manual credit addition
            'credits_added' => $credits,
            'payment_method' => 'manual',
            'status' => 'success',
            'notes' => $request->reason ?? 'Manual credit addition by Super Admin',
            'processed_by' => auth()->guard('admin')->id(),
        ]);

        return back()->with('success', "Added {$credits} credits successfully!");
    }

    public function toggleStatus(Admin $admin)
    {
        $admin->update(['is_active' => !$admin->is_active]);

        $status = $admin->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Client {$status} successfully!");
    }

    public function toggleProductImages(Admin $admin)
    {
        $admin->update(['send_product_images' => !$admin->send_product_images]);

        $status = $admin->send_product_images ? 'enabled' : 'disabled';
        return back()->with('success', "Product images {$status} for {$admin->name}!");
    }

    public function resetPassword(Request $request, Admin $admin)
    {
        $request->validate([
            'admin_id' => 'required|exists:super_admins,id',
            'password' => 'required|min:6|confirmed',
        ]);

        $admin = SuperAdmin::where('id', $request->admin_id)
            ->where('admin_id', $admin->id)
            ->first();

        if (!$admin) {
            return back()->with('error', 'Admin user not found for this tenant.');
        }

        $admin->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', "Password reset successfully for {$admin->email}!");
    }
}
