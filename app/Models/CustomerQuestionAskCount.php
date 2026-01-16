<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerQuestionAskCount extends Model
{
    protected $fillable = [
        'customer_id',
        'questionnaire_node_id',
        'ask_count',
    ];

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the questionnaire node
     */
    public function questionnaireNode(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireNode::class);
    }

    /**
     * Increment count for a customer-node pair
     */
    public static function increment(int $customerId, int $nodeId): void
    {
        $record = self::firstOrCreate(
            [
                'customer_id' => $customerId,
                'questionnaire_node_id' => $nodeId,
            ],
            ['ask_count' => 0]
        );

        $record->increment('ask_count');
    }

    /**
     * Get count for a customer-node pair
     */
    public static function getCount(int $customerId, int $nodeId): int
    {
        return self::where('customer_id', $customerId)
            ->where('questionnaire_node_id', $nodeId)
            ->value('ask_count') ?? 0;
    }

    /**
     * Reset count for a customer-node pair
     */
    public static function resetCount(int $customerId, int $nodeId): void
    {
        self::where('customer_id', $customerId)
            ->where('questionnaire_node_id', $nodeId)
            ->delete();
    }
}
