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
        $company = auth()->user(); // Company IS the authenticated user

        return [
            'company' => $company,
            'logoUrl' => $company && $company->logo
                ? url('storage/' . $company->logo)
                : null,
        ];
    }
}
