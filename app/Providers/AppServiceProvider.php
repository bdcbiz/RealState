<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Unit;
use App\Models\Compound;
use App\Models\User;
use App\Models\Company;
use App\Observers\UnitObserver;
use App\Observers\CompoundObserver;
use App\Observers\GlobalModelObserver;

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
        // Specific observers for Units and Compounds (sync data)
        Unit::observe(UnitObserver::class);
        Compound::observe(CompoundObserver::class);

        // Global observer for all other models (update timestamp only)
        User::observe(GlobalModelObserver::class);
        Company::observe(GlobalModelObserver::class);
    }
}
