<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class AvailableUnitsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Available Units';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $totalUnits = Unit::count();
        $availableUnits = Unit::where('is_sold', false)->count();
        $soldUnits = Unit::where('is_sold', true)->count();

        return [
            'datasets' => [
                [
                    'data' => [$availableUnits, $soldUnits],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.8)', // Teal for available
                        'rgba(255, 99, 132, 0.8)', // Red for sold
                    ],
                ],
            ],
            'labels' => [
                "Available ($availableUnits)",
                "Sold ($soldUnits)"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
