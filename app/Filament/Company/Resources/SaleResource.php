<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\Compound;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $slug = 'discounts-sales';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Discounts & Sales';
    protected static ?string $modelLabel = 'Sale/Discount';
    protected static ?string $pluralModelLabel = 'Discounts & Sales';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        // Get the authenticated user's company_id
        return parent::getEloquentQuery()
            ->where('company_id', auth()->user()?->company_id);
    }

    public static function form(Form $form): Form
    {
        // Get the authenticated user's company_id
        $companyId = auth()->user()?->company_id;

        return $form->schema([
            Forms\Components\Section::make('Sale Information')->schema([
                Forms\Components\TextInput::make('sale_name')
                    ->required()->maxLength(255)->label('Sale Name'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)->rows(3),
                Forms\Components\Select::make('sale_type')
                    ->required()->options(['unit' => 'Unit Sale', 'compound' => 'Compound Sale'])
                    ->reactive(),
                Forms\Components\Select::make('sales_person_id')
                    ->label('Sales Person (Optional)')
                    ->options(function () use ($companyId) {
                        return User::where('company_id', $companyId)
                            ->where('role', 'sales')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->helperText('Select the sales person responsible for this discount'),
                Forms\Components\Select::make('unit_id')->label('Select Unit')
                    ->options(function () use ($companyId) {
                        return Unit::whereHas('compound', fn($q) => $q->where('company_id', $companyId))
                            ->pluck('unit_name', 'id');
                    })->searchable()
                    ->visible(fn ($get) => $get('sale_type') === 'unit')
                    ->required(fn ($get) => $get('sale_type') === 'unit')
                    ->reactive()
                    ->afterStateUpdated(function ($get, $set, $state) {
                        if ($state && $unit = Unit::find($state)) {
                            // Use normal_price or fallback to unit_total_with_finish_price
                            $oldPrice = $unit->normal_price ?? $unit->unit_total_with_finish_price;

                            if ($oldPrice) {
                                $set('old_price', $oldPrice);

                                // Auto-calculate new price if discount percentage exists
                                if ($discount = $get('discount_percentage')) {
                                    $newPrice = $oldPrice - ($oldPrice * $discount / 100);
                                    $set('new_price', $newPrice);
                                }
                            } else {
                                // Clear old_price if unit has no price (user must enter manually)
                                $set('old_price', null);
                                $set('new_price', null);
                            }
                        }
                    }),
                Forms\Components\Select::make('compound_id')->label('Select Compound')
                    ->options(fn() => Compound::where('company_id', $companyId)->pluck('project', 'id'))
                    ->searchable()
                    ->visible(fn ($get) => $get('sale_type') === 'compound')
                    ->required(fn ($get) => $get('sale_type') === 'compound'),
            ])->columns(2),

            Forms\Components\Section::make('Pricing')->schema([
                Forms\Components\TextInput::make('discount_percentage')
                    ->required()->numeric()->suffix('%')->minValue(0)->maxValue(100)
                    ->reactive()
                    ->afterStateUpdated(function ($get, $set, $state) {
                        if ($oldPrice = $get('old_price')) {
                            $newPrice = $oldPrice - ($oldPrice * $state / 100);
                            $set('new_price', $newPrice);
                        }
                    }),
                Forms\Components\TextInput::make('old_price')
                    ->required()->numeric()->prefix('EGP')
                    ->reactive()
                    ->label('Original Price')
                    ->helperText('Auto-filled from selected unit, or enter manually')
                    ->afterStateUpdated(function ($get, $set, $state) {
                        if ($state && $discount = $get('discount_percentage')) {
                            $newPrice = $state - ($state * $discount / 100);
                            $set('new_price', $newPrice);
                        }
                    }),
                Forms\Components\TextInput::make('new_price')
                    ->required()->numeric()->prefix('EGP')
                    ->disabled()
                    ->dehydrated()
                    ->label('Sale Price')
                    ->helperText('Auto-calculated based on discount %'),
            ])->columns(3),

            Forms\Components\Section::make('Duration')->schema([
                Forms\Components\DatePicker::make('start_date')->required()->default(now()),
                Forms\Components\DatePicker::make('end_date')->required()->after('start_date'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(3),

            Forms\Components\Hidden::make('company_id')->default(auth()->user()?->company_id),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('sale_name')->searchable()->sortable()->weight('bold'),
            Tables\Columns\BadgeColumn::make('sale_type')
                ->colors(['primary' => 'unit', 'success' => 'compound'])
                ->formatStateUsing(fn($state) => ucfirst($state)),
            Tables\Columns\TextColumn::make('salesPerson.name')
                ->label('Sales Person')->searchable()->sortable()->default('—'),
            Tables\Columns\TextColumn::make('unit.unit_name')->default('—'),
            Tables\Columns\TextColumn::make('compound.project')->default('—'),
            Tables\Columns\TextColumn::make('discount_percentage')->suffix('%')->color('warning')->weight('bold'),
            Tables\Columns\TextColumn::make('old_price')->money('EGP'),
            Tables\Columns\TextColumn::make('new_price')->money('EGP')->color('success')->weight('bold'),
            Tables\Columns\TextColumn::make('start_date')->date(),
            Tables\Columns\TextColumn::make('end_date')->date(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('sale_type')->options(['unit' => 'Unit', 'compound' => 'Compound']),
            Tables\Filters\TernaryFilter::make('is_active'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
