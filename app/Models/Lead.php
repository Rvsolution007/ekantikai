<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'whatsapp_user_id',
        'customer_id',
        'stage',
        'status',
        'purpose_of_purchase',
        'purpose_asked',
        'city_asked',
        'assigned_to',
        'confirmed_at',
        'sheet_exported_at',
        'lead_score',
        'lead_quality',
        'notes',
        'collected_data',
    ];

    protected $casts = [
        'purpose_asked' => 'boolean',
        'city_asked' => 'boolean',
        'confirmed_at' => 'datetime',
        'sheet_exported_at' => 'datetime',
        'collected_data' => 'array',
    ];

    // Stage constants
    const STAGE_NEW_LEAD = 'New Lead';
    const STAGE_QUALIFIED = 'Qualified';
    const STAGE_CONFIRM = 'Confirm';
    const STAGE_LOSE = 'Lose';

    // Quality constants
    const QUALITY_COLD = 'cold';
    const QUALITY_WARM = 'warm';
    const QUALITY_HOT = 'hot';
    const QUALITY_AT_RISK = 'at_risk';

    /**
     * Get the admin (tenant)
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the WhatsApp user (legacy)
     */
    public function whatsappUser()
    {
        return $this->belongsTo(WhatsappUser::class);
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get assigned admin
     */
    public function assignedAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'assigned_to');
    }

    /**
     * Add or update collected data
     */
    public function addCollectedData(string $key, $value, string $category = 'global_questions'): void
    {
        $data = $this->collected_data ?? [];

        if (!isset($data[$category])) {
            $data[$category] = [];
        }

        $data[$category][$key] = $value;
        $data['last_updated'] = now()->toIso8601String();

        $this->collected_data = $data;
        $this->save();
    }

    /**
     * Add product to collected data
     */
    public function addProductData(array $productData): void
    {
        $data = $this->collected_data ?? [];

        if (!isset($data['products'])) {
            $data['products'] = [];
        }

        $data['products'][] = $productData;
        $data['last_updated'] = now()->toIso8601String();

        $this->collected_data = $data;
        $this->save();
    }

    /**
     * Get contact name (from customer or whatsappUser)
     */
    public function getContactNameAttribute(): string
    {
        return $this->customer?->name ?? $this->whatsappUser?->name ?? 'Unknown';
    }

    /**
     * Get contact phone (from customer or whatsappUser)
     */
    public function getContactPhoneAttribute(): string
    {
        return $this->customer?->phone ?? $this->whatsappUser?->number ?? '';
    }

    /**
     * Get products for this lead
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get followups
     */
    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    /**
     * Get latest followup
     */
    public function latestFollowup()
    {
        return $this->hasOne(Followup::class)->latestOfMany();
    }

    /**
     * Update lead stage
     */
    public function updateStage(string $stage): void
    {
        $data = ['stage' => $stage];

        if ($stage === self::STAGE_CONFIRM) {
            $data['confirmed_at'] = now();
        }

        $this->update($data);
    }

    /**
     * Calculate and update lead score
     */
    public function calculateScore(): void
    {
        $score = 0;

        // Product count
        $productCount = $this->products()->count();
        $score += min($productCount * 10, 30);

        // Has model specified
        if ($this->products()->whereNotNull('model')->exists()) {
            $score += 20;
        }

        // Has size/finish specified
        if ($this->products()->whereNotNull('size')->exists()) {
            $score += 15;
        }
        if ($this->products()->whereNotNull('finish')->exists()) {
            $score += 15;
        }

        // Purpose specified
        if ($this->purpose_of_purchase) {
            $score += 10;
        }

        // City specified
        if ($this->whatsappUser->city) {
            $score += 10;
        }

        // Determine quality
        $quality = self::QUALITY_COLD;
        if ($score >= 70) {
            $quality = self::QUALITY_HOT;
        } elseif ($score >= 40) {
            $quality = self::QUALITY_WARM;
        } elseif ($score < 20) {
            $quality = self::QUALITY_AT_RISK;
        }

        $this->update([
            'lead_score' => min($score, 100),
            'lead_quality' => $quality,
        ]);
    }

    /**
     * Get stage badge color
     */
    public function getStageBadgeColorAttribute(): string
    {
        return match ($this->stage) {
            self::STAGE_NEW_LEAD => 'blue',
            self::STAGE_QUALIFIED => 'yellow',
            self::STAGE_CONFIRM => 'green',
            self::STAGE_LOSE => 'red',
            default => 'gray',
        };
    }

    /**
     * Get quality badge color
     */
    public function getQualityBadgeColorAttribute(): string
    {
        return match ($this->lead_quality) {
            self::QUALITY_HOT => 'red',
            self::QUALITY_WARM => 'yellow',
            self::QUALITY_COLD => 'blue',
            self::QUALITY_AT_RISK => 'gray',
            default => 'gray',
        };
    }
}
