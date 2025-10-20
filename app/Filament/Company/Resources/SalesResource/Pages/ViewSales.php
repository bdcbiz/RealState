<?php

namespace App\Filament\Company\Resources\SalesResource\Pages;

use App\Filament\Company\Resources\SalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewSales extends ViewRecord
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Sales Person Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')
                            ->copyable(),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Verified')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Joined Date')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Sales Performance')
                    ->schema([
                        Infolists\Components\TextEntry::make('soldUnits')
                            ->label('Total Units Sold')
                            ->getStateUsing(fn ($record) => $record->soldUnits()->count())
                            ->badge()
                            ->color('success'),
                    ]),
            ]);
    }
}
