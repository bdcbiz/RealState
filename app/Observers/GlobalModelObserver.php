<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesAvailability;
use App\Models\UnitsAvailability;

class GlobalModelObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->updateAvailabilityTables();
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->updateAvailabilityTables();
    }

    /**
     * Update all rows in availability tables with current timestamp
     */
    private function updateAvailabilityTables(): void
    {
        $now = now();

        // Update all rows in sales_availability
        SalesAvailability::query()->update(['updated_at' => $now]);

        // Update all rows in units_availability
        UnitsAvailability::query()->update(['updated_at' => $now]);
    }
}
