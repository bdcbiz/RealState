<?php

namespace App\Filament\Resources\MergedAvailabilityResource\Pages;

use App\Filament\Resources\MergedAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMergedAvailability extends EditRecord
{
    protected static string $resource = MergedAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
