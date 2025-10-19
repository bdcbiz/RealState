<?php

namespace App\Filament\Resources\UnitsAvailabilityResource\Pages;

use App\Filament\Resources\UnitsAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitsAvailability extends EditRecord
{
    protected static string $resource = UnitsAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
