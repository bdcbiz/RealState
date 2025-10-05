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
        $user = auth()->user()->load('company');

        return [
            'user' => $user,
            'company' => $user->company,
            'logoUrl' => $user->company && $user->company->logo
                ? url('storage/' . $user->company->logo)
                : null,
        ];
    }
}
