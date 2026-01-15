<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'trigger_type',
        'trigger_conditions',
        'actions',
        'schedule_cron',
        'next_run_at',
        'last_run_at',
        'is_active',
        'execution_count',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfTrigger($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->where('trigger_type', 'schedule')
            ->whereNotNull('schedule_cron');
    }

    public function scopeDue($query)
    {
        return $query->scheduled()
            ->active()
            ->where('next_run_at', '<=', now());
    }

    // Methods
    public function shouldTrigger(array $context): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $conditions = $this->trigger_conditions ?? [];

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? null;

        $contextValue = data_get($context, $field);

        return match ($operator) {
            'equals' => $contextValue == $value,
            'not_equals' => $contextValue != $value,
            'contains' => str_contains(strtolower($contextValue), strtolower($value)),
            'not_contains' => !str_contains(strtolower($contextValue), strtolower($value)),
            'starts_with' => str_starts_with(strtolower($contextValue), strtolower($value)),
            'ends_with' => str_ends_with(strtolower($contextValue), strtolower($value)),
            'greater_than' => $contextValue > $value,
            'less_than' => $contextValue < $value,
            'in' => in_array($contextValue, (array) $value),
            'not_in' => !in_array($contextValue, (array) $value),
            default => false,
        };
    }

    public function execute(array $context = []): void
    {
        $this->increment('execution_count');
        $this->update(['last_run_at' => now()]);

        foreach ($this->actions as $action) {
            $this->executeAction($action, $context);
        }

        // Schedule next run for scheduled workflows
        if ($this->trigger_type === 'schedule' && $this->schedule_cron) {
            $this->scheduleNextRun();
        }
    }

    protected function executeAction(array $action, array $context): void
    {
        $type = $action['type'] ?? null;

        match ($type) {
            'send_message' => $this->actionSendMessage($action, $context),
            'update_lead_stage' => $this->actionUpdateLeadStage($action, $context),
            'assign_to_agent' => $this->actionAssignAgent($action, $context),
            'add_tag' => $this->actionAddTag($action, $context),
            'webhook' => $this->actionWebhook($action, $context),
            default => null,
        };
    }

    protected function actionSendMessage(array $action, array $context): void
    {
        // TODO: Implement send message action
    }

    protected function actionUpdateLeadStage(array $action, array $context): void
    {
        // TODO: Implement lead stage update
    }

    protected function actionAssignAgent(array $action, array $context): void
    {
        // TODO: Implement agent assignment
    }

    protected function actionAddTag(array $action, array $context): void
    {
        // TODO: Implement tag addition
    }

    protected function actionWebhook(array $action, array $context): void
    {
        // TODO: Implement webhook call
    }

    public function scheduleNextRun(): void
    {
        // Simple cron-like scheduling
        $this->update([
            'next_run_at' => now()->addHours(1), // Simplified, use cron parser in production
        ]);
    }

    // Trigger types
    public static function getTriggerTypes(): array
    {
        return [
            'message_received' => 'When message is received',
            'keyword' => 'When keyword detected',
            'schedule' => 'On schedule',
            'lead_stage' => 'When lead stage changes',
            'manual' => 'Manual trigger',
        ];
    }

    // Action types
    public static function getActionTypes(): array
    {
        return [
            'send_message' => 'Send WhatsApp Message',
            'update_lead_stage' => 'Update Lead Stage',
            'assign_to_agent' => 'Assign to Team Member',
            'add_tag' => 'Add Tag',
            'webhook' => 'Call Webhook URL',
        ];
    }
}
