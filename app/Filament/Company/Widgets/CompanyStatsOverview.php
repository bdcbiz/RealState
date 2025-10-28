<?php

namespace App\Filament\Company\Widgets;

use App\Models\Compound;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $companyId = $user?->company_id;

        // Admin users see all data
        if ($user && $user->role === 'admin') {
            $totalCompounds = Compound::count();
            $totalUnits = Unit::count();
            $availableUnits = Unit::where('is_sold', 0)->count();
            $soldUnits = Unit::where('is_sold', 1)->count();
            $salesCount = User::where('role', 'sales')->count();
        } else {
            // Company users see only their data
            $totalCompounds = Compound::where('company_id', $companyId)->count();
            $totalUnits = Unit::whereHas('compound', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->count();
            $availableUnits = Unit::whereHas('compound', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('is_sold', 0)->count();
            $soldUnits = Unit::whereHas('compound', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('is_sold', 1)->count();
            $salesCount = User::where('company_id', $companyId)
                ->where('role', 'sales')
                ->count();
        }

        return [
            Stat::make('Total Compounds', $totalCompounds)
                ->description('Number of compounds')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Total Units', $totalUnits)
                ->description('All units in your compounds')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),

            Stat::make('Available Units', $availableUnits)
                ->description($soldUnits . ' sold | ' . $salesCount . ' sales team')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('warning'),

            Stat::make('Sales Team', $salesCount)
                ->description('Total sales members')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
}
