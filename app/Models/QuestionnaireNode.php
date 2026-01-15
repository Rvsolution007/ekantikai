<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireNode extends Model
{
    protected $fillable = [
        'admin_id',
        'node_type',
        'label',
        'config',
        'pos_x',
        'pos_y',
        'questionnaire_field_id',
        'is_active',
        'is_required',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
    ];

    // Node type constants
    const TYPE_START = 'start';
    const TYPE_QUESTION = 'question';
    const TYPE_CONDITION = 'condition';
    const TYPE_ACTION = 'action';
    const TYPE_END = 'end';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function questionnaireField(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireField::class);
    }

    public function outgoingConnections(): HasMany
    {
        return $this->hasMany(QuestionnaireConnection::class, 'source_node_id');
    }

    public function incomingConnections(): HasMany
    {
        return $this->hasMany(QuestionnaireConnection::class, 'target_node_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('node_type', $type);
    }

    // Get the start node for a tenant
    public static function getStartNode(int $tenantId): ?self
    {
        return static::where('admin_id', $tenantId)
            ->where('node_type', self::TYPE_START)
            ->active()
            ->first();
    }

    /**
     * Get the next node based on answer
     */
    public function getNextNode(?string $answer = null): ?self
    {
        $connections = $this->outgoingConnections()->orderBy('priority')->get();

        if ($connections->isEmpty()) {
            return null;
        }

        // For question/condition nodes, check conditions
        if ($answer !== null && in_array($this->node_type, [self::TYPE_QUESTION, self::TYPE_CONDITION])) {
            foreach ($connections as $connection) {
                if ($connection->matchesCondition($answer)) {
                    return $connection->targetNode;
                }
            }
        }

        // Default: return first connection's target (fallback path)
        return $connections->first()->targetNode;
    }

    /**
     * Get config value
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set config value
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
    }

    /**
     * Convert to React Flow node format
     */
    public function toReactFlowNode(): array
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->node_type,
            'position' => [
                'x' => $this->pos_x,
                'y' => $this->pos_y,
            ],
            'data' => [
                'label' => $this->label,
                'config' => $this->config ?? [],
                'fieldId' => $this->questionnaire_field_id,
            ],
        ];
    }

    /**
     * Sync this node to its linked QuestionnaireField
     */
    public function syncToField(): void
    {
        if (!$this->questionnaire_field_id || $this->node_type !== self::TYPE_QUESTION) {
            return;
        }

        $field = $this->questionnaireField;
        if (!$field) {
            return;
        }

        $config = $this->config ?? [];

        $field->update([
            'display_name' => $config['display_name'] ?? $this->label,
            'field_type' => $config['field_type'] ?? 'text',
            'is_required' => $config['is_required'] ?? false,
            'is_unique_key' => $config['is_unique_key'] ?? false,
            'options_manual' => $config['options'] ?? null,
        ]);
    }

    /**
     * Sync from linked QuestionnaireField
     */
    public function syncFromField(): void
    {
        if (!$this->questionnaire_field_id) {
            return;
        }

        $field = $this->questionnaireField;
        if (!$field) {
            return;
        }

        $this->label = $field->display_name;
        $this->config = array_merge($this->config ?? [], [
            'field_name' => $field->field_name,
            'display_name' => $field->display_name,
            'field_type' => $field->field_type,
            'is_required' => $field->is_required,
            'is_unique_key' => $field->is_unique_key,
            'options' => $field->options_manual,
            'options_source' => $field->options_source,
            'catalogue_field' => $field->catalogue_field,
        ]);
        $this->save();
    }
}
