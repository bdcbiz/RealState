<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Filament\Resources\SalesResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): ?string
    {
        return __('sales_users.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('sales_users.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('sales_users.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sales_users.model.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'sales');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('sales_users.fields.name')),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label(__('sales_users.fields.email')),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->label(__('sales_users.fields.phone')),
                Forms\Components\Select::make('company_id')
                    ->label(__('sales_users.fields.company'))
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('user-images')
                    ->label(__('sales_users.fields.image')),
                Forms\Components\Toggle::make('is_verified')
                    ->label(__('sales_users.fields.verified'))
                    ->default(true),
                Forms\Components\Toggle::make('is_banned')
                    ->label(__('sales_users.fields.banned'))
                    ->default(false),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label(__('sales_users.fields.password'))
                    ->revealable()
                    ->autocomplete('new-password'),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label(__('sales_users.fields.password_confirmation'))
                    ->revealable()
                    ->autocomplete('new-password'),
                Forms\Components\Hidden::make('role')
                    ->default('sales'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label(__('sales_users.fields.image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('sales_users.fields.name')),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(__('sales_users.fields.email')),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->label(__('sales_users.fields.phone')),
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('sales_users.fields.company'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label(__('sales_users.fields.verified'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_banned')
                    ->label(__('sales_users.fields.banned'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('sales_users.fields.created_at')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label(__('sales_users.filters.company'))
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label(__('sales_users.filters.verified')),
                Tables\Filters\TernaryFilter::make('is_banned')
                    ->label(__('sales_users.filters.banned')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
