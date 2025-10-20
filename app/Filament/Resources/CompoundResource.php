<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompoundResource\Pages;
use App\Filament\Resources\CompoundResource\RelationManagers;
use App\Models\Compound;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompoundResource extends Resource
{
    protected static ?string $model = Compound::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): ?string
    {
        return __('compounds.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('compounds.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('compounds.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('compounds.model.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project')
                    ->maxLength(255)
                    ->default(null)
                    ->label(__('compounds.fields.project')),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255)
                    ->label(__('compounds.fields.location')),
                Forms\Components\TextInput::make('location_url')
                    ->url()
                    ->maxLength(500)
                    ->label(__('compounds.fields.location_url'))
                    ->placeholder(__('compounds.helpers.location_url')),
                Forms\Components\FileUpload::make('images')
                    ->label(__('compounds.fields.images'))
                    ->multiple()
                    ->reorderable()
                    ->disk('apache_public')
                    ->directory('compound-images')
                    ->visibility('public')
                    ->image()
                    ->maxSize(10240)
                    ->maxFiles(10)
                    ->imageEditor()
                    ->columnSpanFull()
                    ->helperText(__('compounds.helpers.images')),
                Forms\Components\TextInput::make('built_up_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('compounds.fields.built_up_area')),
                Forms\Components\TextInput::make('how_many_floors')
                    ->numeric()
                    ->default(null)
                    ->label(__('compounds.fields.how_many_floors')),
                Forms\Components\DatePicker::make('planned_delivery_date')
                    ->label(__('compounds.fields.planned_delivery_date')),
                Forms\Components\DatePicker::make('actual_delivery_date')
                    ->label(__('compounds.fields.actual_delivery_date')),
                Forms\Components\TextInput::make('completion_progress')
                    ->numeric()
                    ->default(null)
                    ->label(__('compounds.fields.completion_progress')),
                Forms\Components\TextInput::make('land_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('compounds.fields.land_area')),
                Forms\Components\TextInput::make('built_area')
                    ->numeric()
                    ->default(null)
                    ->label(__('compounds.fields.built_area')),
                Forms\Components\Textarea::make('finish_specs')
                    ->columnSpanFull()
                    ->label(__('compounds.fields.finish_specs')),
                Forms\Components\Toggle::make('club')
                    ->required()
                    ->label(__('compounds.fields.club')),
                Forms\Components\Select::make('status')
                    ->options([
                        'inhabited' => __('compounds.status.inhabited'),
                        'in_progress' => __('compounds.status.in_progress'),
                        'delivered' => __('compounds.status.delivered'),
                    ])
                    ->default('in_progress')
                    ->required()
                    ->label(__('compounds.fields.status')),
                Forms\Components\DatePicker::make('delivered_at')
                    ->label(__('compounds.fields.delivered_at')),
                Forms\Components\TextInput::make('total_units')
                    ->numeric()
                    ->default(0)
                    ->label(__('compounds.fields.total_units')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label(__('compounds.fields.images'))
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project')
                    ->label(__('compounds.fields.project'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label(__('compounds.fields.total_units'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('built_up_area')
                    ->numeric()
                    ->sortable()
                    ->label(__('compounds.fields.built_up_area')),
                Tables\Columns\TextColumn::make('how_many_floors')
                    ->numeric()
                    ->sortable()
                    ->label(__('compounds.fields.how_many_floors')),
                Tables\Columns\TextColumn::make('planned_delivery_date')
                    ->date()
                    ->sortable()
                    ->label(__('compounds.fields.planned_delivery_date')),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->date()
                    ->sortable()
                    ->label(__('compounds.fields.actual_delivery_date')),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->numeric()
                    ->sortable()
                    ->label(__('compounds.fields.completion_progress')),
                Tables\Columns\TextColumn::make('land_area')
                    ->numeric()
                    ->sortable()
                    ->label(__('compounds.fields.land_area')),
                Tables\Columns\TextColumn::make('built_area')
                    ->numeric()
                    ->sortable()
                    ->label(__('compounds.fields.built_area')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'in_progress',
                        'success' => 'delivered',
                        'primary' => 'inhabited',
                    ])
                    ->sortable()
                    ->label(__('compounds.fields.status')),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label(__('compounds.fields.delivered_at'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_units')
                    ->label(__('compounds.fields.total_units'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('compounds.fields.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('compounds.fields.updated_at')),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('compounds.fields.deleted_at')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label(__('compounds.filters.location'))
                    ->options(function () {
                        return \App\Models\Compound::whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location');
                    }),

                Tables\Filters\SelectFilter::make('land_area')
                    ->label(__('compounds.filters.land_area'))
                    ->options(__('compounds.filters.land_area_ranges'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '1000+') {
                                return $q->where('land_area', '>=', 1000);
                            }

                            [$min, $max] = explode('-', $value);
                            return $q->whereBetween('land_area', [(int)$min, (int)$max]);
                        });
                    }),

                Tables\Filters\SelectFilter::make('planned_delivery_date')
                    ->label(__('compounds.filters.delivery_date'))
                    ->options(function () {
                        return \App\Models\Compound::whereNotNull('planned_delivery_date')
                            ->distinct()
                            ->orderBy('planned_delivery_date')
                            ->pluck('planned_delivery_date', 'planned_delivery_date')
                            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('M Y'));
                    })
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['value'], fn ($q, $value) =>
                            $q->where('planned_delivery_date', $value)
                        )
                    ),

                // Unit-based filters
                Tables\Filters\Filter::make('has_available_units')
                    ->label(__('compounds.filters.has_available_units'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('units', fn ($q) => $q->where('available', true))),

                Tables\Filters\SelectFilter::make('price')
                    ->label(__('compounds.filters.price'))
                    ->options(__('compounds.filters.price_ranges'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '10000000+') {
                                return $q->whereHas('units', fn ($unitQuery) =>
                                    $unitQuery->where('total_pricing', '>=', 10000000)
                                );
                            }

                            [$min, $max] = explode('-', $value);
                            return $q->whereHas('units', fn ($unitQuery) =>
                                $unitQuery->whereBetween('total_pricing', [(int)$min, (int)$max])
                            );
                        });
                    }),

                Tables\Filters\SelectFilter::make('unit_area')
                    ->label(__('compounds.filters.unit_area'))
                    ->options(__('compounds.filters.unit_area_ranges'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '500+') {
                                return $q->whereHas('units', fn ($unitQuery) =>
                                    $unitQuery->where('garden_area', '>=', 500)
                                );
                            }

                            [$min, $max] = explode('-', $value);
                            return $q->whereHas('units', fn ($unitQuery) =>
                                $unitQuery->whereBetween('garden_area', [(int)$min, (int)$max])
                            );
                        });
                    }),

                Tables\Filters\SelectFilter::make('unit_type')
                    ->label(__('compounds.filters.unit_type'))
                    ->options(__('compounds.filters.unit_types'))
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['value'], fn ($q, $value) =>
                            $q->whereHas('units', fn ($unitQuery) => $unitQuery->where('unit_type', $value))
                        )
                    ),
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
            RelationManagers\UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompounds::route('/'),
            'create' => Pages\CreateCompound::route('/create'),
            'edit' => Pages\EditCompound::route('/{record}/edit'),
        ];
    }
}
