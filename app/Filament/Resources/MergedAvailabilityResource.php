<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MergedAvailabilityResource\Pages;
use App\Models\MergedAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;

class MergedAvailabilityResource extends Resource
{
    protected static ?string $model = MergedAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public static function getNavigationGroup(): ?string
    {
        return __('merged_availability.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('merged_availability.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('merged_availability.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('merged_availability.model.plural');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        // Return a custom query that merges both tables
        return parent::getEloquentQuery()
            ->fromSub(function ($query) {
                $query->from('sales_availability')
                    ->selectRaw('
                        CONCAT("s_", id) as id,
                        project,
                        stage,
                        category,
                        unit_type,
                        unit_code,
                        CAST(REPLACE(REPLACE(grand_total, "EGP", ""), ",", "") AS DECIMAL(15,2)) as price,
                        CAST(REPLACE(extra_builtup_area, ",", "") AS DECIMAL(10,2)) as bua,
                        CAST(REPLACE(garden_outdoor_area, ",", "") AS DECIMAL(10,2)) as garden_area,
                        CAST(REPLACE(roof_area, ",", "") AS DECIMAL(10,2)) as roof_area,
                        "sales" as source,
                        created_at,
                        updated_at
                    ')
                    ->unionAll(
                        DB::table('units_availability')
                            ->selectRaw('
                                CONCAT("u_", id) as id,
                                project,
                                NULL as stage,
                                NULL as category,
                                usage_type as unit_type,
                                NULL as unit_code,
                                CAST(REPLACE(REPLACE(REPLACE(nominal_price, "EGP", ""), ",", ""), " ", "") AS DECIMAL(15,2)) as price,
                                CAST(REPLACE(bua, ",", "") AS DECIMAL(10,2)) as bua,
                                CAST(REPLACE(garden_area, ",", "") AS DECIMAL(10,2)) as garden_area,
                                CAST(REPLACE(roof_area, ",", "") AS DECIMAL(10,2)) as roof_area,
                                "units" as source,
                                created_at,
                                updated_at
                            ')
                    );
            }, 'merged_availability');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project')
                    ->disabled(),
                Forms\Components\TextInput::make('unit_type')
                    ->disabled(),
                Forms\Components\TextInput::make('price')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('project', 'asc')
            ->columns([
                Tables\Columns\BadgeColumn::make('source')
                    ->colors([
                        'success' => 'sales',
                        'primary' => 'units',
                    ])
                    ->sortable()
                    ->label(__('merged_availability.fields.source')),
                Tables\Columns\TextColumn::make('project')
                    ->searchable()
                    ->sortable()
                    ->label(__('merged_availability.fields.project')),

                // Sales Availability columns
                Tables\Columns\TextColumn::make('stage')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.stage')),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.category')),
                Tables\Columns\TextColumn::make('unit_type')
                    ->searchable()
                    ->sortable()
                    ->label(__('merged_availability.fields.unit_type')),
                Tables\Columns\TextColumn::make('unit_code')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.unit_code')),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label(__('merged_availability.fields.grand_total'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_finishing_price')
                    ->label(__('merged_availability.fields.total_finishing_price'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_total_with_finishing_price')
                    ->label(__('merged_availability.fields.unit_total_with_finishing_price'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('planned_delivery_date')
                    ->label(__('merged_availability.fields.planned_delivery_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->label(__('merged_availability.fields.actual_delivery_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->label(__('merged_availability.fields.completion_progress'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('land_area')
                    ->label(__('merged_availability.fields.land_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('built_area')
                    ->label(__('merged_availability.fields.built_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('basement_area')
                    ->label(__('merged_availability.fields.basement_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('uncovered_basement_area')
                    ->label(__('merged_availability.fields.uncovered_basement_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('penthouse_area')
                    ->label(__('merged_availability.fields.penthouse_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('semi_covered_roof_area')
                    ->label(__('merged_availability.fields.semi_covered_roof_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('garage_area')
                    ->label(__('merged_availability.fields.garage_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pergola_area')
                    ->label(__('merged_availability.fields.pergola_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('storage_area')
                    ->label(__('merged_availability.fields.storage_area'))
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('finishing_specs')
                    ->label(__('merged_availability.fields.finishing_specs'))
                    ->sortable()
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('club')
                    ->label(__('merged_availability.fields.club'))
                    ->sortable()
                    ->toggleable(),

                // Units Availability columns
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.unit_name')),
                Tables\Columns\TextColumn::make('usage_type')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.usage_type')),
                Tables\Columns\TextColumn::make('floor')
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.floor')),
                Tables\Columns\TextColumn::make('no_of_bedrooms')
                    ->label(__('merged_availability.fields.no_of_bedrooms'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nominal_price')
                    ->label(__('merged_availability.fields.nominal_price'))
                    ->sortable()
                    ->toggleable(),

                // Common columns
                Tables\Columns\TextColumn::make('bua')
                    ->label(__('merged_availability.fields.bua'))
                    ->suffix(' m²')
                    ->sortable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.garden_area')),
                Tables\Columns\TextColumn::make('roof_area')
                    ->suffix(' m²')
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.roof_area')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->label(__('merged_availability.fields.updated_at')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label(__('merged_availability.filters.source'))
                    ->options([
                        'sales' => __('merged_availability.source_options.sales'),
                        'units' => __('merged_availability.source_options.units'),
                    ]),
                Tables\Filters\SelectFilter::make('project')
                    ->label(__('merged_availability.filters.project'))
                    ->options(function () {
                        return DB::table(DB::raw('(
                            SELECT DISTINCT project FROM sales_availability WHERE project IS NOT NULL
                            UNION
                            SELECT DISTINCT project FROM units_availability WHERE project IS NOT NULL
                        ) as projects'))
                        ->pluck('project', 'project')
                        ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Action::make('importExcel')
                    ->label(__('merged_availability.actions.import_excel'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->url(fn (): string => route('import.merged-availability.form'))
                    ->openUrlInNewTab(),
                Action::make('exportExcel')
                    ->label(__('merged_availability.actions.export_excel'))
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(fn (): string => route('export.merged-availability'))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No delete action for merged data
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
            'index' => Pages\ListMergedAvailabilities::route('/'),
        ];
    }
}
