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
                Forms\Components\Select::make('status')
                    ->options([
                        'inhabited' => 'Inhabited',
                        'in_progress' => 'In Progress',
                        'delivered' => 'Delivered',
                    ])
                    ->default('in_progress')
                    ->required(),
                Forms\Components\DatePicker::make('delivered_at')
                    ->label('Delivered At'),
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
                Tables\Columns\ImageColumn::make('images')
                    ->label('Images')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),
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
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'in_progress',
                        'success' => 'delivered',
                        'primary' => 'inhabited',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered At')
                    ->date()
                    ->sortable()
                    ->toggleable(),
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
                Tables\Filters\SelectFilter::make('compound_id')
                    ->label('Compound')
                    ->relationship('compound', 'project')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label('Unit Type')
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
                Tables\Filters\SelectFilter::make('price_range')
                    ->label('Price Range')
                    ->options([
                        '0-1000000' => 'Under 1M EGP',
                        '1000000-3000000' => '1M - 3M EGP',
                        '3000000-5000000' => '3M - 5M EGP',
                        '5000000-10000000' => '5M - 10M EGP',
                        '10000000+' => 'Above 10M EGP',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '10000000+') {
                                return $q->where('total_pricing', '>=', 10000000);
                            }
                            [$min, $max] = explode('-', $value);
                            return $q->whereBetween('total_pricing', [(int)$min, (int)$max]);
                        });
                    }),
                Tables\Filters\SelectFilter::make('bua_range')
                    ->label('BUA Range')
                    ->options([
                        '0-50' => '0 - 50 sqm',
                        '50-100' => '50 - 100 sqm',
                        '100-150' => '100 - 150 sqm',
                        '150-200' => '150 - 200 sqm',
                        '200-300' => '200 - 300 sqm',
                        '300+' => 'Above 300 sqm',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '300+') {
                                return $q->where('extra_built_up_area', '>=', 300);
                            }
                            [$min, $max] = explode('-', $value);
                            return $q->whereBetween('extra_built_up_area', [(int)$min, (int)$max]);
                        });
                    }),
                Tables\Filters\SelectFilter::make('number_of_beds')
                    ->label('Bedrooms')
                    ->options([
                        '1' => '1 Bedroom',
                        '2' => '2 Bedrooms',
                        '3' => '3 Bedrooms',
                        '4' => '4 Bedrooms',
                        '5+' => '5+ Bedrooms',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '5+') {
                                return $q->where('number_of_beds', '>=', 5);
                            }
                            return $q->where('number_of_beds', (int)$value);
                        });
                    }),
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
