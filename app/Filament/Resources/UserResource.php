<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.User Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.Users');
    }

    public static function getModelLabel(): string
    {
        return __('messages.Users');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'buyer');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('messages.Full Name')),
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
                Forms\Components\Select::make('role')
                    ->options([
                        'buyer' => __('messages.Buyer'),
                        'company' => __('messages.Company'),
                        'admin' => __('messages.Admin'),
                    ])
                    ->default('buyer')
                    ->required()
                    ->reactive()
                    ->label(__('messages.Role')),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('user-images')
                    ->label(__('messages.Profile Image'))
                    ->visible(fn (Forms\Get $get) => $get('role') === 'company'),
                Forms\Components\Toggle::make('is_verified')
                    ->label(__('messages.Verified'))
                    ->default(true),
                Forms\Components\Toggle::make('is_banned')
                    ->label(__('messages.Banned'))
                    ->default(false),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('messages.Email Verified At')),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label(__('messages.Password'))
                    ->revealable()
                    ->autocomplete('new-password'),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label(__('messages.Confirm Password'))
                    ->revealable()
                    ->autocomplete('new-password'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label(__('messages.Image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.Name'))
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
                    ->label(__('messages.Role'))
                    ->colors([
                        'secondary' => 'buyer',
                        'warning' => 'sales',
                        'success' => 'admin',
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
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label(__('messages.Created At')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label(__('messages.Updated At')),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
