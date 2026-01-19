<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowupTemplate extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'message_template',
        'delay_minutes',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the admin (tenant)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get available dynamic fields for templates
     * These come from required fields in questionnaire
     */
    public static function getAvailableFields(int $adminId): array
    {
        $fields = ProductQuestion::where('admin_id', $adminId)
            ->where('is_required', true)
            ->pluck('display_name', 'field_name')
            ->toArray();

        // Add standard fields
        return array_merge([
            'customer_name' => 'Customer Name',
            'customer_phone' => 'Customer Phone',
            'lead_stage' => 'Lead Stage',
        ], $fields);
    }

    /**
     * Render the template with actual values
     */
    public function render(Lead $lead): string
    {
        $message = $this->message_template;
        $customer = $lead->customer;
        $collectedData = $lead->collected_data ?? [];

        // Replace standard fields
        $replacements = [
            '{customer_name}' => $customer?->name ?? 'Customer',
            '{customer_phone}' => $customer?->phone ?? '',
            '{lead_stage}' => $lead->stage ?? '',
        ];

        // Replace global question fields
        $globalQuestions = $collectedData['global_questions'] ?? [];
        foreach ($globalQuestions as $fieldName => $value) {
            $replacements['{' . $fieldName . '}'] = is_array($value) ? implode(', ', $value) : $value;
        }

        // Replace product fields
        $products = $collectedData['products'] ?? [];
        if (!empty($products)) {
            $lastProduct = end($products);
            foreach ($lastProduct as $key => $value) {
                $replacements['{product_' . $key . '}'] = is_array($value) ? implode(', ', $value) : $value;
            }
        }

        // Perform replacements
        foreach ($replacements as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }

        // Remove any unreplaced placeholders
        $message = preg_replace('/\{[^}]+\}/', '', $message);

        return trim($message);
    }

    /**
     * Preview template with sample data
     */
    public function preview(): string
    {
        $message = $this->message_template;

        // Replace with sample data
        $samples = [
            '{customer_name}' => 'John Doe',
            '{customer_phone}' => '+91 98765 43210',
            '{lead_stage}' => 'Qualified',
        ];

        foreach ($samples as $placeholder => $value) {
            $message = str_replace($placeholder, $value, $message);
        }

        // Replace remaining with placeholders shown
        $message = preg_replace('/\{([^}]+)\}/', '[$1]', $message);

        return $message;
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
