<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'admin_id',
        'model_name',
        'provider',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost_usd',
        'request_type',
        'metadata',
    ];

    protected $casts = [
        'cost_usd' => 'decimal:6',
        'metadata' => 'array',
    ];

    // Provider constants
    const PROVIDER_GOOGLE = 'google';
    const PROVIDER_OPENAI = 'openai';
    const PROVIDER_DEEPSEEK = 'deepseek';

    // Request type constants
    const TYPE_MESSAGE = 'message';
    const TYPE_EXTRACTION = 'extraction';
    const TYPE_GENERATION = 'generation';

    /**
     * Cost per 1M tokens by provider and model
     */
    protected static array $costPerMillion = [
        'google' => [
            'gemini-2.5-flash' => ['input' => 0.075, 'output' => 0.30],
            'gemini-2.5-pro' => ['input' => 1.25, 'output' => 5.00],
            'gemini-2.0-flash' => ['input' => 0.10, 'output' => 0.40],
            'gemini-1.5-flash' => ['input' => 0.075, 'output' => 0.30],
            'gemini-1.5-pro' => ['input' => 1.25, 'output' => 5.00],
        ],
        'openai' => [
            'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
            'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
            'gpt-4-turbo' => ['input' => 10.00, 'output' => 30.00],
            'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
        ],
        'deepseek' => [
            'deepseek-chat' => ['input' => 0.14, 'output' => 0.28],
            'deepseek-coder' => ['input' => 0.14, 'output' => 0.28],
        ],
    ];

    /**
     * Get the admin (tenant)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Log AI usage and calculate cost
     */
    public static function log(
        int $adminId,
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        string $requestType = self::TYPE_MESSAGE,
        array $metadata = []
    ): self {
        $cost = self::calculateCost($provider, $model, $inputTokens, $outputTokens);

        return self::create([
            'admin_id' => $adminId,
            'provider' => $provider,
            'model_name' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'cost_usd' => $cost,
            'request_type' => $requestType,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Calculate cost based on provider and model
     */
    public static function calculateCost(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        $rates = self::$costPerMillion[$provider][$model] ?? ['input' => 0.10, 'output' => 0.40];

        $inputCost = ($inputTokens / 1_000_000) * $rates['input'];
        $outputCost = ($outputTokens / 1_000_000) * $rates['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Get total cost for an admin
     */
    public static function getTotalCostForAdmin(int $adminId, ?string $period = null): float
    {
        $query = self::where('admin_id', $adminId);

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return (float) $query->sum('cost_usd');
    }

    /**
     * Get token stats for an admin
     */
    public static function getTokenStatsForAdmin(int $adminId, ?string $period = null): array
    {
        $query = self::where('admin_id', $adminId);

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return [
            'total_input_tokens' => (int) $query->sum('input_tokens'),
            'total_output_tokens' => (int) $query->sum('output_tokens'),
            'total_tokens' => (int) $query->sum('total_tokens'),
            'total_cost' => (float) $query->sum('cost_usd'),
            'request_count' => (int) $query->count(),
        ];
    }

    /**
     * Get available models for a provider
     */
    public static function getModelsForProvider(string $provider): array
    {
        return array_keys(self::$costPerMillion[$provider] ?? []);
    }

    /**
     * Get all available providers
     */
    public static function getProviders(): array
    {
        return [
            self::PROVIDER_GOOGLE => 'Google (Gemini)',
            self::PROVIDER_OPENAI => 'OpenAI (GPT)',
            self::PROVIDER_DEEPSEEK => 'DeepSeek',
        ];
    }
}
