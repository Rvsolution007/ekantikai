<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\WhatsappChat;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Overall Stats (Admin = previously Tenant)
        $stats = [
            'total_tenants' => Admin::count(),
            'active_tenants' => Admin::where('is_active', true)->count(),
            'total_revenue' => Payment::successful()->sum('amount'),
            'this_month_revenue' => Payment::successful()->thisMonth()->sum('amount'),
            'total_leads' => Lead::count(),
            'total_messages' => WhatsappChat::count(),
        ];

        // Recent Admins (previously Tenants)
        $recentTenants = Admin::latest()
            ->limit(5)
            ->get();

        // Recent Payments
        $recentPayments = Payment::with('admin')
            ->latest()
            ->limit(10)
            ->get();

        // Revenue Chart (Last 7 days)
        $revenueChart = Payment::successful()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Admin Growth Chart (previously Tenant Growth)
        $tenantGrowth = Admin::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Subscription Distribution
        $subscriptionDistribution = Admin::select('subscription_plan', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription_plan')
            ->pluck('count', 'subscription_plan')
            ->toArray();

        return view('superadmin.dashboard.index', compact(
            'stats',
            'recentTenants',
            'recentPayments',
            'revenueChart',
            'tenantGrowth',
            'subscriptionDistribution'
        ));
    }
}
