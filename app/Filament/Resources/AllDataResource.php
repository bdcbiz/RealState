<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllDataResource\Pages;
use App\Models\AllData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class AllDataResource extends Resource
{
    protected static ?string $model = AllData::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationLabel = 'All Data (Excel)';

    protected static ?string $navigationGroup = 'Data Management';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'All Data';
    }

    public static function getPluralModelLabel(): string
    {
        return 'All Data (Units + Compounds + Companies)';
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_code')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                // Main visible columns
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_code')
                    ->label('Unit Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_name')
                    ->label('Unit Name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_name_ar')
                    ->label('Unit Name (Arabic)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_name_en')
                    ->label('Unit Name (English)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building_name')
                    ->label('Building Name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_name')
                    ->label('Compound Name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_type')
                    ->label('Unit Type')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_type_ar')
                    ->label('Unit Type (Arabic)')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_type_en')
                    ->label('Unit Type (English)')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('usage_type')
                    ->label('Usage Type')
                    ->sortable()
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('Floor')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('view')
                    ->label('View')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Bedrooms')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('Bathrooms')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('living_rooms')
                    ->label('Living Rooms')
                    ->sortable()
                    ->toggleable(),

                // Areas
                Tables\Columns\TextColumn::make('built_up_area')
                    ->label('Built-Up Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('land_area')
                    ->label('Land Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('garden_area')
                    ->label('Garden Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roof_area')
                    ->label('Roof Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('terrace_area')
                    ->label('Terrace Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('basement_area')
                    ->label('Basement Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('garage_area')
                    ->label('Garage Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_area')
                    ->label('Total Area')
                    ->sortable()
                    ->suffix(' m²')
                    ->numeric(decimalPlaces: 2)
                    ->toggleable(),

                // Pricing
                Tables\Columns\TextColumn::make('normal_price')
                    ->label('Normal Price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cash_price')
                    ->label('Cash Price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_per_meter')
                    ->label('Price Per Meter')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('down_payment')
                    ->label('Down Payment')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('monthly_installment')
                    ->label('Monthly Installment')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('over_years')
                    ->label('Over Years')
                    ->sortable()
                    ->toggleable(),

                // Finishing
                Tables\Columns\TextColumn::make('finishing_type')
                    ->label('Finishing Type')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('finishing_specs')
                    ->label('Finishing Specs')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('finishing_price')
                    ->label('Finishing Price')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),

                // Status & Dates
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'available',
                        'warning' => 'reserved',
                        'danger' => 'sold',
                        'info' => 'in_progress',
                    ])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('availability')
                    ->label('Availability')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('planned_delivery_date')
                    ->label('Planned Delivery Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->label('Actual Delivery Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->label('Completion %')
                    ->sortable()
                    ->suffix('%')
                    ->toggleable(),

                // Unit Details
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phase')
                    ->label('Phase')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building_number')
                    ->label('Building Number')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unit_number')
                    ->label('Unit Number')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description_ar')
                    ->label('Description (Arabic)')
                    ->limit(50)
                    ->toggleable(),

                // Project/Compound Info
                Tables\Columns\TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project_name_ar')
                    ->label('Project Name (Arabic)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_location')
                    ->label('Compound Location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_city')
                    ->label('Compound City')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_area')
                    ->label('Compound Area')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_latitude')
                    ->label('Latitude')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('compound_longitude')
                    ->label('Longitude')
                    ->toggleable(),

                // Company Info
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_name_ar')
                    ->label('Company Name (Arabic)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_email')
                    ->label('Company Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_phone')
                    ->label('Company Phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_website')
                    ->label('Company Website')
                    ->toggleable(),

                // Sales Info
                Tables\Columns\TextColumn::make('sales_id')
                    ->label('Sales ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('buyer_id')
                    ->label('Buyer ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_price_after_discount')
                    ->label('Total Price After Discount')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),

                // Timestamps
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_name')
                    ->label('Project')
                    ->options(fn () => \App\Models\AllData::query()
                        ->whereNotNull('project_name')
                        ->distinct()
                        ->orderBy('project_name')
                        ->pluck('project_name', 'project_name')
                        ->toArray())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('company_name')
                    ->label('Company')
                    ->options(fn () => \App\Models\AllData::query()
                        ->whereNotNull('company_name')
                        ->distinct()
                        ->orderBy('company_name')
                        ->pluck('company_name', 'company_name')
                        ->toArray())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('usage_type')
                    ->label('Type')
                    ->options(fn () => \App\Models\AllData::query()
                        ->whereNotNull('usage_type')
                        ->distinct()
                        ->orderBy('usage_type')
                        ->pluck('usage_type', 'usage_type')
                        ->toArray())
                    ->preload(),
                Tables\Filters\SelectFilter::make('compound_city')
                    ->label('City')
                    ->options(fn () => \App\Models\AllData::query()
                        ->whereNotNull('compound_city')
                        ->distinct()
                        ->orderBy('compound_city')
                        ->pluck('compound_city', 'compound_city')
                        ->toArray())
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'reserved' => 'Reserved',
                        'sold' => 'Sold',
                        'in_progress' => 'In Progress',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->url(fn (): string => route('import.all-data.form'))
                    ->openUrlInNewTab(),
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (): string => route('export.all-data'))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk actions here if needed
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
            'index' => Pages\ListAllData::route('/'),
        ];
    }
}
