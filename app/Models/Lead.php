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
        'lead_status_id',
        'status',
        'bot_active',
        'completed_all_questions',
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
        'product_confirmations',
        'detected_language',
    ];

    protected $casts = [
        'purpose_asked' => 'boolean',
        'city_asked' => 'boolean',
        'bot_active' => 'boolean',
        'completed_all_questions' => 'boolean',
        'confirmed_at' => 'datetime',
        'sheet_exported_at' => 'datetime',
        'collected_data' => 'array',
        'product_confirmations' => 'array',
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
     * Get custom lead status
     */
    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class);
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
     * Add product confirmation from AI extraction
     */
    public function addProductConfirmation(array $productData): void
    {
        $confirmations = $this->product_confirmations ?? [];
        $confirmations[] = array_merge($productData, [
            'confirmed_at' => now()->toIso8601String(),
        ]);
        $this->product_confirmations = $confirmations;
        $this->save();
    }

    /**
     * Update product confirmation (modify existing or remove)
     */
    public function updateProductConfirmation(int $index, ?array $productData): void
    {
        $confirmations = $this->product_confirmations ?? [];

        if ($productData === null) {
            // Remove the confirmation
            unset($confirmations[$index]);
            $confirmations = array_values($confirmations);
        } else {
            // Update the confirmation
            $confirmations[$index] = array_merge($confirmations[$index] ?? [], $productData, [
                'updated_at' => now()->toIso8601String(),
            ]);
        }

        $this->product_confirmations = $confirmations;
        $this->save();
    }

    /**
     * Get product confirmations for AI context
     */
    public function getProductConfirmationsForAI(): array
    {
        return $this->product_confirmations ?? [];
    }

    /**
     * Update lead from AI response
     */
    public function updateFromAI(array $aiData): void
    {
        $updates = [];

        // Update stage if provided
        if (isset($aiData['stage']) && $aiData['stage']) {
            $updates['stage'] = $aiData['stage'];
        }

        // Update lead status if provided
        if (isset($aiData['lead_status_id'])) {
            $updates['lead_status_id'] = $aiData['lead_status_id'];
        }

        // Update detected language
        if (isset($aiData['language'])) {
            $updates['detected_language'] = $aiData['language'];
        }

        // Add extracted data
        if (isset($aiData['extracted_data']) && is_array($aiData['extracted_data'])) {
            foreach ($aiData['extracted_data'] as $key => $value) {
                $this->addCollectedData($key, $value);
            }
        }

        // Add product confirmations
        if (isset($aiData['product_confirmations']) && is_array($aiData['product_confirmations'])) {
            foreach ($aiData['product_confirmations'] as $product) {
                $this->addProductConfirmation($product);
            }
        }

        if (!empty($updates)) {
            $this->update($updates);
        }

        // Recalculate score
        $this->calculateScore();
    }

    /**
     * Mark bot as completed (all required questions answered)
     */
    public function markBotComplete(): void
    {
        $this->update([
            'completed_all_questions' => true,
            'bot_active' => false,
        ]);
    }

    /**
     * Check if all required questions have been answered
     */
    public function checkRequiredQuestionsComplete(): bool
    {
        $requiredNodes = QuestionnaireNode::where('admin_id', $this->admin_id)
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        $collectedData = $this->collected_data['global_questions'] ?? [];

        foreach ($requiredNodes as $node) {
            $field = $node->questionnaireField;
            if ($field && !isset($collectedData[$field->field_name])) {
                return false;
            }
        }

        return true;
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
     * Update lead status (custom status)
     */
    public function updateLeadStatus(int $statusId): void
    {
        $this->update(['lead_status_id' => $statusId]);
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

        // City specified (from customer or whatsappUser)
        if ($this->customer?->getGlobalField('city') || $this->whatsappUser?->city) {
            $score += 10;
        }

        // Product confirmations from AI
        $confirmationCount = count($this->product_confirmations ?? []);
        $score += min($confirmationCount * 5, 20);

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

    /**
     * Get bot active badge for display
     */
    public function getBotActiveBadgeAttribute(): string
    {
        return $this->bot_active ? 'Active' : 'Inactive';
    }

    /**
     * Scope for leads with active bot
     */
    public function scopeBotActive($query)
    {
        return $query->where('bot_active', true);
    }

    /**
     * Scope for open leads
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}

