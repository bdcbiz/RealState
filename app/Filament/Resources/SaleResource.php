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
    protected static ?string $navigationLabel = 'All Discounts';
    protected static ?string $modelLabel = 'Discount';
    protected static ?string $pluralModelLabel = 'All Discounts';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Sale Information')->schema([
                Forms\Components\TextInput::make('sale_name')->disabled(),
                Forms\Components\Textarea::make('description')->disabled(),
                Forms\Components\Select::make('sale_type')
                    ->options(['unit' => 'Unit', 'compound' => 'Compound'])->disabled(),
                Forms\Components\TextInput::make('salesPerson.name')
                    ->label('Sales Person')->disabled(),
            ])->columns(2),
            Forms\Components\Section::make('Pricing')->schema([
                Forms\Components\TextInput::make('discount_percentage')->suffix('%')->disabled(),
                Forms\Components\TextInput::make('old_price')->prefix('EGP')->disabled(),
                Forms\Components\TextInput::make('new_price')->prefix('EGP')->disabled(),
            ])->columns(3),
            Forms\Components\Section::make('Duration')->schema([
                Forms\Components\DatePicker::make('start_date')->disabled(),
                Forms\Components\DatePicker::make('end_date')->disabled(),
                Forms\Components\Toggle::make('is_active')->disabled(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('company.name')
                ->label('Company')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('salesPerson.name')
                ->label('Sales Person')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('sale_name')->searchable()->sortable()->weight('bold'),
            Tables\Columns\BadgeColumn::make('sale_type')
                ->colors(['primary' => 'unit', 'success' => 'compound'])
                ->formatStateUsing(fn($state) => ucfirst($state)),
            Tables\Columns\TextColumn::make('unit.unit_name')->default('—')->searchable(),
            Tables\Columns\TextColumn::make('compound.project')->default('—')->searchable(),
            Tables\Columns\TextColumn::make('discount_percentage')->suffix('%')
                ->sortable()->color('warning')->weight('bold'),
            Tables\Columns\TextColumn::make('old_price')->money('EGP')->sortable(),
            Tables\Columns\TextColumn::make('new_price')->money('EGP')
                ->sortable()->color('success')->weight('bold'),
            Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()
                ->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\SelectFilter::make('company')
                ->relationship('company', 'name'),
            Tables\Filters\SelectFilter::make('sale_type')
                ->options(['unit' => 'Unit', 'compound' => 'Compound']),
            Tables\Filters\TernaryFilter::make('is_active'),
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
