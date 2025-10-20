<?php

namespace App\Filament\Widgets;

use App\Models\Compound;
use App\Models\Unit;
use App\Models\User;
use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RealEstateStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Companies', Company::count())
                ->description('Total number of companies')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('warning'),

            Stat::make('Total Compounds', Compound::count())
                ->description('Total number of compounds')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Available Units', Unit::where('is_sold', false)->count())
                ->description('Units not yet sold')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),
        ];
    }
}
