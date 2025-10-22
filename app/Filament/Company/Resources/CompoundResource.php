<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\CompoundResource\Pages;
use App\Filament\Company\Resources\CompoundResource\RelationManagers;
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

    protected static ?string $navigationGroup = 'Property Management';

    public static function getEloquentQuery(): Builder
    {
        // Get the authenticated user's company_id
        $companyId = auth()->user()?->company_id;

        if ($companyId) {
            return parent::getEloquentQuery()->where('company_id', $companyId);
        }

        // If no company_id, return empty result to avoid showing all compounds
        return parent::getEloquentQuery()->whereRaw('1 = 0');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project')
                    ->label('Project Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('location_url')
                    ->label('Location URL')
                    ->url()
                    ->placeholder('https://maps.google.com/?q=latitude,longitude')
                    ->helperText('Enter Google Maps URL or any location link'),
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
                    ->numeric(),
                Forms\Components\TextInput::make('how_many_floors')
                    ->numeric(),
                Forms\Components\DatePicker::make('planned_delivery_date'),
                Forms\Components\DatePicker::make('actual_delivery_date'),
                Forms\Components\TextInput::make('completion_progress')
                    ->numeric()
                    ->suffix('%'),
                Forms\Components\TextInput::make('land_area')
                    ->numeric(),
                Forms\Components\TextInput::make('built_area')
                    ->numeric(),
                Forms\Components\Textarea::make('finish_specs')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('club'),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold Out'),
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
                    ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('project')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_url')
                    ->label('Location URL')
                    ->url(fn ($record) => $record->location_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('primary')
                    ->placeholder('No URL')
                    ->limit(30),
                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Total Units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('built_up_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('club')
                    ->label('Has Club')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Sold')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
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
                Tables\Filters\SelectFilter::make('location')
                    ->options(function () {
                        $companyId = auth()->id(); // Company IS the authenticated user
                        return \App\Models\Compound::query()
                            ->where('company_id', $companyId)
                            ->whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location')
                            ->toArray();
                    })
                    ->multiple()
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('club')
                    ->label('Has Club')
                    ->placeholder('All')
                    ->trueLabel('With Club')
                    ->falseLabel('Without Club'),
                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label('Sold Status')
                    ->placeholder('All')
                    ->trueLabel('Sold Out')
                    ->falseLabel('Available'),
                Tables\Filters\SelectFilter::make('completion_progress')
                    ->label('Completion Progress')
                    ->options([
                        '0-20' => '0% - 20%',
                        '20-40' => '20% - 40%',
                        '40-60' => '40% - 60%',
                        '60-80' => '60% - 80%',
                        '80-100' => '80% - 100%',
                    ])
                    ->query(function (Builder $query, $state) {
                        if (!$state || !isset($state['value'])) {
                            return $query;
                        }

                        $range = explode('-', $state['value']);
                        if (count($range) === 2) {
                            return $query->whereBetween('completion_progress', [(int)$range[0], (int)$range[1]]);
                        }
                        return $query;
                    }),
                Tables\Filters\SelectFilter::make('delivery_year')
                    ->label('Delivery Year')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = -2; $i <= 5; $i++) {
                            $year = $currentYear + $i;
                            $years[$year] = $year;
                        }
                        return $years;
                    })
                    ->query(function (Builder $query, $state) {
                        if (!$state || !isset($state['value'])) {
                            return $query;
                        }

                        return $query->whereYear('planned_delivery_date', $state['value']);
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
