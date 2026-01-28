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
        'data',          // NEW: Dynamic JSON data for all fields
        'source',        // NEW: 'bot', 'manual', 'import'
        'confirmed_at',
    ];

    protected $casts = [
        'qty' => 'integer',
        'data' => 'array',
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
     * Get field value - first check 'data' JSON, then direct column
     */
    public function getFieldValue(string $fieldName): ?string
    {
        // Check dynamic data first
        if (isset($this->data[$fieldName])) {
            return $this->data[$fieldName];
        }

        // Fallback to direct column
        return $this->{$fieldName} ?? null;
    }

    /**
     * Set field value - stores in 'data' JSON and syncs to column if exists
     */
    public function setFieldValue(string $fieldName, $value): void
    {
        // Update in data JSON
        $data = $this->data ?? [];
        $data[$fieldName] = $value;
        $this->data = $data;

        // Also update direct column if it exists
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
     * Clear a non-unique field value (set to blank)
     * Used when user says "black nahi chahiye" for non-unique fields
     */
    public function clearField(string $fieldName): void
    {
        $this->setFieldValue($fieldName, null);
        $this->save();
    }

    /**
     * Check if this product matches unique field value
     * Used for finding products by unique identifier (Model Number etc.)
     */
    public function matchesUniqueField(string $value): bool
    {
        // Get unique field from ProductQuestion
        $uniqueField = ProductQuestion::where('admin_id', $this->admin_id)
            ->where('is_unique_field', true)
            ->first();

        if (!$uniqueField) {
            return false;
        }

        $fieldValue = $this->getFieldValue($uniqueField->field_name);
        return strtolower($fieldValue ?? '') === strtolower($value);
    }

    /**
     * Generate unique key from unique key fields
     */
    public function generateUniqueKey(): string
    {
        $uniqueFields = ProductQuestion::where('admin_id', $this->admin_id)
            ->where('is_unique_key', true)
            ->orderBy('unique_key_order')
            ->pluck('field_name');

        $parts = [];
        foreach ($uniqueFields as $fieldName) {
            $parts[] = $this->getFieldValue($fieldName) ?? '';
        }

        return implode('|', $parts);
    }

    /**
     * Get all product data as array (combines direct columns + data JSON)
     */
    public function toProductArray(): array
    {
        $result = [
            '_id' => $this->id,
            '_source' => $this->source ?? 'bot',
        ];

        // Add direct columns
        $directColumns = ['category', 'model', 'size', 'finish', 'qty', 'material', 'packaging'];
        foreach ($directColumns as $col) {
            if ($this->{$col} !== null) {
                $result[$col] = $this->{$col};
            }
        }

        // Merge with dynamic data (data takes priority for same keys)
        if (is_array($this->data)) {
            $result = array_merge($result, $this->data);
        }

        return $result;
    }

    /**
     * Create from collected data array
     */
    public static function createFromCollectedData(Lead $lead, array $collectedData): self
    {
        $directColumns = ['category', 'model', 'size', 'finish', 'qty', 'material', 'packaging'];

        $attributes = [
            'lead_id' => $lead->id,
            'admin_id' => $lead->admin_id,
            'source' => 'bot',
            'data' => $collectedData,
        ];

        // Map collected data to direct columns where possible
        foreach ($directColumns as $col) {
            if (isset($collectedData[$col])) {
                $attributes[$col] = $collectedData[$col];
            }
        }

        $product = new self($attributes);
        $product->unique_key = $product->generateUniqueKey();
        $product->save();

        return $product;
    }

    /**
     * Get connection status for SuperAdmin dashboard
     */
    public function getFieldsWithStatus(): array
    {
        $productQuestions = ProductQuestion::where('admin_id', $this->admin_id)
            ->active()
            ->ordered()
            ->get();

        $fields = [];
        foreach ($productQuestions as $pq) {
            $value = $this->getFieldValue($pq->field_name);
            $fields[] = [
                'field_name' => $pq->field_name,
                'display_name' => $pq->display_name,
                'value' => $value,
                'has_value' => $value !== null && $value !== '',
                'is_unique_key' => $pq->is_unique_key,
                'is_unique_field' => $pq->is_unique_field,
            ];
        }

        return $fields;
    }
}

