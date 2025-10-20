<?php

namespace App\Filament\Resources\CompanyResource\Widgets;

use App\Models\Compound;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class CompanyStatsOverview extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $compoundsCount = Compound::where('company_id', $this->record->id)->count();

        $availableUnitsCount = Unit::whereHas('compound', function ($query) {
            $query->where('company_id', $this->record->id);
        })->where('is_sold', false)->count();

        $totalUnitsCount = Unit::whereHas('compound', function ($query) {
            $query->where('company_id', $this->record->id);
        })->count();

        $soldUnitsCount = $totalUnitsCount - $availableUnitsCount;

        $salesTeamCount = User::where('company_id', $this->record->id)
            ->where('role', 'sales')
            ->count();

        return [
            Stat::make(__('companies.widgets.compounds'), $compoundsCount)
                ->description(__('companies.widgets.total_compounds'))
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make(__('companies.widgets.available_units'), $availableUnitsCount)
                ->description(__('companies.widgets.units_for_sale'))
                ->descriptionIcon('heroicon-o-home')
                ->color('warning')
                ->chart([3, 2, 5, 3, 6, 4, 7, 5]),

            Stat::make(__('companies.widgets.sold_units'), $soldUnitsCount)
                ->description(__('companies.widgets.total_sold'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('danger')
                ->chart([1, 2, 3, 4, 5, 6, 7, 8]),

            Stat::make(__('companies.widgets.sales_team'), $salesTeamCount)
                ->description(__('companies.widgets.team_members'))
                ->descriptionIcon('heroicon-o-users')
                ->color('info')
                ->chart([2, 3, 2, 4, 3, 5, 4, 6]),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
