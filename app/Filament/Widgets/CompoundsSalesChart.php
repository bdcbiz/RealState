<?php

namespace App\Filament\Widgets;

use App\Models\Compound;
use App\Models\Company;
use Filament\Widgets\ChartWidget;

class CompoundsSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Units Sold by Compound';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get all companies
        $companies = Company::orderBy('id')->get();

        // Get all compounds with their sold units count
        $compounds = Compound::withCount(['units' => function ($query) {
            $query->where('is_sold', true);
        }])
        ->orderBy('units_count', 'desc')
        ->get();

        // Define colors for companies
        $companyColors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(16, 185, 129, 0.8)',   // Emerald
            'rgba(251, 191, 36, 0.8)',   // Amber
            'rgba(168, 85, 247, 0.8)',   // Purple
            'rgba(249, 115, 22, 0.8)',   // Orange
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(20, 184, 166, 0.8)',   // Teal
            'rgba(139, 92, 246, 0.8)',   // Violet
            'rgba(34, 197, 94, 0.8)',    // Green
        ];

        $borderColors = [
            'rgba(59, 130, 246, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(251, 191, 36, 1)',
            'rgba(168, 85, 247, 1)',
            'rgba(249, 115, 22, 1)',
            'rgba(236, 72, 153, 1)',
            'rgba(20, 184, 166, 1)',
            'rgba(139, 92, 246, 1)',
            'rgba(34, 197, 94, 1)',
        ];

        // Create a dataset for each company
        $datasets = [];
        foreach ($companies as $index => $company) {
            // Check if this company has any compounds
            $hasCompounds = $compounds->where('company_id', $company->id)->count() > 0;

            if (!$hasCompounds) continue;

            $data = [];

            // For each compound position, check if it belongs to this company
            foreach ($compounds as $compound) {
                if ($compound->company_id == $company->id) {
                    $data[] = $compound->units_count ?? 0;
                } else {
                    $data[] = null;
                }
            }

            $datasets[] = [
                'label' => $company->name,
                'data' => $data,
                'backgroundColor' => $companyColors[$index % count($companyColors)],
                'borderColor' => $borderColors[$index % count($borderColors)],
                'borderWidth' => 2,
                'borderRadius' => 6,
            ];
        }

        return [
            'datasets' => $datasets,
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
            'responsive' => true,
            'maintainAspectRatio' => true,
            'devicePixelRatio' => 2,
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                            'weight' => '500',
                        ],
                        'maxRotation' => 45,
                        'minRotation' => 45,
                        'color' => '#374151',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                        'precision' => 0,
                        'color' => '#374151',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'left',
                    'labels' => [
                        'padding' => 12,
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                        'usePointStyle' => true,
                        'pointStyle' => 'rectRounded',
                        'color' => '#374151',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'cornerRadius' => 6,
                ],
            ],
        ];
    }
}
