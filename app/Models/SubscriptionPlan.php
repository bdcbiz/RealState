<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'description',
        'description_en',
        'monthly_price',
        'yearly_price',
        'max_users',
        'icon',
        'color',
        'badge',
        'badge_en',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionFeature::class, 'feature_subscription_plan')
            ->withPivot('is_included')
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(SubscriptionPricingTier::class)->orderBy('sort_order');
    }
}
