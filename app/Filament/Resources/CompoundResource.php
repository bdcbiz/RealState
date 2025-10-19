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

    protected static ?string $navigationGroup = 'Real Estate Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project')
                    ->maxLength(255)
                    ->default(null)
                    ->label('Project Name'),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255)
                    ->label('Location Name'),
                Forms\Components\TextInput::make('location_url')
                    ->url()
                    ->maxLength(500)
                    ->label('Location URL (Google Maps)')
                    ->placeholder('https://maps.google.com/...'),
                Forms\Components\FileUpload::make('images')
                    ->label('Compound Images')
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
                    ->helperText('Upload multiple images for this compound (max 10 images, up to 10MB each)'),
                Forms\Components\TextInput::make('built_up_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('how_many_floors')
                    ->numeric()
                    ->default(null),
                Forms\Components\DatePicker::make('planned_delivery_date'),
                Forms\Components\DatePicker::make('actual_delivery_date'),
                Forms\Components\TextInput::make('completion_progress')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('land_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('built_area')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('finish_specs')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('club')
                    ->required(),
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
                Forms\Components\TextInput::make('total_units')
                    ->numeric()
                    ->default(0)
                    ->label('Total Units'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Images')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Total Units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('built_up_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('how_many_floors')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('planned_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('land_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('built_area')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('total_units')
                    ->label('Total Units')
                    ->numeric()
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->label('Location')
                    ->options(function () {
                        return \App\Models\Compound::whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location');
                    }),

                Tables\Filters\SelectFilter::make('land_area')
                    ->label('Land Area')
                    ->options([
                        '0-50' => '0 - 50 sqm',
                        '50-100' => '50 - 100 sqm',
                        '100-200' => '100 - 200 sqm',
                        '200-300' => '200 - 300 sqm',
                        '300-500' => '300 - 500 sqm',
                        '500-1000' => '500 - 1000 sqm',
                        '1000+' => 'Above 1000 sqm',
                    ])
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
                    ->label('Delivery Date')
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
                    ->label('Has Available Units')
                    ->query(fn (Builder $query): Builder => $query->whereHas('units', fn ($q) => $q->where('available', true))),

                Tables\Filters\SelectFilter::make('price')
                    ->label('Price Range')
                    ->options([
                        '0-1000000' => 'Under 1M',
                        '1000000-3000000' => '1M - 3M',
                        '3000000-5000000' => '3M - 5M',
                        '5000000-10000000' => '5M - 10M',
                        '10000000+' => 'Above 10M',
                    ])
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
                    ->label('Unit Area')
                    ->options([
                        '0-50' => '0 - 50 sqm',
                        '50-100' => '50 - 100 sqm',
                        '100-150' => '100 - 150 sqm',
                        '150-200' => '150 - 200 sqm',
                        '200-300' => '200 - 300 sqm',
                        '300-500' => '300 - 500 sqm',
                        '500+' => 'Above 500 sqm',
                    ])
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
                    ->label('Unit Type')
                    ->options([
                        'Apartment' => 'Apartment',
                        'Villa' => 'Villa',
                        'Town House' => 'Town House',
                        'Chalet' => 'Chalet',
                        'Cabins' => 'Cabins',
                        'Offices' => 'Offices',
                        'Twin House' => 'Twin House',
                    ])
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
