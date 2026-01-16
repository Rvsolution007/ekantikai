<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AiUsageLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class AIConfigController extends Controller
{
    /**
     * Display AI configuration page
     */
    public function index()
    {
        $currentProvider = Setting::getValue('global_ai_provider', 'google');
        $currentModel = Setting::getValue('global_ai_model', 'gemini-2.5-flash');

        $providers = AiUsageLog::getProviders();
        $models = $this->getAllModels();

        // Get usage stats
        $totalUsage = AiUsageLog::getTokenStatsForAdmin(0, 'month'); // Global
        $adminUsage = $this->getUsageByAdmin('month');

        return view('super-admin.ai-config.index', compact(
            'currentProvider',
            'currentModel',
            'providers',
            'models',
            'totalUsage',
            'adminUsage'
        ));
    }

    /**
     * Update AI configuration
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:google,openai,deepseek',
            'model' => 'required|string',
            'gemini_api_key' => 'nullable|string',
            'openai_api_key' => 'nullable|string',
            'deepseek_api_key' => 'nullable|string',
        ]);

        // Update provider and model
        Setting::setValue('global_ai_provider', $validated['provider']);
        Setting::setValue('global_ai_model', $validated['model']);

        // Update API keys (only if provided)
        if (!empty($validated['gemini_api_key'])) {
            Setting::setValue('gemini_api_key', $validated['gemini_api_key'], 'ai', 'encrypted');
        }
        if (!empty($validated['openai_api_key'])) {
            Setting::setValue('openai_api_key', $validated['openai_api_key'], 'ai', 'encrypted');
        }
        if (!empty($validated['deepseek_api_key'])) {
            Setting::setValue('deepseek_api_key', $validated['deepseek_api_key'], 'ai', 'encrypted');
        }

        return redirect()->route('super-admin.ai-config.index')
            ->with('success', 'AI configuration updated successfully.');
    }

    /**
     * Get AI usage dashboard
     */
    public function dashboard()
    {
        $period = request('period', 'month');

        $globalStats = $this->getGlobalStats($period);
        $adminUsage = $this->getUsageByAdmin($period);
        $modelUsage = $this->getUsageByModel($period);
        $dailyUsage = $this->getDailyUsage($period);

        return view('super-admin.ai-config.dashboard', compact(
            'globalStats',
            'adminUsage',
            'modelUsage',
            'dailyUsage',
            'period'
        ));
    }

    /**
     * Get global stats
     */
    protected function getGlobalStats(string $period): array
    {
        $query = AiUsageLog::query();

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return [
            'total_requests' => $query->count(),
            'total_tokens' => $query->sum('total_tokens'),
            'total_cost' => $query->sum('cost_usd'),
            'avg_tokens_per_request' => $query->count() > 0
                ? round($query->sum('total_tokens') / $query->count())
                : 0,
        ];
    }

    /**
     * Get usage by admin
     */
    protected function getUsageByAdmin(string $period): array
    {
        $query = AiUsageLog::query()
            ->select('admin_id')
            ->selectRaw('SUM(total_tokens) as tokens')
            ->selectRaw('SUM(cost_usd) as cost')
            ->selectRaw('COUNT(*) as requests')
            ->groupBy('admin_id');

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        $results = $query->get();
        $adminIds = $results->pluck('admin_id')->toArray();
        $admins = Admin::whereIn('id', $adminIds)->pluck('name', 'id');

        return $results->map(function ($row) use ($admins) {
            return [
                'admin_id' => $row->admin_id,
                'admin_name' => $admins[$row->admin_id] ?? 'Unknown',
                'tokens' => (int) $row->tokens,
                'cost' => (float) $row->cost,
                'requests' => (int) $row->requests,
            ];
        })->sortByDesc('cost')->values()->toArray();
    }

    /**
     * Get usage by model
     */
    protected function getUsageByModel(string $period): array
    {
        $query = AiUsageLog::query()
            ->select('provider', 'model_name')
            ->selectRaw('SUM(total_tokens) as tokens')
            ->selectRaw('SUM(cost_usd) as cost')
            ->selectRaw('COUNT(*) as requests')
            ->groupBy('provider', 'model_name');

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return $query->get()->map(function ($row) {
            return [
                'provider' => $row->provider,
                'model' => $row->model_name,
                'tokens' => (int) $row->tokens,
                'cost' => (float) $row->cost,
                'requests' => (int) $row->requests,
            ];
        })->sortByDesc('cost')->values()->toArray();
    }

    /**
     * Get daily usage for charts
     */
    protected function getDailyUsage(string $period): array
    {
        $days = match ($period) {
            'today' => 1,
            'week' => 7,
            'month' => 30,
            default => 30,
        };

        return AiUsageLog::query()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_tokens) as tokens')
            ->selectRaw('SUM(cost_usd) as cost')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'tokens' => (int) $row->tokens,
                    'cost' => (float) $row->cost,
                ];
            })
            ->toArray();
    }

    /**
     * Get all available models by provider
     */
    protected function getAllModels(): array
    {
        return [
            'google' => [
                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fastest, Cheapest)',
                'gemini-2.5-pro' => 'Gemini 2.5 Pro (Most Capable)',
                'gemini-2.0-flash' => 'Gemini 2.0 Flash',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            ],
            'openai' => [
                'gpt-4o' => 'GPT-4o (Most Capable)',
                'gpt-4o-mini' => 'GPT-4o Mini (Fast, Cheap)',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Cheapest)',
            ],
            'deepseek' => [
                'deepseek-chat' => 'DeepSeek Chat',
                'deepseek-coder' => 'DeepSeek Coder',
            ],
        ];
    }

    /**
     * API: Get current AI config
     */
    public function apiConfig()
    {
        return response()->json([
            'provider' => Setting::getValue('global_ai_provider', 'google'),
            'model' => Setting::getValue('global_ai_model', 'gemini-2.5-flash'),
            'providers' => AiUsageLog::getProviders(),
            'models' => $this->getAllModels(),
        ]);
    }

    /**
     * API: Get usage stats for an admin
     */
    public function apiUsage(Request $request)
    {
        $adminId = $request->input('admin_id');
        $period = $request->input('period', 'month');

        if ($adminId) {
            $stats = AiUsageLog::getTokenStatsForAdmin($adminId, $period);
        } else {
            $stats = $this->getGlobalStats($period);
        }

        return response()->json($stats);
    }
}
