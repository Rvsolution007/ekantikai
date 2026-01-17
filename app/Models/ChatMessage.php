<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'customer_id',
        'role',
        'content',
        'message_type',
        'media_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the customer that owns the message
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
