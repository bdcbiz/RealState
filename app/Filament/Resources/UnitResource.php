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

    public static function getNavigationGroup(): ?string
    {
        return __('units.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('units.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('units.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('units.model.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('compound_id')
                    ->relationship('compound', 'project')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('units.fields.compound')),
                Forms\Components\TextInput::make('unit_name')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.unit_name')),
                Forms\Components\TextInput::make('building_name')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.building_name')),
                Forms\Components\TextInput::make('unit_number')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.unit_number')),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.code')),
                Forms\Components\TextInput::make('usage_type')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.usage_type')),
                Forms\Components\TextInput::make('garden_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.garden_area')),
                Forms\Components\TextInput::make('roof_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.roof_area')),
                Forms\Components\TextInput::make('floor_number')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.floor_number')),
                Forms\Components\TextInput::make('number_of_beds')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.number_of_beds')),
                Forms\Components\TextInput::make('normal_price')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.normal_price')),
                Forms\Components\TextInput::make('stage_number')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.stage_number')),
                Forms\Components\TextInput::make('unit_type')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.unit_type')),
                Forms\Components\TextInput::make('unit_code')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('units.fields.unit_code')),
                Forms\Components\TextInput::make('total_pricing')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.total_pricing')),
                Forms\Components\TextInput::make('total_finish_pricing')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.total_finish_pricing')),
                Forms\Components\TextInput::make('unit_total_with_finish_price')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.unit_total_with_finish_price')),
                Forms\Components\TextInput::make('basement_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.basement_area')),
                Forms\Components\TextInput::make('uncovered_basement')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.uncovered_basement')),
                Forms\Components\TextInput::make('penthouse')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.penthouse')),
                Forms\Components\TextInput::make('semi_covered_roof_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.semi_covered_roof_area')),
                Forms\Components\TextInput::make('garage_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.garage_area')),
                Forms\Components\TextInput::make('pergola_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.pergola_area')),
                Forms\Components\TextInput::make('storage_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.storage_area')),
                Forms\Components\TextInput::make('extra_built_up_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('units.fields.extra_built_up_area')),
                Forms\Components\Toggle::make('is_sold')
                    ->label(__('units.fields.is_sold'))
                    ->default(false),
                Forms\Components\Select::make('status')
                    ->options([
                        'inhabited' => __('units.status.inhabited'),
                        'in_progress' => __('units.status.in_progress'),
                        'delivered' => __('units.status.delivered'),
                    ])
                    ->default('in_progress')
                    ->required()
                    ->label(__('units.fields.status')),
                Forms\Components\DatePicker::make('delivered_at')
                    ->label(__('units.fields.delivered_at')),
                Forms\Components\FileUpload::make('images')
                    ->label(__('units.fields.images'))
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
                    ->helperText(__('units.helpers.images')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('compound.project')
                    ->label(__('units.fields.compound'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('images')
                    ->label(__('units.fields.images'))
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_name')
                    ->label(__('units.fields.unit_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->label(__('units.fields.unit_code'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building_name')
                    ->label(__('units.fields.building_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_number')
                    ->label(__('units.fields.unit_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('units.fields.code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('usage_type')
                    ->label(__('units.fields.usage_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->label(__('units.fields.garden_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roof_area')
                    ->label(__('units.fields.roof_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('floor_number')
                    ->label(__('units.fields.floor_number'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_beds')
                    ->label(__('units.fields.number_of_beds'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('normal_price')
                    ->label(__('units.fields.normal_price'))
                    ->numeric()
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stage_number')
                    ->label(__('units.fields.stage_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->label(__('units.fields.unit_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_pricing')
                    ->label(__('units.fields.total_pricing'))
                    ->numeric()
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_finish_pricing')
                    ->label(__('units.fields.total_finish_pricing'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_total_with_finish_price')
                    ->label(__('units.fields.unit_total_with_finish_price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('basement_area')
                    ->label(__('units.fields.basement_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uncovered_basement')
                    ->label(__('units.fields.uncovered_basement'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penthouse')
                    ->label(__('units.fields.penthouse'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('semi_covered_roof_area')
                    ->label(__('units.fields.semi_covered_roof_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('garage_area')
                    ->label(__('units.fields.garage_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->label(__('units.fields.pergola_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('storage_area')
                    ->label(__('units.fields.storage_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_built_up_area')
                    ->label(__('units.fields.extra_built_up_area'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label(__('units.fields.is_sold'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('units.fields.status'))
                    ->colors([
                        'warning' => 'in_progress',
                        'success' => 'delivered',
                        'primary' => 'inhabited',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label(__('units.fields.delivered_at'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('units.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('units.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label(__('units.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('compound_name')
                    ->label(__('units.fields.compound_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_salespeople')
                    ->label('Company Salespeople')
                    ->badge()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('compound_id')
                    ->label(__('units.filters.compound'))
                    ->relationship('compound', 'project')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label(__('units.filters.unit_type'))
                    ->options([
                        'Apartment' => __('units.types.apartment'),
                        'Villa' => __('units.types.villa'),
                        'Town House' => __('units.types.town_house'),
                        'Chalet' => __('units.types.chalet'),
                        'Cabins' => __('units.types.cabins'),
                        'Offices' => __('units.types.offices'),
                        'Twin House' => __('units.types.twin_house'),
                    ]),
                Tables\Filters\TernaryFilter::make('available')
                    ->label(__('units.filters.available')),
                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label(__('units.filters.sold')),
                Tables\Filters\SelectFilter::make('price_range')
                    ->label(__('units.filters.price_range'))
                    ->options([
                        '0-1000000' => __('units.filters.price_ranges.under_1m'),
                        '1000000-3000000' => __('units.filters.price_ranges.1m_3m'),
                        '3000000-5000000' => __('units.filters.price_ranges.3m_5m'),
                        '5000000-10000000' => __('units.filters.price_ranges.5m_10m'),
                        '10000000+' => __('units.filters.price_ranges.above_10m'),
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
                    ->label(__('units.filters.bua_range'))
                    ->options([
                        '0-50' => __('units.filters.bua_ranges.0_50'),
                        '50-100' => __('units.filters.bua_ranges.50_100'),
                        '100-150' => __('units.filters.bua_ranges.100_150'),
                        '150-200' => __('units.filters.bua_ranges.150_200'),
                        '200-300' => __('units.filters.bua_ranges.200_300'),
                        '300+' => __('units.filters.bua_ranges.300_plus'),
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
                    ->label(__('units.filters.bedrooms'))
                    ->options([
                        '1' => __('units.filters.bedroom_options.1_bedroom'),
                        '2' => __('units.filters.bedroom_options.2_bedrooms'),
                        '3' => __('units.filters.bedroom_options.3_bedrooms'),
                        '4' => __('units.filters.bedroom_options.4_bedrooms'),
                        '5+' => __('units.filters.bedroom_options.5_plus_bedrooms'),
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
