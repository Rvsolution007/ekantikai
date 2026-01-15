<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'number',
        'product',
        'model',
        'size',
        'finish',
        'qty',
        'material',
        'packaging',
        'line_key',
    ];

    // Product types
    const TYPE_CABINET = 'Cabinet handles';
    const TYPE_WARDROBE = 'Wardrobe handles';
    const TYPE_WARDROBE_PROFILE = 'Wardrobe profile handle';
    const TYPE_KNOB = 'Knob handles';
    const TYPE_MAIN_DOOR = 'Main door handles';
    const TYPE_PROFILE = 'Profile handles';

    /**
     * Get the lead
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Generate line key
     */
    public static function generateLineKey(string $number, ?string $product, ?string $model, ?string $size, ?string $finish): string
    {
        return implode('|', [
            trim($number),
            strtolower(trim($product ?? '')),
            strtolower(trim($model ?? '')),
            strtolower(trim($size ?? '')),
            strtolower(trim($finish ?? '')),
        ]);
    }

    /**
     * Boot method to auto-generate line_key
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (!$model->line_key) {
                $model->line_key = self::generateLineKey(
                    $model->number,
                    $model->product,
                    $model->model,
                    $model->size,
                    $model->finish
                );
            }
        });
    }

    /**
     * Check if product is complete (all required fields filled)
     */
    public function isComplete(): bool
    {
        return !empty($this->product)
            && !empty($this->model)
            && !empty($this->size)
            && !empty($this->finish)
            && !empty($this->qty)
            && !empty($this->material)
            && !empty($this->packaging);
    }

    /**
     * Get the next empty field
     */
    public function getNextEmptyField(): ?string
    {
        $fields = ['product', 'model', 'size', 'finish', 'qty', 'packaging', 'material'];

        foreach ($fields as $field) {
            if (empty($this->{$field})) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Get available product types
     */
    public static function getProductTypes(): array
    {
        return [
            self::TYPE_CABINET,
            self::TYPE_WARDROBE,
            self::TYPE_WARDROBE_PROFILE,
            self::TYPE_KNOB,
            self::TYPE_MAIN_DOOR,
            self::TYPE_PROFILE,
        ];
    }

    /**
     * Get product summary
     */
    public function getSummaryAttribute(): string
    {
        $parts = array_filter([
            $this->product,
            $this->model ? "Model: {$this->model}" : null,
            $this->size ? "Size: {$this->size}" : null,
            $this->finish ? "Finish: {$this->finish}" : null,
        ]);

        return implode(' | ', $parts) ?: 'No details';
    }
}
