<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'slug',
        'user_id',
        'email',
        'password',
        'role',
        'is_admin_active',
        'last_login_at',
        'phone',
        'logo_url',
        'company_name',
        'industry',
        'address',
        'subscription_plan',
        'trial_ends_at',
        'subscription_ends_at',
        'whatsapp_api_url',
        'whatsapp_api_key',
        'whatsapp_instance',
        'whatsapp_connected',
        'bot_control_number',
        'gemini_api_key',
        'ai_model',
        'timezone',
        'language',
        'lead_timeout_hours',
        'send_product_images',
        'delete_passcode',
        'is_active',
        'ai_system_prompt',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_admin_active' => 'boolean',
        'whatsapp_connected' => 'boolean',
        'send_product_images' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_api_key',
        'gemini_api_key',
    ];

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->slug)) {
                $admin->slug = Str::slug($admin->name);
            }
        });

        static::created(function ($admin) {
            // Create default credit record
            $admin->credits()->create([
                'total_credits' => $admin->subscription_plan === 'free' ? 100 : 1000,
                'available_credits' => $admin->subscription_plan === 'free' ? 100 : 1000,
            ]);
        });
    }

    // Relationships
    public function superAdmins()
    {
        return $this->hasMany(SuperAdmin::class, 'admin_id');
    }

    public function credits()
    {
        return $this->hasOne(AdminCredit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function aiAgents()
    {
        return $this->hasMany(AiAgent::class);
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class);
    }

    public function whatsappUsers()
    {
        return $this->hasMany(WhatsappUser::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function chats()
    {
        return $this->hasMany(WhatsappChat::class);
    }

    public function catalogues()
    {
        return $this->hasMany(Catalogue::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    // Accessors
    public function getLogoAttribute()
    {
        return $this->logo_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function getIsTrialActiveAttribute(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getIsSubscriptionActiveAttribute(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    // Methods
    public function hasCredits(): bool
    {
        return $this->credits && $this->credits->available_credits > 0;
    }

    public function deductCredits(float $amount, string $reason = 'usage'): bool
    {
        if (!$this->credits || $this->credits->available_credits < $amount) {
            return false;
        }

        $this->credits->decrement('available_credits', $amount);
        $this->credits->increment('used_credits', $amount);

        return true;
    }

    public function isActive(): bool
    {
        return $this->is_active && ($this->is_trial_active || $this->is_subscription_active || $this->subscription_plan === 'free');
    }

    public function getDefaultAiAgent()
    {
        return $this->aiAgents()->where('is_default', true)->first()
            ?? $this->aiAgents()->where('is_active', true)->first();
    }

    // Subscription Plan Features
    public function getPlanLimits(): array
    {
        return match ($this->subscription_plan) {
            'free' => [
                'messages_per_month' => 100,
                'ai_agents' => 1,
                'workflows' => 2,
                'team_members' => 1,
            ],
            'basic' => [
                'messages_per_month' => 1000,
                'ai_agents' => 3,
                'workflows' => 10,
                'team_members' => 3,
            ],
            'pro' => [
                'messages_per_month' => 10000,
                'ai_agents' => 10,
                'workflows' => 50,
                'team_members' => 10,
            ],
            'enterprise' => [
                'messages_per_month' => -1, // unlimited
                'ai_agents' => -1,
                'workflows' => -1,
                'team_members' => -1,
            ],
            default => [],
        };
    }
}
