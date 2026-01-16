<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'customer_id',
        'whatsapp_user_id',
        'number',
        'role',
        'content',
        'message_id',
        'whatsapp_message_id',
        'is_reply',
        'reply_to_message_id',
        'reply_to_content',
        'quoted_message_id',
        'quoted_message_text',
        'media_type',
        'media_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_reply' => 'boolean',
    ];

    // Role constants
    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';
    const ROLE_SYSTEM = 'system';

    /**
     * Get the admin (tenant)
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the WhatsApp user
     */
    public function whatsappUser()
    {
        return $this->belongsTo(WhatsappUser::class);
    }

    /**
     * Check if this is a user message
     */
    public function isUserMessage(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if this is a reply to another message
     */
    public function isReply(): bool
    {
        return $this->is_reply || !empty($this->reply_to_message_id) || !empty($this->quoted_message_id);
    }

    /**
     * Get the replied-to message content
     */
    public function getRepliedContent(): ?string
    {
        return $this->reply_to_content ?? $this->quoted_message_text;
    }

    /**
     * Get the quoted message (if exists in DB)
     */
    public function quotedMessage()
    {
        $messageId = $this->reply_to_message_id ?? $this->quoted_message_id;
        if (!$messageId) {
            return null;
        }

        return self::where('whatsapp_message_id', $messageId)
            ->orWhere('message_id', $messageId)
            ->first();
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('h:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        if ($this->created_at->isToday()) {
            return 'Today';
        }
        if ($this->created_at->isYesterday()) {
            return 'Yesterday';
        }
        return $this->created_at->format('M d, Y');
    }

    /**
     * Scope for recent messages
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope for messages by number
     */
    public function scopeForNumber($query, string $number)
    {
        return $query->where('number', $number);
    }

    /**
     * Scope for customer messages
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Get recent conversation for AI context
     */
    public static function getRecentConversation(int $customerId, int $limit = 5): array
    {
        return self::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($chat) {
                return [
                    'role' => $chat->role,
                    'content' => $chat->content,
                ];
            })
            ->values()
            ->toArray();
    }
}
