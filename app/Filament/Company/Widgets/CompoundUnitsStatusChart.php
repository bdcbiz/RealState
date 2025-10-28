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
        $user = auth()->user();
        $companyId = $user?->company_id;

        // Get compounds with their unit statistics and sales count
        $compoundsQuery = Compound::query();

        // Filter by company for non-admin users
        if (!$user || $user->role !== 'admin') {
            $compoundsQuery->where('company_id', $companyId);
        }

        $compounds = $compoundsQuery
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
