<?php

namespace App\Filament\Resources\SalesUserResource\Pages;

use App\Filament\Resources\SalesUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesUsers extends ListRecords
{
    protected static string $resource = SalesUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
