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

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Property Management';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Admin users can see all units
        if ($user && $user->role === 'admin') {
            return parent::getEloquentQuery();
        }

        // Company users see only their own units
        $companyId = $user?->company_id;
        if ($companyId) {
            return parent::getEloquentQuery()->whereHas('compound', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }

        return parent::getEloquentQuery()->whereRaw('1 = 0');
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
                Forms\Components\FileUpload::make('images')
                    ->label('Unit Images')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->maxSize(10240)
                    ->disk('apache_public')
                    ->directory('unit-images')
                    ->visibility('public')
                    ->imageEditor()
                    ->reorderable()
                    ->columnSpanFull()
                    ->helperText('Upload up to 10 images (max 10MB each). You can drag to reorder.'),
                Forms\Components\TextInput::make('number_of_beds')
                    ->numeric(),
                Forms\Components\TextInput::make('garden_area')
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
                    ->numeric()
                    ->prefix('EGP'),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold'),
                // Sales relationship doesn't exist on Unit model - commented out
                // Forms\Components\Select::make('sales_id')
                //     ->label('Sales Person')
                //     ->relationship('sales', 'name', function ($query) {
                //         return $query->where('role', 'sales')
                //                     ->where('company_id', auth()->user()->company_id);
                //     })
                //     ->searchable()
                //     ->preload()
                //     ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchDebounce('500ms')
            ->columns([
                Tables\Columns\TextColumn::make('compound.project')
                    ->label('Compound')
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),
                Tables\Columns\ImageColumn::make('images')
                    ->label('Images')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText()
                    ->disk('apache_public')
                    ->height(40)
                    ->width(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->searchable(isIndividual: true, isGlobal: true),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable(isIndividual: true, isGlobal: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_beds')
                    ->label('Beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->numeric()
                    ->sortable()
                    ->suffix(' sqm')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roof_area')
                    ->label('Roof')
                    ->numeric()
                    ->sortable()
                    ->suffix(' sqm'),
                Tables\Columns\TextColumn::make('garage_area')
                    ->label('Garage')
                    ->numeric()
                    ->sortable()
                    ->suffix(' sqm'),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->label('Pergola')
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
                // Sales relationship doesn't exist on Unit model - commented out
                // Tables\Columns\TextColumn::make('sales.name')
                //     ->label('Sales Person')
                //     ->searchable()
                //     ->sortable()
                //     ->placeholder('Not assigned'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('compound_id')
                    ->label('Compound')
                    ->relationship('compound', 'project')
                    ->searchable()
                    ->preload()
                    ->multiple(),
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
                // Sales relationship doesn't exist on Unit model - commented out
                // Tables\Filters\SelectFilter::make('sales_id')
                //     ->label('Sales Person')
                //     ->relationship('sales', 'name', function ($query) {
                //         return $query->where('role', 'sales')
                //                     ->where('company_id', auth()->user()->company_id);
                //     })
                //     ->searchable()
                //     ->preload()
                //     ->multiple(),
                Tables\Filters\SelectFilter::make('number_of_beds')
                    ->label('Bedrooms')
                    ->options([
                        '1' => '1 Bedroom',
                        '2' => '2 Bedrooms',
                        '3' => '3 Bedrooms',
                        '4' => '4 Bedrooms',
                        '5' => '5+ Bedrooms',
                    ])
                    ->query(function (Builder $query, $state) {
                        if (!$state || !isset($state['value'])) {
                            return $query;
                        }

                        if ($state['value'] === '5') {
                            return $query->where('number_of_beds', '>=', 5);
                        }

                        return $query->where('number_of_beds', $state['value']);
                    })
                    ->multiple(),
                Tables\Filters\SelectFilter::make('total_pricing')
                    ->label('Price Range')
                    ->options([
                        '0-1000000' => 'Under 1M EGP',
                        '1000000-2000000' => '1M - 2M EGP',
                        '2000000-3000000' => '2M - 3M EGP',
                        '3000000-5000000' => '3M - 5M EGP',
                        '5000000-10000000' => '5M - 10M EGP',
                        '10000000-999999999' => 'Above 10M EGP',
                    ])
                    ->query(function (Builder $query, $state) {
                        if (!$state || !isset($state['value'])) {
                            return $query;
                        }

                        $range = explode('-', $state['value']);
                        if (count($range) === 2) {
                            return $query->whereBetween('total_pricing', [(int)$range[0], (int)$range[1]]);
                        }
                        return $query;
                    }),
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
