<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Real Estate Management';

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
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('building_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('unit_number')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('usage_type')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('garden_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('roof_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('floor_number')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('number_of_beds')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('normal_price')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('stage_number')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('unit_type')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('unit_code')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('total_pricing')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('total_finish_pricing')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('unit_total_with_finish_price')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('basement_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('uncovered_basement')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('penthouse')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('semi_covered_roof_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('garage_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('pergola_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('storage_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('extra_built_up_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold')
                    ->default(false),
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
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('usage_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roof_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('normal_price')
                    ->numeric()
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stage_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_pricing')
                    ->numeric()
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_finish_pricing')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_total_with_finish_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('basement_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uncovered_basement')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penthouse')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semi_covered_roof_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garage_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('storage_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_built_up_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Sold')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('compound_name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
