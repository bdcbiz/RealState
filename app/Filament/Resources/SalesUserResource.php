<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesUserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesUserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'all-sales-team';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.sales_team.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.sales_team.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.sales_team.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.sales_team.model.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'sales');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.sales_team.sections.information'))->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.sales_team.fields.name'))
                    ->disabled(),
                Forms\Components\TextInput::make('email')
                    ->label(__('admin.sales_team.fields.email'))
                    ->disabled(),
                Forms\Components\TextInput::make('phone')
                    ->label(__('admin.sales_team.fields.phone'))
                    ->disabled(),
                Forms\Components\TextInput::make('company.name')
                    ->label(__('admin.sales_team.fields.company'))
                    ->disabled(),
                Forms\Components\Toggle::make('is_verified')
                    ->label(__('admin.sales_team.fields.verified'))
                    ->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->circular(),
            Tables\Columns\TextColumn::make('company.name')
                ->label(__('admin.sales_team.fields.company'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('name')
                ->label(__('admin.sales_team.fields.name'))
                ->searchable()
                ->sortable()
                ->weight('bold'),
            Tables\Columns\TextColumn::make('email')
                ->label(__('admin.sales_team.fields.email'))
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('phone')
                ->label(__('admin.sales_team.fields.phone'))
                ->searchable(),
            Tables\Columns\IconColumn::make('is_verified')
                ->boolean()
                ->label(__('admin.sales_team.fields.verified')),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('admin.sales_team.fields.created_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\SelectFilter::make('company')
                ->relationship('company', 'name'),
            Tables\Filters\TernaryFilter::make('is_verified'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesUsers::route('/'),
            'view' => Pages\ViewSalesUser::route('/{record}'),
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
