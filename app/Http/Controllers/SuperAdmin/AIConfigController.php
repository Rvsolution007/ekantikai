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
        $currentModel = Setting::getValue('global_ai_model', 'gemini-2.0-flash');

        $providers = AiUsageLog::getProviders();
        $models = $this->getAllModels();

        // Get usage stats
        $totalUsage = AiUsageLog::getTokenStatsForAdmin(0, 'month'); // Global
        $adminUsage = $this->getUsageByAdmin('month');

        // Get Vertex AI settings
        $vertexRegion = Setting::getValue('vertex_region', '');
        $vertexProjectId = Setting::getValue('vertex_project_id', '');
        $vertexServiceEmail = Setting::getValue('vertex_service_email', '');
        $vertexPrivateKey = Setting::getValue('vertex_private_key', '');

        return view('superadmin.ai-config.index', compact(
            'currentProvider',
            'currentModel',
            'providers',
            'models',
            'totalUsage',
            'adminUsage',
            'vertexRegion',
            'vertexProjectId',
            'vertexServiceEmail',
            'vertexPrivateKey'
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
            'vertex_region' => 'nullable|string',
            'vertex_project_id' => 'nullable|string',
            'vertex_service_email' => 'nullable|email',
            'vertex_private_key' => 'nullable|string',
            'openai_api_key' => 'nullable|string',
            'deepseek_api_key' => 'nullable|string',
        ]);

        // Update provider and model
        Setting::setValue('global_ai_provider', $validated['provider']);
        Setting::setValue('global_ai_model', $validated['model']);

        // Update Vertex AI settings
        if (!empty($validated['vertex_region'])) {
            Setting::setValue('vertex_region', $validated['vertex_region'], 'ai');
        }
        if (!empty($validated['vertex_project_id'])) {
            Setting::setValue('vertex_project_id', $validated['vertex_project_id'], 'ai');
        }
        if (!empty($validated['vertex_service_email'])) {
            Setting::setValue('vertex_service_email', $validated['vertex_service_email'], 'ai');
        }
        // Only update private key if it's not placeholder
        if (!empty($validated['vertex_private_key']) && !str_contains($validated['vertex_private_key'], '••••')) {
            Setting::setValue('vertex_private_key', $validated['vertex_private_key'], 'ai', 'encrypted');
        }

        // Update other API keys (only if provided)
        if (!empty($validated['openai_api_key'])) {
            Setting::setValue('openai_api_key', $validated['openai_api_key'], 'ai', 'encrypted');
        }
        if (!empty($validated['deepseek_api_key'])) {
            Setting::setValue('deepseek_api_key', $validated['deepseek_api_key'], 'ai', 'encrypted');
        }

        return redirect()->route('superadmin.ai-config.index')
            ->with('success', 'AI configuration updated successfully.');
    }

    /**
     * Test AI Model
     */
    public function testAI()
    {
        try {
            $geminiService = new \App\Services\AI\GeminiService();

            // Send a simple test prompt
            $response = $geminiService->generateContent('Say "Hello! AI is working correctly." in exactly those words.');

            if ($response) {
                return response()->json([
                    'success' => true,
                    'response' => $response,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'AI returned empty response. Check your Vertex AI configuration.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
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

        return view('superadmin.ai-config.dashboard', compact(
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

    /**
     * AI Playground page
     */
    public function playground()
    {
        $provider = Setting::getValue('global_ai_provider', 'vertex');
        $model = Setting::getValue('global_ai_model', 'gemini-2.0-flash');

        // Check if AI is working by doing a quick test
        $aiWorking = false;
        try {
            $aiService = new \App\Services\AIService();
            $response = $aiService->callAI("You are a helpful assistant. Respond briefly.", "Say 'OK' only");
            $aiWorking = !empty($response);
        } catch (\Exception $e) {
            $aiWorking = false;
        }

        // Determine actual provider being used
        $vertexPrivateKey = Setting::getValue('vertex_private_key', '');
        $vertexProjectId = Setting::getValue('vertex_project_id', '');
        if (!empty($vertexPrivateKey) && !empty($vertexProjectId)) {
            $provider = 'Vertex AI';
        }

        return view('superadmin.ai-config.playground', compact('provider', 'model', 'aiWorking'));
    }

    /**
     * Handle playground chat message
     */
    public function playgroundChat(Request $request)
    {
        $message = $request->input('message');

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'error' => 'Message is required'
            ]);
        }

        try {
            $aiService = new \App\Services\AIService();
            $response = $aiService->callAI("You are a helpful AI assistant. Answer questions clearly and helpfully.", $message);

            if (empty($response)) {
                return response()->json([
                    'success' => false,
                    'error' => 'AI returned empty response'
                ]);
            }

            return response()->json([
                'success' => true,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
