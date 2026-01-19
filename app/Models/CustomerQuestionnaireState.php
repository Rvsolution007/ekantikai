<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerQuestionnaireState extends Model
{
    protected $table = 'customer_questionnaire_state';

    protected $fillable = [
        'admin_id',
        'customer_id',
        'current_field',
        'current_product_index',
        'completed_fields',
        'pending_items',
        'city_asked',
        'purpose_asked',
        'current_node_id',
        'workflow_data',
        'skipped_optional_fields',
    ];

    protected $casts = [
        'completed_fields' => 'array',
        'pending_items' => 'array',
        'city_asked' => 'boolean',
        'purpose_asked' => 'boolean',
        'workflow_data' => 'array',
        'skipped_optional_fields' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireNode::class, 'current_node_id');
    }

    // Get completed field value
    public function getCompletedField(string $key, $default = null)
    {
        return $this->completed_fields[$key] ?? $default;
    }

    // Set completed field value
    public function setCompletedField(string $key, $value): void
    {
        $fields = $this->completed_fields ?? [];
        $fields[$key] = $value;
        $this->completed_fields = $fields;
        $this->save();
    }

    // Clear completed fields (start fresh)
    public function clearCompletedFields(): void
    {
        $this->completed_fields = [];
        $this->current_field = null;
        $this->save();
    }

    // Add pending item (save completed product)
    public function addPendingItem(array $item): void
    {
        $items = $this->pending_items ?? [];
        $items[] = $item;
        $this->pending_items = $items;
        $this->save();
    }

    // Get next empty required field
    public function getNextField(int $tenantId): ?ProductQuestion
    {
        $fields = ProductQuestion::where('admin_id', $tenantId)
            ->active()
            ->ordered()
            ->get();

        foreach ($fields as $field) {
            // Skip if already completed
            if (!empty($this->getCompletedField($field->field_name))) {
                continue;
            }

            // If required, ask it
            if ($field->is_required) {
                return $field;
            }
        }

        return null;
    }

    // Check if all required fields are completed
    public function isComplete(int $tenantId): bool
    {
        return $this->getNextField($tenantId) === null;
    }

    // Reset state for new product
    public function reset(): void
    {
        $this->completed_fields = [];
        $this->current_field = null;
        $this->current_product_index = 0;
        $this->save();
    }
}
