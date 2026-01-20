<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductQuestion extends Model
{
    protected $table = 'product_questions';

    protected $fillable = [
        'admin_id',
        'field_name',
        'display_name',
        'question_template', // Custom question format for bot to ask
        'field_type',
        'is_required',
        'sort_order',
        'is_unique_key',
        'unique_key_order',
        'is_unique_field', // For identifying unique products like Model Number
        'options_source',
        'options_manual',
        'catalogue_field',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_unique_key' => 'boolean',
        'is_unique_field' => 'boolean',
        'options_manual' => 'array',
        'validation_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Scope for active fields
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for unique key fields (ordered)
    public function scopeUniqueKeyFields($query)
    {
        return $query->where('is_unique_key', true)
            ->orderBy('unique_key_order');
    }

    // Scope ordered by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Get options based on source
    public function getOptions(): array
    {
        if ($this->options_source === 'manual') {
            return $this->options_manual ?? [];
        }

        // For catalogue source, fetch from products
        if ($this->options_source === 'catalogue' && $this->catalogue_field) {
            return $this->getOptionsFromCatalogue();
        }

        return [];
    }

    protected function getOptionsFromCatalogue(): array
    {
        // Get unique values from products/catalogues for this field
        $field = $this->catalogue_field;

        return Catalogue::where('admin_id', $this->admin_id)
            ->whereNotNull($field)
            ->distinct()
            ->pluck($field)
            ->filter()
            ->values()
            ->toArray();
    }
}
