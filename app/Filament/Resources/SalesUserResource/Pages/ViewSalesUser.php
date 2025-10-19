<?php

namespace App\Filament\Resources\SalesUserResource\Pages;

use App\Filament\Resources\SalesUserResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesUser extends ViewRecord
{
    protected static string $resource = SalesUserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
