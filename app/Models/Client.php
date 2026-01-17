<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'admin_id',
        'customer_id',
        'lead_id',
        'name',
        'business_name',
        'gst_number',
        'city',
        'state',
        'phone',
        'email',
        'address',
        'notes',
        'global_fields',
    ];

    protected $casts = [
        'global_fields' => 'array',
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

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'customer_id', 'customer_id');
    }

    /**
     * Get global field value
     */
    public function getGlobalField(string $key, $default = null)
    {
        return $this->global_fields[$key] ?? $default;
    }

    /**
     * Set global field value
     */
    public function setGlobalField(string $key, $value): void
    {
        $fields = $this->global_fields ?? [];
        $fields[$key] = $value;
        $this->global_fields = $fields;
        $this->save();
    }

    /**
     * Create client from lead data
     */
    public static function createFromLead(Lead $lead): self
    {
        $customer = $lead->customer;
        $collectedData = $lead->collected_data ?? [];
        $globalQuestions = $collectedData['global_questions'] ?? [];

        return self::create([
            'admin_id' => $lead->admin_id,
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'name' => $customer->name ?? $globalQuestions['name'] ?? null,
            'phone' => $customer->phone,
            'city' => $globalQuestions['city'] ?? $customer->getGlobalField('city'),
            'global_fields' => array_merge(
                $customer->global_fields ?? [],
                $globalQuestions
            ),
        ]);
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->business_name ?? $this->phone ?? 'Unknown Client';
    }
}
