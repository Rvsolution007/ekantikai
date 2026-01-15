<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireConnection extends Model
{
    protected $fillable = [
        'admin_id',
        'source_node_id',
        'target_node_id',
        'source_handle',
        'target_handle',
        'condition',
        'priority',
        'label',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    // Operators for conditions
    const OPERATOR_EQUALS = 'equals';
    const OPERATOR_NOT_EQUALS = 'not_equals';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_LESS = 'less';
    const OPERATOR_DEFAULT = 'default'; // Always matches (fallback)

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function sourceNode(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireNode::class, 'source_node_id');
    }

    public function targetNode(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireNode::class, 'target_node_id');
    }

    /**
     * Check if the given answer matches this connection's condition
     */
    public function matchesCondition(?string $answer): bool
    {
        $condition = $this->condition;

        // No condition = default path (matches everything)
        if (empty($condition) || !isset($condition['operator'])) {
            return true;
        }

        $operator = $condition['operator'] ?? self::OPERATOR_DEFAULT;
        $value = $condition['value'] ?? '';

        switch ($operator) {
            case self::OPERATOR_EQUALS:
                return strtolower(trim($answer)) === strtolower(trim($value));

            case self::OPERATOR_NOT_EQUALS:
                return strtolower(trim($answer)) !== strtolower(trim($value));

            case self::OPERATOR_CONTAINS:
                return stripos($answer, $value) !== false;

            case self::OPERATOR_GREATER:
                return is_numeric($answer) && is_numeric($value) && floatval($answer) > floatval($value);

            case self::OPERATOR_LESS:
                return is_numeric($answer) && is_numeric($value) && floatval($answer) < floatval($value);

            case self::OPERATOR_DEFAULT:
            default:
                return true;
        }
    }

    /**
     * Convert to React Flow edge format
     */
    public function toReactFlowEdge(): array
    {
        return [
            'id' => (string) $this->id,
            'source' => (string) $this->source_node_id,
            'target' => (string) $this->target_node_id,
            'sourceHandle' => $this->source_handle,
            'targetHandle' => $this->target_handle,
            'label' => $this->label,
            'data' => [
                'condition' => $this->condition,
                'priority' => $this->priority,
            ],
            'type' => $this->condition ? 'conditional' : 'default',
        ];
    }

    /**
     * Create from React Flow edge data
     */
    public static function createFromReactFlow(int $tenantId, array $edgeData): self
    {
        return static::create([
            'admin_id' => $tenantId,
            'source_node_id' => (int) $edgeData['source'],
            'target_node_id' => (int) $edgeData['target'],
            'source_handle' => $edgeData['sourceHandle'] ?? null,
            'target_handle' => $edgeData['targetHandle'] ?? null,
            'label' => $edgeData['label'] ?? null,
            'condition' => $edgeData['data']['condition'] ?? null,
            'priority' => $edgeData['data']['priority'] ?? 0,
        ]);
    }
}
