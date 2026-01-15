<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\WhatsappUser;
use App\Models\WhatsappChat;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        // Lead statistics
        $leadStats = [
            'total' => Lead::count(),
            'new' => Lead::where('stage', 'New Lead')->count(),
            'qualified' => Lead::where('stage', 'Qualified')->count(),
            'confirmed' => Lead::where('stage', 'Confirm')->count(),
            'lost' => Lead::where('stage', 'Lose')->count(),
        ];

        // User statistics
        $userStats = [
            'total' => WhatsappUser::count(),
            'active_today' => WhatsappUser::whereDate('last_activity_at', today())->count(),
            'bot_enabled' => WhatsappUser::where('bot_enabled', true)->count(),
        ];

        // Today's chat count
        $todayChats = WhatsappChat::whereDate('created_at', today())->count();

        // Recent leads (last 10)
        $recentLeads = Lead::with('whatsappUser')
            ->latest()
            ->take(10)
            ->get();

        // Recent chats (last 20)
        $recentChats = WhatsappChat::with('whatsappUser')
            ->where('role', 'user')
            ->latest()
            ->take(20)
            ->get();

        // Lead stage chart data (last 7 days)
        $chartData = $this->getLeadChartData();

        // Popular products
        $popularProducts = Product::select('product', DB::raw('count(*) as count'))
            ->whereNotNull('product')
            ->groupBy('product')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'leadStats',
            'userStats',
            'todayChats',
            'recentLeads',
            'recentChats',
            'chartData',
            'popularProducts'
        ));
    }

    /**
     * Get lead chart data for last 7 days
     */
    private function getLeadChartData(): array
    {
        $dates = [];
        $newLeads = [];
        $qualified = [];
        $confirmed = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');

            $newLeads[] = Lead::whereDate('created_at', $date)
                ->where('stage', 'New Lead')
                ->count();

            $qualified[] = Lead::whereDate('created_at', $date)
                ->where('stage', 'Qualified')
                ->count();

            $confirmed[] = Lead::whereDate('created_at', $date)
                ->where('stage', 'Confirm')
                ->count();
        }

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'New Leads',
                    'data' => $newLeads,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Qualified',
                    'data' => $qualified,
                    'borderColor' => '#eab308',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.1)',
                ],
                [
                    'label' => 'Confirmed',
                    'data' => $confirmed,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
        ];
    }
}
