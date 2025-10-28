<?php

namespace App\Observers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Automatically assign free plan to new users
        $freePlan = SubscriptionPlan::getFreePlan();

        if ($freePlan && $user->role !== 'admin') {
            $startedAt = now();
            $expiresAt = $freePlan->validity_days > 0
                ? $startedAt->copy()->addDays($freePlan->validity_days)
                : null;

            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $freePlan->id,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'searches_used' => 0,
                'status' => 'active',
                'notes' => 'تفعيل تلقائي للباقة المجانية',
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
