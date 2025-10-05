<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\UnitResource\Pages;
use App\Filament\Company\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('compound', function ($query) {
            $query->where('company_id', auth()->user()->company_id);
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('compound_id')
                    ->relationship('compound', 'project')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('unit_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('unit_code')
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
                    ->numeric(),
                Forms\Components\TextInput::make('garden_area')
                    ->numeric()
                    ->suffix('sqm'),
                Forms\Components\TextInput::make('total_pricing')
                    ->numeric()
                    ->prefix('EGP'),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('compound.project')
                    ->label('Compound')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_beds')
                    ->label('Beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->numeric()
                    ->sortable()
                    ->suffix(' sqm'),
                Tables\Columns\TextColumn::make('total_pricing')
                    ->numeric()
                    ->money('EGP')
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
                        'Twin House' => 'Twin House',
                        'Chalet' => 'Chalet',
                        'Offices' => 'Offices',
                    ]),
                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label('Sold Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
