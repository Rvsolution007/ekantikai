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
        'global_question_id',   // NEW: Link to global question
        'lead_status_id',       // NEW: Lead status to set when answered
        'is_active',
        'is_required',
        'ask_digit',
        'is_unique_field',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'is_unique_field' => 'boolean',
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
        return $this->belongsTo(ProductQuestion::class);
    }

    // NEW: Relationship to GlobalQuestion
    public function globalQuestion(): BelongsTo
    {
        return $this->belongsTo(GlobalQuestion::class);
    }

    // NEW: Relationship to LeadStatus
    public function leadStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class);
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
                'globalQuestionId' => $this->global_question_id,
                'leadStatusId' => $this->lead_status_id,
                'isRequired' => $this->is_required,
                'askDigit' => $this->ask_digit,
                'isUniqueField' => $this->is_unique_field,
            ],
        ];
    }

    /**
     * Check if this is an optional question
     */
    public function isOptional(): bool
    {
        return !$this->is_required && $this->node_type === self::TYPE_QUESTION;
    }

    /**
     * Get ask digit value (how many times to ask optional question)
     */
    public function getAskDigit(): int
    {
        return $this->ask_digit ?? 0;
    }

    /**
     * Check if should ask optional question to customer
     */
    public function shouldAskOptional(Customer $customer): bool
    {
        if (!$this->isOptional()) {
            return true; // Required questions always asked
        }

        $askDigit = $this->getAskDigit();
        if ($askDigit === 0) {
            return true; // 0 means ask unlimited times until answered
        }

        // Check how many times this was asked
        $askCount = CustomerQuestionAskCount::where('customer_id', $customer->id)
            ->where('questionnaire_node_id', $this->id)
            ->value('ask_count') ?? 0;

        return $askCount < $askDigit;
    }

    /**
     * Increment ask count for optional question
     */
    public function incrementAskCount(Customer $customer): void
    {
        CustomerQuestionAskCount::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'questionnaire_node_id' => $this->id,
            ],
            ['ask_count' => \DB::raw('ask_count + 1')]
        );
    }

    /**
     * Sync this node to its linked ProductQuestion
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
     * Sync from linked ProductQuestion
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

    /**
     * Get the minimum allowed status ID based on previous nodes
     * Nodes in sequence can only have same or higher status
     */
    public function getMinAllowedStatusId(): ?int
    {
        // Get all nodes that come before this one (have connection to this)
        $previousNodes = self::where('admin_id', $this->admin_id)
            ->whereHas('outgoingConnections', function ($q) {
                $q->where('target_node_id', $this->id);
            })
            ->whereNotNull('lead_status_id')
            ->get();

        if ($previousNodes->isEmpty()) {
            return null; // No restriction
        }

        // Find the maximum status ID from previous nodes
        return $previousNodes->max('lead_status_id');
    }

    /**
     * Validate if a status ID can be set on this node
     * Returns [valid, message]
     */
    public function validateStatusId(?int $statusId): array
    {
        if ($statusId === null) {
            return [true, null];
        }

        $minAllowed = $this->getMinAllowedStatusId();

        if ($minAllowed !== null && $statusId < $minAllowed) {
            $minStatus = LeadStatus::find($minAllowed);
            return [
                false,
                "Warning: Previous node has status '{$minStatus->name}'. This node should have same or higher status."
            ];
        }

        return [true, null];
    }

    /**
     * Get connection status for SuperAdmin dashboard
     */
    public function getConnectionStatus(): array
    {
        return [
            'node_id' => $this->id,
            'label' => $this->label,
            'node_type' => $this->node_type,
            'product_question' => [
                'connected' => $this->questionnaire_field_id !== null,
                'id' => $this->questionnaire_field_id,
                'field_name' => $this->questionnaireField?->field_name,
            ],
            'global_question' => [
                'connected' => $this->global_question_id !== null,
                'id' => $this->global_question_id,
                'field_name' => $this->globalQuestion?->field_name,
            ],
            'lead_status' => [
                'connected' => $this->lead_status_id !== null,
                'id' => $this->lead_status_id,
                'name' => $this->leadStatus?->name,
            ],
            'outgoing_connections' => $this->outgoingConnections->count(),
            'incoming_connections' => $this->incomingConnections->count(),
            'is_required' => $this->is_required,
        ];
    }
}
