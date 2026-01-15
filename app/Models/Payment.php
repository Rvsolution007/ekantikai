<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'amount',
        'currency',
        'credits_added',
        'payment_method',
        'transaction_id',
        'payment_gateway_id',
        'invoice_number',
        'status',
        'failure_reason',
        'gateway_response',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'credits_added' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(SuperAdmin::class, 'processed_by');
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Methods
    public function markAsSuccess(): void
    {
        $this->update(['status' => 'success']);

        // Add credits to tenant
        $this->tenant->credits?->addCredits($this->credits_added);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');
        $sequence = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => 'bg-green-100 text-green-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'processing' => 'bg-blue-100 text-blue-700',
            'failed' => 'bg-red-100 text-red-700',
            'refunded' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
