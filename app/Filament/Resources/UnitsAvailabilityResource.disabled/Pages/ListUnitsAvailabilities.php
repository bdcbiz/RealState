<?php

namespace App\Filament\Resources\UnitsAvailabilityResource\Pages;

use App\Filament\Resources\UnitsAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitsAvailabilities extends ListRecords
{
    protected static string $resource = UnitsAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
