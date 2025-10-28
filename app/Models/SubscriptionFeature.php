<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionFeature extends Model
{
    protected $fillable = [
        'feature',
        'feature_en',
        'value',
        'value_en',
        'sort_order',
    ];

    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'feature_subscription_plan')
            ->withPivot('is_included')
            ->withTimestamps();
    }
}
