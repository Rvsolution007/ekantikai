<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlobalQuestion extends Model
{
    protected $fillable = [
        'admin_id',
        'field_name',
        'display_name',
        'field_type',
        'options',
        'trigger_position',
        'trigger_after_field',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Check if this question should be asked first
    public function shouldAskFirst(): bool
    {
        return $this->trigger_position === 'first';
    }

    // Check if this question should be asked after a specific field
    public function shouldAskAfter(string $fieldName): bool
    {
        return $this->trigger_position === 'after_field'
            && $this->trigger_after_field === $fieldName;
    }
}
