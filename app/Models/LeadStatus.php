<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LeadStatus extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'slug',
        'color',
        'order',
        'is_default',
        'is_active',
        'connected_question_ids',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'connected_question_ids' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($status) {
            if (empty($status->slug)) {
                $status->slug = Str::slug($status->name);
            }
        });
    }

    /**
     * Get the admin (tenant)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get leads with this status
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get connected questionnaire nodes
     */
    public function connectedNodes()
    {
        $ids = $this->connected_question_ids ?? [];
        if (empty($ids)) {
            return collect();
        }
        return QuestionnaireNode::whereIn('id', $ids)->get();
    }

    /**
     * Check if a lead matches this status based on answered questions
     */
    public function matchesLead(Lead $lead): bool
    {
        $connectedIds = $this->connected_question_ids ?? [];
        if (empty($connectedIds)) {
            return false;
        }

        $collectedData = $lead->collected_data ?? [];
        $answeredQuestions = array_keys($collectedData['global_questions'] ?? []);

        // Check if all connected questions have been answered
        foreach ($connectedIds as $nodeId) {
            $node = QuestionnaireNode::find($nodeId);
            if ($node && $node->questionnaireField) {
                $fieldName = $node->questionnaireField->field_name;
                if (!in_array($fieldName, $answeredQuestions)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Scope for active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get default status for admin
     */
    public static function getDefault(int $adminId): ?self
    {
        return static::where('admin_id', $adminId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Create default statuses for an admin
     */
    public static function createDefaultsForAdmin(int $adminId): void
    {
        $defaults = [
            ['name' => 'New Lead', 'color' => '#3b82f6', 'order' => 1, 'is_default' => true],
            ['name' => 'Qualified', 'color' => '#f59e0b', 'order' => 2],
            ['name' => 'Negotiation', 'color' => '#8b5cf6', 'order' => 3],
            ['name' => 'Confirmed', 'color' => '#10b981', 'order' => 4],
            ['name' => 'Lost', 'color' => '#ef4444', 'order' => 5],
        ];

        foreach ($defaults as $status) {
            static::create(array_merge($status, [
                'admin_id' => $adminId,
                'slug' => Str::slug($status['name']),
            ]));
        }
    }
}
