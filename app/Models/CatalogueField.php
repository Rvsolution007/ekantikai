<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CatalogueField extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'product_question_id',
        'field_name',
        'field_key',
        'field_type',
        'is_unique',
        'is_required',
        'sort_order',
        'options',
    ];

    protected $casts = [
        'is_unique' => 'boolean',
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    // Field types
    const TYPE_TEXT = 'text';
    const TYPE_NUMBER = 'number';
    const TYPE_SELECT = 'select';

    /**
     * Get available field types
     */
    public static function getFieldTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_SELECT => 'Dropdown (Select)',
        ];
    }

    /**
     * Get the tenant
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Generate field key from name
     */
    public static function generateFieldKey(string $name): string
    {
        return Str::snake(Str::lower($name));
    }

    /**
     * Scope for tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('admin_id', $tenantId);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get options as array (for select type)
     */
    public function getOptionsArrayAttribute(): array
    {
        if (!$this->options || !is_array($this->options)) {
            return [];
        }
        return $this->options;
    }

    /**
     * Check if field is select type
     */
    public function isSelect(): bool
    {
        return $this->field_type === self::TYPE_SELECT;
    }

    /**
     * Check if field is number type
     */
    public function isNumber(): bool
    {
        return $this->field_type === self::TYPE_NUMBER;
    }

    /**
     * Validate a value for this field
     */
    public function validateValue($value): array
    {
        $errors = [];

        // Required check
        if ($this->is_required && empty($value) && $value !== '0') {
            $errors[] = "{$this->field_name} is required.";
        }

        // Number type check
        if ($this->isNumber() && !empty($value) && !is_numeric($value)) {
            $errors[] = "{$this->field_name} must be a number.";
        }

        // Select type check
        if ($this->isSelect() && !empty($value)) {
            $options = $this->options_array;
            if (!empty($options) && !in_array($value, $options)) {
                $errors[] = "{$this->field_name} must be one of: " . implode(', ', $options);
            }
        }

        return $errors;
    }
}
