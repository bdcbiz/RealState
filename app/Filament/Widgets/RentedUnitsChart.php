<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;

class RentedUnitsChart extends ChartWidget
{
    protected static ?string $heading = 'Units for Rent Status';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $currentlyRented = Unit::where('usage_type', 'rent')->where('is_sold', true)->count();
        $availableForRent = Unit::where('usage_type', 'rent')->where('is_sold', false)->count();

        return [
            'datasets' => [
                [
                    'data' => [$currentlyRented, $availableForRent],
                    'backgroundColor' => [
                        'rgba(255, 159, 64, 0.8)', // Orange for rented
                        'rgba(153, 102, 255, 0.8)', // Purple for available
                    ],
                ],
            ],
            'labels' => ['Currently Rented', 'Available for Rent'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
