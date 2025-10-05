<?php

namespace App\Observers;

use App\Models\Unit;

class UnitObserver
{
    /**
     * Handle the Unit "created" event.
     */
    public function created(Unit $unit): void
    {
        // Add to units_availability when created
        $this->syncToUnitsAvailability($unit);
    }

    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit): void
    {
        // Update units_availability table
        $this->syncToUnitsAvailability($unit);

        // Check if is_sold was changed
        if ($unit->isDirty('is_sold')) {
            $this->updateCompoundStatus($unit);
        }
    }

    /**
     * Sync unit data to units_availability table
     */
    private function syncToUnitsAvailability(Unit $unit): void
    {
        \App\Models\UnitsAvailability::updateOrCreate(
            ['unit_name' => $unit->unit_name],
            [
                'project' => $unit->compound->project ?? null,
                'usage_type' => $unit->usage_type,
                'bua' => $unit->building_name,
                'garden_area' => $unit->garden_area,
                'roof_area' => $unit->roof_area,
                'floor' => $unit->floor_number,
                'no__of_bedrooms' => $unit->number_of_beds,
                'nominal_price' => $unit->normal_price,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Update compound status based on units
     */
    private function updateCompoundStatus(Unit $unit): void
    {
        $compound = $unit->compound;

        if (!$compound) {
            return;
        }

        // Check if all units are sold
        $totalUnits = $compound->units()->count();
        $soldUnits = $compound->units()->where('is_sold', true)->count();

        // Update compound is_sold status
        if ($totalUnits > 0 && $soldUnits === $totalUnits) {
            $compound->update(['is_sold' => true]);
        } else {
            $compound->update(['is_sold' => false]);
        }
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit): void
    {
        //
    }

    /**
     * Handle the Unit "restored" event.
     */
    public function restored(Unit $unit): void
    {
        //
    }

    /**
     * Handle the Unit "force deleted" event.
     */
    public function forceDeleted(Unit $unit): void
    {
        //
    }
}
