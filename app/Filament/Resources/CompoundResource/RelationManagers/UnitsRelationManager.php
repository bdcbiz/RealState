<?php

namespace App\Filament\Resources\CompoundResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_name')
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')
                    ->label('Unit Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_built_up_area')
                    ->label('BUA (sqm)')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pricing')
                    ->label('Price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\IconColumn::make('available')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Sold')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->options([
                        'Apartment' => 'Apartment',
                        'Villa' => 'Villa',
                        'Town House' => 'Town House',
                        'Chalet' => 'Chalet',
                        'Cabins' => 'Cabins',
                        'Offices' => 'Offices',
                        'Twin House' => 'Twin House',
                    ]),
                Tables\Filters\TernaryFilter::make('available')
                    ->label('Available'),
                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label('Sold'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
