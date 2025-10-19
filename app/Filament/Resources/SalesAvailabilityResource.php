<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesAvailabilityResource\Pages;
use App\Filament\Resources\SalesAvailabilityResource\RelationManagers;
use App\Models\SalesAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class SalesAvailabilityResource extends Resource
{
    protected static ?string $model = SalesAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Sales Availability';

    protected static ?string $navigationGroup = 'Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('project')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('stage')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('category')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('unit_type')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('unit_code')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('grand_total')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('total_finishing_price')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('unit_total_with_finishing_price')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('planned_delivery_date')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('actual_delivery_date')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('completion_progress')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('land_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('built_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('basement_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('uncovered_basement_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('penthouse_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('semi_covered_roof_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('roof_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('garden_outdoor_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('garage_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('pergola_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('storage_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('extra_builtup_area')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('finishing_specs')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('club')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_finishing_price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('unit_total_with_finishing_price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('planned_delivery_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('land_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('built_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('basement_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('uncovered_basement_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('penthouse_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('semi_covered_roof_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('roof_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('garden_outdoor_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('garage_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('storage_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('extra_builtup_area')
                    ->label('BUA')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('finishing_specs')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->limit(50),
                Tables\Columns\TextColumn::make('club')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                    ->options(fn () => SalesAvailability::query()
                        ->distinct()
                        ->whereNotNull('project')
                        ->orderBy('project')
                        ->limit(100)
                        ->pluck('project', 'project')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('category')
                    ->searchable()
                    ->preload()
                    ->options(fn () => SalesAvailability::query()
                        ->distinct()
                        ->whereNotNull('category')
                        ->orderBy('category')
                        ->limit(50)
                        ->pluck('category', 'category')
                        ->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Commented out - route not defined yet
                // Action::make('print')
                //     ->icon('heroicon-o-printer')
                //     ->url(fn (SalesAvailability $record): string => route('sales-availability.print', $record))
                //     ->openUrlInNewTab(),
            ])
            ->headerActions([
                Action::make('exportExcel')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(fn (): string => route('export.sales-availability'))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->poll('30s')
            ->deferLoading();
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
            'index' => Pages\ListSalesAvailabilities::route('/'),
            'create' => Pages\CreateSalesAvailability::route('/create'),
            'edit' => Pages\EditSalesAvailability::route('/{record}/edit'),
        ];
    }
}
