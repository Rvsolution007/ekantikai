<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappChat extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_user_id',
        'number',
        'role',
        'content',
        'message_id',
        'quoted_message_id',
        'quoted_message_text',
        'media_type',
        'media_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Role constants
    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';
    const ROLE_SYSTEM = 'system';

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
        return !empty($this->quoted_message_id);
    }

    /**
     * Get the quoted message (if exists in DB)
     */
    public function quotedMessage()
    {
        if (!$this->quoted_message_id) {
            return null;
        }

        return self::where('message_id', $this->quoted_message_id)->first();
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
}
