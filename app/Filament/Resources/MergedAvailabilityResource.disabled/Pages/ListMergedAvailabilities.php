<?php

namespace App\Filament\Resources\MergedAvailabilityResource\Pages;

use App\Filament\Resources\MergedAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListMergedAvailabilities extends ListRecords
{
    protected static string $resource = MergedAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for merged data
        ];
    }

    protected function getTableQuery(): ?Builder
    {
        // Use DB::table directly and convert results to models
        $subquery = DB::table(DB::raw('(
            SELECT
                CONCAT("s_", id) as id,
                project,
                stage,
                category,
                unit_type,
                unit_code,
                grand_total,
                total_finishing_price,
                unit_total_with_finishing_price,
                planned_delivery_date,
                actual_delivery_date,
                completion_progress,
                land_area,
                built_area,
                basement_area,
                uncovered_basement_area,
                penthouse_area,
                semi_covered_roof_area,
                roof_area,
                garden_outdoor_area as garden_area,
                garage_area,
                pergola_area,
                storage_area,
                extra_builtup_area as bua,
                finishing_specs,
                club,
                NULL as unit_name,
                NULL as usage_type,
                NULL as floor,
                NULL as no_of_bedrooms,
                NULL as nominal_price,
                "sales" as source,
                created_at,
                updated_at
            FROM sales_availability

            UNION ALL

            SELECT
                CONCAT("u_", id) as id,
                project,
                NULL as stage,
                NULL as category,
                usage_type as unit_type,
                NULL as unit_code,
                NULL as grand_total,
                NULL as total_finishing_price,
                NULL as unit_total_with_finishing_price,
                NULL as planned_delivery_date,
                NULL as actual_delivery_date,
                NULL as completion_progress,
                NULL as land_area,
                NULL as built_area,
                NULL as basement_area,
                NULL as uncovered_basement_area,
                NULL as penthouse_area,
                NULL as semi_covered_roof_area,
                roof_area,
                garden_area,
                NULL as garage_area,
                NULL as pergola_area,
                NULL as storage_area,
                bua,
                NULL as finishing_specs,
                NULL as club,
                unit_name,
                usage_type,
                floor,
                no__of_bedrooms as no_of_bedrooms,
                nominal_price,
                "units" as source,
                created_at,
                updated_at
            FROM units_availability
        ) as merged_data'));

        return static::getResource()::getEloquentQuery()
            ->fromSub($subquery, 'merged_availability');
    }
}
