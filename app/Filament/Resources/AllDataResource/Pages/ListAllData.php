<?php

namespace App\Filament\Resources\AllDataResource\Pages;

use App\Filament\Resources\AllDataResource;
use Filament\Resources\Pages\ListRecords;

class ListAllData extends ListRecords
{
    protected static string $resource = AllDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
