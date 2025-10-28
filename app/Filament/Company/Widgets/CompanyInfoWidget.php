<?php

namespace App\Filament\Company\Widgets;

use Filament\Widgets\Widget;

class CompanyInfoWidget extends Widget
{
    protected static string $view = 'filament.company.widgets.company-info-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -1;

    public function getViewData(): array
    {
        $user = auth()->user();

        // For admin users, show system info
        if ($user && $user->role === 'admin') {
            return [
                'company' => (object) [
                    'name' => 'Admin Dashboard',
                    'email' => $user->email,
                    'logo' => null,
                ],
                'logoUrl' => null,
            ];
        }

        // For company users, show their company info
        return [
            'company' => $user,
            'logoUrl' => $user && $user->logo
                ? url('storage/' . $user->logo)
                : null,
        ];
    }
}
