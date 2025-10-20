<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class ClientsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Users';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '200px';

    protected function getData(): array
    {
        $totalUsers = User::count();

        // Get all unique roles
        $roleGroups = User::select('role')
            ->selectRaw('count(*) as count')
            ->groupBy('role')
            ->get();

        $data = [];
        $labels = [];
        $colors = [
            'rgba(54, 162, 235, 0.8)',  // Blue
            'rgba(255, 206, 86, 0.8)',  // Yellow
            'rgba(75, 192, 192, 0.8)',  // Teal
            'rgba(255, 99, 132, 0.8)',  // Red
            'rgba(153, 102, 255, 0.8)', // Purple
            'rgba(255, 159, 64, 0.8)',  // Orange
        ];

        foreach ($roleGroups as $index => $group) {
            $roleName = $group->role ?? 'No Role';
            $data[] = $group->count;
            $labels[] = ucfirst($roleName) . " ({$group->count})";
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
