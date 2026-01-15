<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_number',
        'user_name',
        'instance',
        'fixed_credit',
        'extra_credit',
        'total_credit',
        'usage_message',
        'usage_credit',
        'available_credit',
        'last_sender',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'fixed_credit' => 'decimal:2',
        'extra_credit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'usage_credit' => 'decimal:2',
        'available_credit' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Message cost per message
    const MESSAGE_COST = 2;

    /**
     * Get WhatsApp user by number
     */
    public function whatsappUser()
    {
        return WhatsappUser::where('number', $this->user_number)->first();
    }

    /**
     * Deduct credit for message
     */
    public function deductMessageCredit(): bool
    {
        if ($this->available_credit < self::MESSAGE_COST / 2) {
            return false;
        }

        $this->increment('usage_message', self::MESSAGE_COST);
        $this->usage_credit = $this->usage_message / 2;
        $this->available_credit = $this->total_credit - $this->usage_credit;
        $this->save();

        return true;
    }

    /**
     * Add extra credit
     */
    public function addExtraCredit(float $amount): void
    {
        $this->extra_credit += $amount;
        $this->total_credit = $this->fixed_credit + $this->extra_credit;
        $this->available_credit = $this->total_credit - $this->usage_credit;
        $this->save();
    }

    /**
     * Reset credits (for new period)
     */
    public function resetCredits(float $fixedCredit, ?string $startDate = null, ?string $endDate = null): void
    {
        $this->fixed_credit = $fixedCredit;
        $this->extra_credit = 0;
        $this->total_credit = $fixedCredit;
        $this->usage_message = 0;
        $this->usage_credit = 0;
        $this->available_credit = $fixedCredit;
        $this->start_date = $startDate ?? now()->toDateString();
        $this->end_date = $endDate ?? now()->addMonth()->toDateString();
        $this->save();
    }

    /**
     * Check if credits are available
     */
    public function hasCredits(): bool
    {
        return $this->is_active && $this->available_credit > 0;
    }

    /**
     * Check if subscription is active
     */
    public function isSubscriptionActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_credit <= 0) {
            return 0;
        }

        return round(($this->usage_credit / $this->total_credit) * 100, 2);
    }

    /**
     * Scope for active credits
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for user number
     */
    public function scopeForNumber($query, string $number)
    {
        return $query->where('user_number', $number);
    }
}
