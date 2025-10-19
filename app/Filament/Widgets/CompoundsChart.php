<?php

namespace App\Filament\Widgets;

use App\Models\Compound;
use Filament\Widgets\ChartWidget;

class CompoundsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Compounds';
    protected static ?int $sort = 1;
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $totalCompounds = Compound::count();
        $soldCompounds = Compound::where('is_sold', true)->count();
        $availableCompounds = Compound::where('is_sold', false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$soldCompounds, $availableCompounds],
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)', // Red for sold
                        'rgba(54, 162, 235, 0.8)', // Blue for available
                    ],
                ],
            ],
            'labels' => [
                "Sold ($soldCompounds)",
                "Available ($availableCompounds)"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
