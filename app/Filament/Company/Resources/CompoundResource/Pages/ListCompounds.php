<?php

namespace App\Filament\Company\Resources\CompoundResource\Pages;

use App\Filament\Company\Resources\CompoundResource;
use App\Filament\Company\Widgets\CompoundUnitsStatusChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompounds extends ListRecords
{
    protected static string $resource = CompoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
