<?php

namespace App\Filament\Resources\SalesAvailabilityResource\Pages;

use App\Filament\Resources\SalesAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesAvailability extends EditRecord
{
    protected static string $resource = SalesAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
