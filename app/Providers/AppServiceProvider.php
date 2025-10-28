<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Unit;
use App\Models\Compound;
use App\Models\Sale;
use App\Models\User;
use App\Observers\UnitObserver;
use App\Observers\CompoundObserver;
use App\Observers\SaleObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for FCM notifications
        Unit::observe(UnitObserver::class);
        Compound::observe(CompoundObserver::class);
        Sale::observe(SaleObserver::class);

        // Register User observer for auto subscription
        User::observe(UserObserver::class);
    }
}
