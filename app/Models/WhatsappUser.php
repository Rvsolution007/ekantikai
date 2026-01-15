<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'instance',
        'city',
        'bot_enabled',
        'pause_reason',
        'conversation_mode',
        'catalog_sent',
        'catalog_sent_at',
        'last_activity_at',
    ];

    protected $casts = [
        'bot_enabled' => 'boolean',
        'catalog_sent' => 'boolean',
        'catalog_sent_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Conversation modes
    const MODE_AI_BOT = 'ai_bot';
    const MODE_HUMAN_ONLY = 'human_only';
    const MODE_HYBRID = 'hybrid';

    /**
     * Get all leads for this user
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get all chat messages
     */
    public function chats()
    {
        return $this->hasMany(WhatsappChat::class);
    }

    /**
     * Get the latest lead
     */
    public function latestLead()
    {
        return $this->hasOne(Lead::class)->latestOfMany();
    }

    /**
     * Get or create lead for this user
     */
    public function getOrCreateLead(): Lead
    {
        $lead = $this->leads()->where('status', 'open')->latest()->first();

        if (!$lead) {
            $lead = $this->leads()->create([
                'stage' => 'New Lead',
                'status' => 'open',
            ]);
        }

        return $lead;
    }

    /**
     * Check if bot is enabled
     */
    public function isBotEnabled(): bool
    {
        return $this->bot_enabled && $this->conversation_mode !== self::MODE_HUMAN_ONLY;
    }

    /**
     * Update last activity
     */
    public function touchActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedNumberAttribute(): string
    {
        $number = $this->number;
        if (strlen($number) === 12 && str_starts_with($number, '91')) {
            return '+' . substr($number, 0, 2) . ' ' . substr($number, 2, 5) . ' ' . substr($number, 7);
        }
        return '+' . $number;
    }
}
