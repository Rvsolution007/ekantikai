<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProductQuestion extends Model
{
    protected $table = 'product_questions';

    protected $fillable = [
        'admin_id',
        'field_name',
        'display_name',
        'question_template', // Custom question format for bot to ask
        'field_type',
        'is_required',
        'sort_order',
        'is_unique_key',
        'unique_key_order',
        'is_unique_field', // For identifying unique products like Model Number
        'is_qty_field', // For qty input fields - always ask for input
        'options_source',
        'options_manual',
        'catalogue_field',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_unique_key' => 'boolean',
        'is_unique_field' => 'boolean',
        'is_qty_field' => 'boolean',
        'options_manual' => 'array',
        'validation_rules' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        // Auto-sync to CatalogueField when ProductQuestion is saved
        static::saved(function ($productQuestion) {
            $productQuestion->syncToCatalogueField();
        });

        // Delete linked CatalogueField when ProductQuestion is deleted
        static::deleted(function ($productQuestion) {
            // Remove the linked CatalogueField
            CatalogueField::where('product_question_id', $productQuestion->id)->delete();

            // Also remove by field_name if linked via field_key
            CatalogueField::where('admin_id', $productQuestion->admin_id)
                ->where('field_key', \Illuminate\Support\Str::snake(\Illuminate\Support\Str::lower($productQuestion->field_name)))
                ->delete();
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    // Relationship to CatalogueField
    public function catalogueFieldRecord(): HasOne
    {
        return $this->hasOne(CatalogueField::class, 'product_question_id');
    }

    // Scope for active fields
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for unique key fields (ordered)
    public function scopeUniqueKeyFields($query)
    {
        return $query->where('is_unique_key', true)
            ->orderBy('unique_key_order');
    }

    // Scope ordered by sort_order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Get options based on source
    public function getOptions(): array
    {
        if ($this->options_source === 'manual') {
            return $this->options_manual ?? [];
        }

        // For catalogue source, fetch from products
        if ($this->options_source === 'catalogue' && $this->catalogue_field) {
            return $this->getOptionsFromCatalogue();
        }

        return [];
    }

    protected function getOptionsFromCatalogue(): array
    {
        // Get unique values from products/catalogues for this field
        $field = $this->catalogue_field;

        return Catalogue::where('admin_id', $this->admin_id)
            ->where('is_active', true)
            ->whereNotNull("data->{$field}")
            ->get()
            ->pluck("data.{$field}")
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get filtered options based on previously collected data (cascading filter)
     * 
     * @param array $collectedData Previous answers to filter by
     * @return array Filtered options for this field
     */
    public function getFilteredOptions(array $collectedData = []): array
    {
        if ($this->options_source === 'manual') {
            return $this->options_manual ?? [];
        }

        if ($this->options_source !== 'catalogue' || !$this->catalogue_field) {
            return [];
        }

        $field = $this->catalogue_field;

        $query = Catalogue::where('admin_id', $this->admin_id)
            ->where('is_active', true);

        // Apply filters from previously collected data
        foreach ($collectedData as $fieldName => $value) {
            if (!empty($value)) {
                $query->where("data->{$fieldName}", $value);
            }
        }

        return $query->whereNotNull("data->{$field}")
            ->get()
            ->pluck("data.{$field}")
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Sync this ProductQuestion to CatalogueField
     * Creates or updates the linked CatalogueField with same field_name
     */
    public function syncToCatalogueField(): void
    {
        $fieldKey = Str::snake(Str::lower($this->field_name));

        // First try to find existing CatalogueField by product_question_id
        $catalogueField = CatalogueField::where('admin_id', $this->admin_id)
            ->where('product_question_id', $this->id)
            ->first();

        // If not found, try by field_key (for existing fields not yet linked)
        if (!$catalogueField) {
            $catalogueField = CatalogueField::where('admin_id', $this->admin_id)
                ->where('field_key', $fieldKey)
                ->first();
        }

        $data = [
            'admin_id' => $this->admin_id,
            'product_question_id' => $this->id,
            'field_name' => $this->field_name,
            'field_key' => $fieldKey,
            'field_type' => $this->mapFieldType(),
            'is_required' => $this->is_required,
            'is_unique' => $this->is_unique_key,
            'sort_order' => $this->sort_order,
            'options' => $this->options_manual,
        ];

        if ($catalogueField) {
            // Update existing
            $catalogueField->update($data);
        } else {
            // Create new
            CatalogueField::create($data);
        }
    }

    /**
     * Map ProductQuestion field_type to CatalogueField field_type
     */
    private function mapFieldType(): string
    {
        return match ($this->field_type) {
            'number', 'integer' => 'number',
            'select', 'dropdown' => 'select',
            default => 'text',
        };
    }

    /**
     * Check if this field has value in given data
     */
    public function hasValueIn(array $data): bool
    {
        $value = $data[$this->field_name] ?? null;
        return $value !== null && $value !== '';
    }

    /**
     * Get connection status for SuperAdmin dashboard
     */
    public function getConnectionStatus(): array
    {
        $catalogueField = $this->catalogueFieldRecord;
        $flowchartNode = QuestionnaireNode::where('questionnaire_field_id', $this->id)->first();

        return [
            'field_name' => $this->field_name,
            'display_name' => $this->display_name,
            'catalogue_field' => [
                'connected' => $catalogueField !== null,
                'field_key' => $catalogueField?->field_key,
            ],
            'flowchart_node' => [
                'connected' => $flowchartNode !== null,
                'node_id' => $flowchartNode?->id,
                'node_label' => $flowchartNode?->label,
            ],
            'has_options' => $this->options_source !== null,
            'options_source' => $this->options_source,
            'is_unique_key' => $this->is_unique_key,
            'is_unique_field' => $this->is_unique_field,
            'is_qty_field' => $this->is_qty_field,
        ];
    }
}

