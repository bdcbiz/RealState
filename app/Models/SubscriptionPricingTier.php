<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPricingTier extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'type',
        'name',
        'name_en',
        'percentage',
        'fixed_fee',
        'sort_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
    ];

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
