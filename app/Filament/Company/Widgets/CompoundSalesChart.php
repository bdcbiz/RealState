<?php

namespace App\Filament\Company\Widgets;

use App\Models\Compound;
use Filament\Widgets\ChartWidget;

class CompoundSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Units Sold by Compound';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Company IS the authenticated user, so use auth()->user()?->company_id
        $companyId = auth()->user()?->company_id;

        $compounds = Compound::where('company_id', $companyId)
            ->withCount(['units as sold_units_count' => function ($query) {
                $query->where('is_sold', true);
            }])
            ->orderBy('sold_units_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Units Sold',
                    'data' => $compounds->pluck('sold_units_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(251, 146, 60, 0.5)',
                        'rgba(168, 85, 247, 0.5)',
                        'rgba(236, 72, 153, 0.5)',
                        'rgba(34, 197, 94, 0.5)',
                        'rgba(234, 179, 8, 0.5)',
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(14, 165, 233, 0.5)',
                        'rgba(249, 115, 22, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(251, 146, 60)',
                        'rgb(168, 85, 247)',
                        'rgb(236, 72, 153)',
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(239, 68, 68)',
                        'rgb(14, 165, 233)',
                        'rgb(249, 115, 22)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $compounds->pluck('project')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
