<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyAccountResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompanyAccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.Company Accounts');
    }

    public static function getModelLabel(): string
    {
        return __('messages.Company Accounts');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'company');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('messages.Company Name')),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label(__('messages.Email Address')),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->label(__('messages.Phone Number')),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('company-images')
                    ->label(__('messages.Company Logo')),
                Forms\Components\Hidden::make('role')
                    ->default('company'),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('messages.Email Verified At')),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label(__('messages.Password'))
                    ->revealable(),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label(__('messages.Confirm Password'))
                    ->revealable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label(__('messages.Logo'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.Company Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('messages.Email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('messages.Phone'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'secondary' => 'user',
                        'success' => 'admin',
                        'warning' => 'agent',
                        'primary' => 'owner',
                        'info' => 'company',
                    ])
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label(__('messages.Verified'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_banned')
                    ->label(__('messages.Banned'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('messages.Created At'))
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCompanyAccounts::route('/'),
            'create' => Pages\CreateCompanyAccount::route('/create'),
            'edit' => Pages\EditCompanyAccount::route('/{record}/edit'),
        ];
    }
}
