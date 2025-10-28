<?php

namespace App\Filament\Company\Pages;

use App\Filament\Company\Widgets\CompanyInfoWidget;
use App\Filament\Company\Widgets\CompanyStatsOverview;
use App\Filament\Company\Widgets\CompoundSalesChart;
use App\Filament\Company\Widgets\CompoundUnitsStatusChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament-panels::pages.dashboard';

    public function getWidgets(): array
    {
        return [
            CompanyInfoWidget::class,
            CompanyStatsOverview::class,
            CompoundUnitsStatusChart::class,
            CompoundSalesChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
