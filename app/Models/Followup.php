<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'stage',
        'status',
        'has_pending_details',
        'last_activity_at',
        'next_followup_at',
        'override_next_at',
        'timezone',
        'ai_message',
        'expected_global_field',
    ];

    protected $casts = [
        'has_pending_details' => 'boolean',
        'last_activity_at' => 'datetime',
        'next_followup_at' => 'datetime',
        'override_next_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';

    /**
     * Get the lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get WhatsApp user through lead
     */
    public function getWhatsappUserAttribute()
    {
        return $this->lead?->whatsappUser;
    }

    /**
     * Check if followup is due
     */
    public function isDue(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $checkTime = $this->override_next_at ?? $this->next_followup_at;

        if (!$checkTime) {
            return false;
        }

        return now()->gte($checkTime);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Schedule next followup
     */
    public function scheduleNext(int $hours = 24): void
    {
        $this->update([
            'next_followup_at' => now()->addHours($hours),
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Update last activity
     */
    public function touchActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Scope for pending followups
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for due followups
     */
    public function scopeDue($query)
    {
        return $query->pending()
            ->where(function ($q) {
                $q->where('next_followup_at', '<=', now())
                    ->orWhere('override_next_at', '<=', now());
            });
    }

    /**
     * Get days since last activity
     */
    public function getDaysSinceActivityAttribute(): int
    {
        if (!$this->last_activity_at) {
            return 0;
        }

        return (int) now()->diffInDays($this->last_activity_at);
    }
}
