<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'payment_gateway_id',
        'user_id',
        'transaction_id',
        'gateway_transaction_id',
        'gateway_reference',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'status',
        'payment_method',
        'description',
        'failure_reason',
        'request_data',
        'response_data',
        'callback_data',
        'customer_data',
        'redirect_url',
        'callback_url',
        'paid_at',
        'failed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'request_data' => 'array',
        'response_data' => 'array',
        'callback_data' => 'array',
        'customer_data' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = self::generateTransactionId();
            }
            if (empty($transaction->net_amount)) {
                $transaction->net_amount = $transaction->amount - $transaction->fee;
            }
        });
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        do {
            $id = 'TXN-' . strtoupper(Str::random(12));
        } while (self::where('transaction_id', $id)->exists());

        return $id;
    }

    /**
     * Polymorphic relationship to payable (Order, Invoice, etc)
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Payment gateway relationship
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark transaction as success
     */
    public function markAsSuccess(array $data = []): bool
    {
        $updated = $this->update([
            'status' => 'success',
            'paid_at' => now(),
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);

        if ($updated) {
            $this->paymentGateway->incrementTransactions($this->amount);
        }

        return $updated;
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(?string $reason = null, array $data = []): bool
    {
        $updated = $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);

        if ($updated) {
            $this->paymentGateway->incrementFailed();
        }

        return $updated;
    }

    /**
     * Mark transaction as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update(['status' => 'processing']);
    }

    /**
     * Mark transaction as refunded
     */
    public function markAsRefunded(array $data = []): bool
    {
        return $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'response_data' => array_merge($this->response_data ?? [], $data),
        ]);
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope for successful transactions
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific gateway
     */
    public function scopeForGateway($query, $gatewaySlug)
    {
        return $query->whereHas('paymentGateway', function ($q) use ($gatewaySlug) {
            $q->where('slug', $gatewaySlug);
        });
    }
}
