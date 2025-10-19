<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class SellingUnitsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Selling Units';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $totalSelling = Unit::where('usage_type', 'sale')->count();
        $soldUnits = Unit::where('usage_type', 'sale')->where('is_sold', true)->count();
        $availableForSale = Unit::where('usage_type', 'sale')->where('is_sold', false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$soldUnits, $availableForSale],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)', // Red for sold
                        'rgba(255, 206, 86, 0.8)', // Yellow for available
                    ],
                ],
            ],
            'labels' => [
                "Sold ($soldUnits)",
                "Available ($availableForSale)"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
