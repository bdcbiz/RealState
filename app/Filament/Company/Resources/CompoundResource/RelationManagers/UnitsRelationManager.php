<?php

namespace App\Filament\Company\Resources\CompoundResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $recordTitleAttribute = 'unit_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_name')
                    ->label('Unit Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_code')
                    ->label('Unit Code')
                    ->maxLength(255),
                Forms\Components\Select::make('unit_type')
                    ->options([
                        'Apartment' => 'Apartment',
                        'Villa' => 'Villa',
                        'Town House' => 'Town House',
                        'Twin House' => 'Twin House',
                        'Chalet' => 'Chalet',
                        'Offices' => 'Offices',
                    ]),
                Forms\Components\TextInput::make('number_of_beds')
                    ->label('Bedrooms')
                    ->numeric(),
                Forms\Components\TextInput::make('garden_area')
                    ->label('Garden Area')
                    ->numeric()
                    ->suffix('sqm'),
                Forms\Components\TextInput::make('roof_area')
                    ->label('Roof Area')
                    ->numeric()
                    ->suffix('sqm'),
                Forms\Components\TextInput::make('garage_area')
                    ->label('Garage Area')
                    ->numeric()
                    ->suffix('sqm'),
                Forms\Components\TextInput::make('pergola_area')
                    ->label('Pergola Area')
                    ->numeric()
                    ->suffix('sqm'),
                Forms\Components\TextInput::make('total_pricing')
                    ->label('Total Price')
                    ->numeric()
                    ->prefix('EGP'),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold'),
                Forms\Components\Select::make('sales_id')
                    ->label('Sales Person')
                    ->relationship('sales', 'name', function ($query) {
                        // Company IS the authenticated user, so use auth()->id()
                        return $query->where('role', 'sales')
                                    ->where('company_id', auth()->id());
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
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
                Tables\Columns\TextColumn::make('unit_code')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_beds')
                    ->label('Beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->label('Garden')
                    ->numeric()
                    ->suffix(' sqm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roof_area')
                    ->label('Roof')
                    ->numeric()
                    ->suffix(' sqm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('garage_area')
                    ->label('Garage')
                    ->numeric()
                    ->suffix(' sqm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->label('Pergola')
                    ->numeric()
                    ->suffix(' sqm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pricing')
                    ->label('Price')
                    ->numeric()
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Sold')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sales.name')
                    ->label('Sales Person')
                    ->searchable()
                    ->placeholder('Not assigned'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->options([
                        'Apartment' => 'Apartment',
                        'Villa' => 'Villa',
                        'Town House' => 'Town House',
                        'Twin House' => 'Twin House',
                        'Chalet' => 'Chalet',
                        'Offices' => 'Offices',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label('Sold Status')
                    ->placeholder('All')
                    ->trueLabel('Sold')
                    ->falseLabel('Available'),
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
