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

    protected static ?string $navigationLabel = 'Units Availability';

    protected static ?string $navigationGroup = 'Reports';

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
                    ->sortable(),
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_type')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('bua')
                    ->label('BUA (Built-Up Area)')
                    ->suffix(' m²')
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('roof_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('floor')
                    ->sortable(),
                Tables\Columns\TextColumn::make('no__of_bedrooms')
                    ->label('Bedrooms')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal_price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->searchable()
                    ->preload()
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
                    ->label('Export to Excel')
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
