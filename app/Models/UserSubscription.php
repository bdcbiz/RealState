<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'started_at',
        'expires_at',
        'searches_used',
        'status',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'searches_used' => 'integer',
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription plan
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Check if the subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if the subscription has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if user can search (has remaining searches)
     */
    public function canSearch(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $searchLimit = $this->subscriptionPlan->search_limit;

        // -1 means unlimited
        if ($searchLimit === -1) {
            return true;
        }

        return $this->searches_used < $searchLimit;
    }

    /**
     * Get remaining searches
     */
    public function getRemainingSearches(): int
    {
        $searchLimit = $this->subscriptionPlan->search_limit;

        // -1 means unlimited
        if ($searchLimit === -1) {
            return -1;
        }

        return max(0, $searchLimit - $this->searches_used);
    }

    /**
     * Increment search count
     */
    public function incrementSearch(): void
    {
        $this->increment('searches_used');
    }

    /**
     * Mark subscription as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark subscription as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }
}
