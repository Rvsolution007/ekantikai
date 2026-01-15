<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AiAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'slug',
        'description',
        'type',
        'model',
        'system_prompt',
        'temperature',
        'max_tokens',
        'persona_name',
        'language',
        'allowed_topics',
        'blocked_keywords',
        'is_active',
        'is_default',
        'total_conversations',
        'successful_responses',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'allowed_topics' => 'array',
        'blocked_keywords' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agent) {
            if (empty($agent->slug)) {
                $agent->slug = Str::slug($agent->name);
            }
        });

        static::saving(function ($agent) {
            // If this is being set as default, unset others
            if ($agent->is_default) {
                self::where('admin_id', $agent->tenant_id)
                    ->where('id', '!=', $agent->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function incrementConversations(): void
    {
        $this->increment('total_conversations');
    }

    public function incrementSuccessful(): void
    {
        $this->increment('successful_responses');
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_conversations == 0) {
            return 0;
        }

        return round(($this->successful_responses / $this->total_conversations) * 100, 1);
    }

    // Default prompts
    public static function getDefaultPrompts(): array
    {
        return [
            'sales' => "You are a helpful sales assistant. Be friendly, professional, and help customers with their product inquiries. Always greet politely and ask relevant follow-up questions.",
            'support' => "You are a customer support agent. Help resolve customer issues with patience and empathy. Escalate complex issues when needed.",
            'classifier' => "You classify incoming messages into categories. Analyze the message intent and extract relevant entities like product names, quantities, and preferences.",
            'custom' => "You are a helpful AI assistant.",
        ];
    }

    public static function getAvailableModels(): array
    {
        return [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fastest)',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro (Best Quality)',
        ];
    }
}
