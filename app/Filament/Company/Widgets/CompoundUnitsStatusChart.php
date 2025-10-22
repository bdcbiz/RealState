<?php

namespace App\Filament\Company\Widgets;

use App\Models\Compound;
use Filament\Widgets\Widget;

class CompoundUnitsStatusChart extends Widget
{
    protected static string $view = 'filament.company.widgets.compound-units-status-chart';

    protected int | string | array $columnSpan = 'full';

    public function getCompoundsData(): array
    {
        $companyId = auth()->user()?->company_id; // Company IS the authenticated user

        // Get compounds with their unit statistics and sales count
        $compounds = Compound::where('company_id', $companyId)
            ->select('id', 'project')
            ->withCount([
                'units as total_units',
                'units as sold_units' => function ($query) {
                    $query->where('is_sold', true);
                },
                'units as available_units' => function ($query) {
                    $query->where('is_sold', false);
                },
                'units as inhabited_units' => function ($query) {
                    $query->where('status', 'inhabited');
                },
                'units as in_progress_units' => function ($query) {
                    $query->where('status', 'in_progress');
                },
                'units as delivered_units' => function ($query) {
                    $query->where('status', 'delivered');
                },
            ])
            ->with(['units' => function ($query) {
                $query->select('compound_id', 'sales_id')
                    ->where('is_sold', true)
                    ->whereNotNull('sales_id');
            }])
            ->having('total_units', '>', 0)
            ->orderBy('project')
            ->get();

        // Count unique sales per compound
        return $compounds->map(function ($compound) {
            $uniqueSales = $compound->units->pluck('sales_id')->unique()->count();

            return [
                'project' => $compound->project,
                'total_units' => $compound->total_units,
                'sold_units' => $compound->sold_units,
                'available_units' => $compound->available_units,
                'inhabited_units' => $compound->inhabited_units,
                'in_progress_units' => $compound->in_progress_units,
                'delivered_units' => $compound->delivered_units,
                'sales_count' => $uniqueSales,
            ];
        })->toArray();
    }
}
