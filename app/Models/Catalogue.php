<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogue extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'data',
        'category',
        'product_type',
        'model_code',
        'sizes',
        'pack_per_size',
        'finishes',
        'material',
        'image_url',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'data' => 'array',
    ];

    /**
     * Get the tenant
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Product type constants (same as Product model)
    const TYPE_CABINET = 'Cabinet handles';
    const TYPE_WARDROBE = 'Wardrobe handles';
    const TYPE_WARDROBE_PROFILE = 'Wardrobe profile handle';
    const TYPE_KNOB = 'Knob handles';
    const TYPE_MAIN_DOOR = 'Main door handles';
    const TYPE_PROFILE = 'Profile handles';

    /**
     * Model ranges per product type
     */
    public static function getModelRanges(): array
    {
        return [
            self::TYPE_WARDROBE => ['252', '253', '9001', '9002', '9004', '9005', '9007', '9008', '9009', '9010', '9011', '9018', '9019', '9020', '9021', '9023', '9024', '9025', '9026', '9027', '9028', '9029', '9030', '9031', '9032', '9033', '9034', '9035', '9036', '9037', '9038', '9039', '9040', '9041', '9042', '9043', '9044', '9045', '9046', '9047', '9048', '9049', '9050', '9051', '9052', '9053'],
            self::TYPE_WARDROBE_PROFILE => ['05', '022'],
            self::TYPE_KNOB => ['401', '402', '404', '406', '407', '408', '409', '410', '412', '413', '414', '415', '416', '417', '418', '419', '422', '9014', '9017', '9034', '9035'],
            self::TYPE_MAIN_DOOR => ['90', '91', '92', '93', '94', '95', '96', '97', '98', '99', '100'],
            self::TYPE_CABINET => ['007', '008', '009', '0010', '0011', '0012', '0013', '0014', '0015'],
            self::TYPE_PROFILE => ['09', '016', '028', '029', '031', '032', '033', '034', '034 BS', '035 BS', '039'],
        ];
    }

    /**
     * Detect product type from model code
     */
    public static function detectProductType(string $modelCode): ?string
    {
        $modelCode = trim($modelCode);
        $ranges = self::getModelRanges();

        foreach ($ranges as $productType => $models) {
            if (in_array($modelCode, $models)) {
                return $productType;
            }
        }

        return null;
    }

    /**
     * Check if model exists in catalogue
     */
    public static function modelExists(string $modelCode): bool
    {
        return self::where('model_code', $modelCode)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get sizes as array
     */
    public function getSizesArrayAttribute(): array
    {
        if (empty($this->sizes)) {
            return [];
        }
        return array_map('trim', explode(',', $this->sizes));
    }

    /**
     * Get finishes as array
     */
    public function getFinishesArrayAttribute(): array
    {
        if (empty($this->finishes)) {
            return [];
        }
        return array_map('trim', explode(',', $this->finishes));
    }

    /**
     * Scope for active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for product type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope for model code
     */
    public function scopeOfModel($query, string $modelCode)
    {
        return $query->where('model_code', $modelCode);
    }

    /**
     * Get image URL or placeholder
     */
    public function getImageUrlOrPlaceholderAttribute(): string
    {
        if ($this->image_url) {
            return $this->image_url;
        }
        return 'https://via.placeholder.com/300x200?text=' . urlencode($this->product_type);
    }
}
