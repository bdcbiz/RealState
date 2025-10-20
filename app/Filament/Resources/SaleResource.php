<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $slug = 'all-discounts';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('sales.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('sales.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('sales.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sales.model.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('sales.sections.sale_information'))->schema([
                Forms\Components\TextInput::make('sale_name')
                    ->label(__('sales.fields.sale_name'))
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->label(__('sales.fields.description'))
                    ->disabled(),
                Forms\Components\Select::make('sale_type')
                    ->label(__('sales.fields.sale_type'))
                    ->options([
                        'unit' => __('sales.types.unit'),
                        'compound' => __('sales.types.compound')
                    ])
                    ->disabled(),
                Forms\Components\TextInput::make('salesPerson.name')
                    ->label(__('sales.fields.sales_person'))
                    ->disabled(),
            ])->columns(2),
            Forms\Components\Section::make(__('sales.sections.pricing'))->schema([
                Forms\Components\TextInput::make('discount_percentage')
                    ->label(__('sales.fields.discount_percentage'))
                    ->suffix('%')
                    ->disabled(),
                Forms\Components\TextInput::make('old_price')
                    ->label(__('sales.fields.old_price'))
                    ->prefix('EGP')
                    ->disabled(),
                Forms\Components\TextInput::make('new_price')
                    ->label(__('sales.fields.new_price'))
                    ->prefix('EGP')
                    ->disabled(),
            ])->columns(3),
            Forms\Components\Section::make(__('sales.sections.duration'))->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->label(__('sales.fields.start_date'))
                    ->disabled(),
                Forms\Components\DatePicker::make('end_date')
                    ->label(__('sales.fields.end_date'))
                    ->disabled(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('sales.fields.is_active'))
                    ->disabled(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('company.name')
                ->label(__('sales.fields.company'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('salesPerson.name')
                ->label(__('sales.fields.sales_person'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('sale_name')
                ->label(__('sales.fields.sale_name'))
                ->searchable()
                ->sortable()
                ->weight('bold'),
            Tables\Columns\BadgeColumn::make('sale_type')
                ->label(__('sales.fields.sale_type'))
                ->colors(['primary' => 'unit', 'success' => 'compound'])
                ->formatStateUsing(fn($state) => __('sales.types.' . $state)),
            Tables\Columns\TextColumn::make('unit.unit_name')
                ->label(__('sales.fields.unit'))
                ->default('—')
                ->searchable(),
            Tables\Columns\TextColumn::make('compound.project')
                ->label(__('sales.fields.compound'))
                ->default('—')
                ->searchable(),
            Tables\Columns\TextColumn::make('discount_percentage')
                ->label(__('sales.fields.discount_percentage'))
                ->suffix('%')
                ->sortable()
                ->color('warning')
                ->weight('bold'),
            Tables\Columns\TextColumn::make('old_price')
                ->label(__('sales.fields.old_price'))
                ->money('EGP')
                ->sortable(),
            Tables\Columns\TextColumn::make('new_price')
                ->label(__('sales.fields.new_price'))
                ->money('EGP')
                ->sortable()
                ->color('success')
                ->weight('bold'),
            Tables\Columns\TextColumn::make('start_date')
                ->label(__('sales.fields.start_date'))
                ->date()
                ->sortable(),
            Tables\Columns\TextColumn::make('end_date')
                ->label(__('sales.fields.end_date'))
                ->date()
                ->sortable(),
            Tables\Columns\IconColumn::make('is_active')
                ->label(__('sales.fields.is_active'))
                ->boolean(),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('sales.fields.created_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\SelectFilter::make('company')
                ->label(__('sales.filters.company'))
                ->relationship('company', 'name'),
            Tables\Filters\SelectFilter::make('sale_type')
                ->label(__('sales.filters.sale_type'))
                ->options([
                    'unit' => __('sales.types.unit'),
                    'compound' => __('sales.types.compound')
                ]),
            Tables\Filters\TernaryFilter::make('is_active')
                ->label(__('sales.filters.is_active')),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'view' => Pages\ViewSale::route('/{record}'),
        ];
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
}
