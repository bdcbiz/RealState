<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitsAvailabilityResource\Pages;
use App\Filament\Resources\UnitsAvailabilityResource\RelationManagers;
use App\Models\UnitsAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class UnitsAvailabilityResource extends Resource
{
    protected static ?string $model = UnitsAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): ?string
    {
        return __('units_availability.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('units_availability.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('units_availability.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('units_availability.model.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('unit_name')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('project')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('usage_type')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bua')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('garden_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('roof_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('floor')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('no__of_bedrooms')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('nominal_price')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable()
                    ->sortable()
                    ->label(__('units_availability.fields.unit_name')),
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable()
                    ->label(__('units_availability.fields.project')),
                Tables\Columns\TextColumn::make('usage_type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label(__('units_availability.fields.usage_type')),
                Tables\Columns\TextColumn::make('bua')
                    ->label(__('units_availability.fields.bua'))
                    ->suffix(' m²')
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('units_availability.fields.garden_area')),
                Tables\Columns\TextColumn::make('roof_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('units_availability.fields.roof_area')),
                Tables\Columns\TextColumn::make('floor')
                    ->sortable()
                    ->label(__('units_availability.fields.floor')),
                Tables\Columns\TextColumn::make('no__of_bedrooms')
                    ->label(__('units_availability.fields.no__of_bedrooms'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_price')
                    ->money('EGP')
                    ->sortable()
                    ->label(__('units_availability.fields.nominal_price')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('units_availability.fields.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('units_availability.fields.updated_at')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->searchable()
                    ->preload()
                    ->label(__('units_availability.filters.project'))
                    ->options(fn () => UnitsAvailability::query()
                        ->distinct()
                        ->whereNotNull('project')
                        ->orderBy('project')
                        ->limit(100)
                        ->pluck('project', 'project')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('usage_type')
                    ->searchable()
                    ->preload()
                    ->label(__('units_availability.filters.usage_type'))
                    ->options(fn () => UnitsAvailability::query()
                        ->distinct()
                        ->whereNotNull('usage_type')
                        ->orderBy('usage_type')
                        ->limit(50)
                        ->pluck('usage_type', 'usage_type')
                        ->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Commented out - route not defined yet
                // Action::make('print')
                //     ->icon('heroicon-o-printer')
                //     ->url(fn (UnitsAvailability $record): string => route('units-availability.print', $record))
                //     ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('exportExcel')
                    ->label(__('units_availability.actions.export_excel'))
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(fn (): string => route('export.units-availability'))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListUnitsAvailabilities::route('/'),
            'create' => Pages\CreateUnitsAvailability::route('/create'),
            'edit' => Pages\EditUnitsAvailability::route('/{record}/edit'),
        ];
    }
}
