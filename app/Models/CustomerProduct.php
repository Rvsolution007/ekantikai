<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProduct extends Model
{
    protected $fillable = [
        'admin_id',
        'customer_id',
        'lead_id',
        'field_values',
        'unique_key',
        'line_key',
        'status',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Get field value
    public function getFieldValue(string $key, $default = null)
    {
        return $this->field_values[$key] ?? $default;
    }

    // Set field value
    public function setFieldValue(string $key, $value): void
    {
        $values = $this->field_values ?? [];
        $values[$key] = $value;
        $this->field_values = $values;
    }

    // Build unique key from field values based on tenant config
    public static function buildUniqueKey(int $tenantId, array $fieldValues): string
    {
        $uniqueFields = ProductQuestion::where('admin_id', $tenantId)
            ->where('is_unique_key', true)
            ->orderBy('unique_key_order')
            ->pluck('field_name')
            ->toArray();

        $keyParts = [];
        foreach ($uniqueFields as $field) {
            $keyParts[] = strtolower(trim($fieldValues[$field] ?? ''));
        }

        return implode('|', $keyParts);
    }

    // Build line key (includes phone)
    public static function buildLineKey(string $phone, int $tenantId, array $fieldValues): string
    {
        $uniqueKey = static::buildUniqueKey($tenantId, $fieldValues);
        return $phone . '|' . $uniqueKey;
    }

    // Find existing product by unique key
    public static function findByUniqueKey(int $tenantId, int $customerId, string $uniqueKey): ?self
    {
        return static::where('admin_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('unique_key', $uniqueKey)
            ->first();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }
}
