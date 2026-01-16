<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $fillable = [
        'admin_id',
        'phone',
        'name',
        'global_fields',
        'global_asked',
        'bot_enabled',
        'bot_stopped_by_user',
        'bot_stopped_at',
        'bot_stop_reason',
        'pause_reason',
        'detected_language',
        'last_activity_at',
        'last_greeted_at',
    ];

    protected $casts = [
        'global_fields' => 'array',
        'global_asked' => 'array',
        'bot_enabled' => 'boolean',
        'bot_stopped_by_user' => 'boolean',
        'bot_stopped_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'last_greeted_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function questionnaireState(): HasOne
    {
        return $this->hasOne(CustomerQuestionnaireState::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    // Get or create questionnaire state
    public function getOrCreateState(): CustomerQuestionnaireState
    {
        return $this->questionnaireState ?? CustomerQuestionnaireState::create([
            'admin_id' => $this->admin_id,
            'customer_id' => $this->id,
        ]);
    }

    // Get global field value
    public function getGlobalField(string $key, $default = null)
    {
        return $this->global_fields[$key] ?? $default;
    }

    // Set global field value
    public function setGlobalField(string $key, $value): void
    {
        $fields = $this->global_fields ?? [];
        $fields[$key] = $value;
        $this->global_fields = $fields;
        $this->save();
    }

    // Check if global question was asked
    public function wasGlobalAsked(string $key): bool
    {
        return ($this->global_asked[$key] ?? false) === true;
    }

    // Mark global question as asked
    public function markGlobalAsked(string $key): void
    {
        $asked = $this->global_asked ?? [];
        $asked[$key] = true;
        $this->global_asked = $asked;
        $this->save();
    }

    // Update last activity
    public function updateLastActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Stop bot for this customer via WhatsApp command
     */
    public function stopBot(string $reason = 'user_request'): void
    {
        $this->update([
            'bot_enabled' => false,
            'bot_stopped_by_user' => true,
            'bot_stopped_at' => now(),
            'bot_stop_reason' => $reason,
        ]);

        // Also stop bot for current lead
        $currentLead = $this->currentLead();
        if ($currentLead) {
            $currentLead->update(['bot_active' => false]);
        }
    }

    /**
     * Start bot for this customer via WhatsApp command
     */
    public function startBot(): void
    {
        $this->update([
            'bot_enabled' => true,
            'bot_stopped_by_user' => false,
            'bot_stopped_at' => null,
            'bot_stop_reason' => null,
        ]);

        // Also start bot for current lead
        $currentLead = $this->currentLead();
        if ($currentLead) {
            $currentLead->update(['bot_active' => true]);
        }
    }

    /**
     * Check if bot is stopped for this customer
     */
    public function isBotStopped(): bool
    {
        return !$this->bot_enabled || $this->bot_stopped_by_user;
    }

    /**
     * Set detected language
     */
    public function setLanguage(string $language): void
    {
        $this->detected_language = $language;
        $this->save();
    }

    /**
     * Get or create lead for this customer
     * Creates new lead if:
     * - No existing open lead exists
     * - Last activity is older than admin's lead_timeout_hours setting
     */
    public function getOrCreateLead(): Lead
    {
        $admin = $this->admin;
        $timeoutHours = $admin->lead_timeout_hours ?? 24;

        // Find existing open lead
        $existingLead = $this->leads()
            ->where('status', 'open')
            ->latest()
            ->first();

        // Check if we should create a new lead based on timeout
        $shouldCreateNew = false;

        if (!$existingLead) {
            // No existing lead, create new one
            $shouldCreateNew = true;
        } elseif ($this->last_activity_at) {
            // Check if last activity is older than timeout
            $hoursSinceLastActivity = $this->last_activity_at->diffInHours(now());
            if ($hoursSinceLastActivity > $timeoutHours) {
                // Close the old lead and create new one
                $existingLead->update(['status' => 'closed', 'bot_active' => false]);
                $shouldCreateNew = true;
            }
        }

        if ($shouldCreateNew) {
            // Get default lead status for this admin
            $defaultStatus = LeadStatus::getDefault($admin->id);

            $existingLead = Lead::create([
                'admin_id' => $admin->id,
                'customer_id' => $this->id,
                'stage' => Lead::STAGE_NEW_LEAD,
                'status' => 'open',
                'lead_status_id' => $defaultStatus?->id,
                'bot_active' => $this->bot_enabled && !$this->bot_stopped_by_user,
                'lead_quality' => Lead::QUALITY_COLD,
                'lead_score' => 0,
                'detected_language' => $this->detected_language,
            ]);
        }

        return $existingLead;
    }

    /**
     * Get the current open lead (without creating)
     */
    public function currentLead(): ?Lead
    {
        return $this->leads()->where('status', 'open')->latest()->first();
    }
}
