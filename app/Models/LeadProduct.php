<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadProduct extends Model
{
    protected $fillable = [
        'lead_id',
        'admin_id',
        'category',
        'model',
        'size',
        'finish',
        'qty',
        'material',
        'packaging',
        'unique_key',
        'confirmed_at',
    ];

    protected $casts = [
        'qty' => 'integer',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the admin
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get field value dynamically
     */
    public function getFieldValue(string $fieldName): ?string
    {
        return $this->{$fieldName} ?? null;
    }

    /**
     * Set field value dynamically
     */
    public function setFieldValue(string $fieldName, $value): void
    {
        if (in_array($fieldName, $this->fillable)) {
            $this->{$fieldName} = $value;
        }
    }

    /**
     * Check if field has value
     */
    public function hasFieldValue(string $fieldName): bool
    {
        $value = $this->getFieldValue($fieldName);
        return $value !== null && $value !== '';
    }

    /**
     * Get all product data as array
     */
    public function toProductArray(): array
    {
        return [
            '_id' => $this->id,
            '_source' => 'lead_product',
            'category' => $this->category,
            'model' => $this->model,
            'size' => $this->size,
            'finish' => $this->finish,
            'qty' => $this->qty,
            'material' => $this->material,
            'packaging' => $this->packaging,
        ];
    }
}
