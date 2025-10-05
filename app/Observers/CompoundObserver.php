<?php

namespace App\Observers;

use App\Models\Compound;

class CompoundObserver
{
    /**
     * Handle the Compound "created" event.
     */
    public function created(Compound $compound): void
    {
        // Add to sales_availability when created
        $this->syncToSalesAvailability($compound);
    }

    /**
     * Handle the Compound "updated" event.
     */
    public function updated(Compound $compound): void
    {
        // Update sales_availability table
        $this->syncToSalesAvailability($compound);
    }

    /**
     * Sync compound data to sales_availability table
     */
    private function syncToSalesAvailability(Compound $compound): void
    {
        // Update all units of this compound in sales_availability
        foreach ($compound->units as $unit) {
            \App\Models\SalesAvailability::updateOrCreate(
                ['unit_code' => $unit->unit_code],
                [
                    'project' => $compound->project,
                    'stage' => $compound->stage,
                    'category' => $unit->unit_type,
                    'unit_type' => $unit->unit_type,
                    'grand_total' => $unit->total_pricing,
                    'total_finishing_price' => $unit->total_finish_pricing,
                    'unit_total_with_finishing_price' => $unit->unit_total_with_finish_price,
                    'planned_delivery_date' => $compound->planned_delivery_date,
                    'actual_delivery_date' => $compound->actual_delivery_date,
                    'completion_progress' => $compound->completion_progress,
                    'land_area' => $unit->garden_area,
                    'basement_area' => $unit->basement_area,
                    'uncovered_basement_area' => $unit->uncovered_basement,
                    'penthouse_area' => $unit->penthouse,
                    'semi_covered_roof_area' => $unit->semi_covered_roof_area,
                    'roof_area' => $unit->roof_area,
                    'garden_outdoor_area' => $unit->garden_area,
                    'garage_area' => $unit->garage_area,
                    'pergola_area' => $unit->pergola_area,
                    'storage_area' => $unit->storage_area,
                    'extra_builtup_area' => $unit->extra_built_up_area,
                    'finishing_specs' => $compound->finishing_specs,
                    'club' => $compound->club,
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Handle the Compound "deleted" event.
     */
    public function deleted(Compound $compound): void
    {
        //
    }

    /**
     * Handle the Compound "restored" event.
     */
    public function restored(Compound $compound): void
    {
        //
    }

    /**
     * Handle the Compound "force deleted" event.
     */
    public function forceDeleted(Compound $compound): void
    {
        //
    }
}
