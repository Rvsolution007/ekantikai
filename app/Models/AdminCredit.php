<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCredit extends Model
{
    use HasFactory;

    protected $table = 'admin_credits';

    protected $fillable = [
        'admin_id',
        'total_credits',
        'used_credits',
        'available_credits',
        'monthly_message_limit',
        'monthly_messages_used',
        'ai_calls_limit',
        'ai_calls_used',
        'credit_per_message',
        'credit_per_ai_call',
        'low_credit_notified',
        'last_reset_at',
    ];

    protected $casts = [
        'total_credits' => 'decimal:2',
        'used_credits' => 'decimal:2',
        'available_credits' => 'decimal:2',
        'credit_per_message' => 'decimal:4',
        'credit_per_ai_call' => 'decimal:4',
        'low_credit_notified' => 'boolean',
        'last_reset_at' => 'datetime',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Methods
    public function addCredits(float $amount): void
    {
        $this->increment('total_credits', $amount);
        $this->increment('available_credits', $amount);
        $this->update(['low_credit_notified' => false]);
    }

    public function deductMessageCredit(): bool
    {
        if ($this->available_credits < $this->credit_per_message) {
            return false;
        }

        $this->decrement('available_credits', $this->credit_per_message);
        $this->increment('used_credits', $this->credit_per_message);
        $this->increment('monthly_messages_used');

        $this->checkLowCredit();

        return true;
    }

    public function deductAiCallCredit(): bool
    {
        if ($this->available_credits < $this->credit_per_ai_call) {
            return false;
        }

        $this->decrement('available_credits', $this->credit_per_ai_call);
        $this->increment('used_credits', $this->credit_per_ai_call);
        $this->increment('ai_calls_used');

        $this->checkLowCredit();

        return true;
    }

    public function checkLowCredit(): void
    {
        $threshold = $this->total_credits * 0.1; // 10%

        if ($this->available_credits <= $threshold && !$this->low_credit_notified) {
            $this->update(['low_credit_notified' => true]);
            // TODO: Send low credit notification
        }
    }

    public function resetMonthlyUsage(): void
    {
        $this->update([
            'monthly_messages_used' => 0,
            'ai_calls_used' => 0,
            'last_reset_at' => now(),
        ]);
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_credits == 0) {
            return 0;
        }

        return round(($this->used_credits / $this->total_credits) * 100, 1);
    }

    public function isWithinMonthlyLimit(): bool
    {
        return $this->monthly_message_limit < 0 || $this->monthly_messages_used < $this->monthly_message_limit;
    }
}
