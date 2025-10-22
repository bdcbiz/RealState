<?php

namespace App\Filament\Company\Resources\SalesResource\Pages;

use App\Filament\Company\Resources\SalesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'sales';
        // Company IS the authenticated user, so use auth()->user()?->company_id
        $data['company_id'] = auth()->user()?->company_id;

        return $data;
    }
}
