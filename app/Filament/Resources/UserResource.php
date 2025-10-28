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
        return __('user.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('user.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('user.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.model.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        // Show all users (buyer, company, seller, admin)
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('user.fields.name')),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label(__('user.fields.email')),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->label(__('user.fields.phone')),
                Forms\Components\Select::make('role')
                    ->options([
                        'buyer' => __('user.roles.buyer'),
                        'company' => __('user.roles.company'),
                        'admin' => __('user.roles.admin'),
                    ])
                    ->default('buyer')
                    ->required()
                    ->reactive()
                    ->label(__('user.fields.role')),
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('user.fields.company'))
                    ->visible(fn (Forms\Get $get) => $get('role') === 'company')
                    ->placeholder('Select a company'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('user-images')
                    ->label(__('user.fields.image'))
                    ->visible(fn (Forms\Get $get) => $get('role') === 'company'),
                Forms\Components\Toggle::make('is_verified')
                    ->label(__('user.fields.verified'))
                    ->default(true),
                Forms\Components\Toggle::make('is_banned')
                    ->label(__('user.fields.banned'))
                    ->default(false),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('user.fields.email_verified_at')),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label(__('user.fields.password'))
                    ->revealable()
                    ->autocomplete('new-password'),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label(__('user.fields.password_confirmation'))
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
                    ->label(__('user.fields.image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('user.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('user.fields.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('user.fields.phone'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->label(__('user.fields.role'))
                    ->colors([
                        'secondary' => 'buyer',
                        'warning' => 'sales',
                        'success' => 'admin',
                        'info' => 'company',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label(__('user.fields.company'))
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label(__('user.fields.verified'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Sent')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->tooltip(fn ($record) => $record->email_verified_at
                        ? 'Verified at: ' . $record->email_verified_at->format('Y-m-d H:i')
                        : 'Not verified yet'),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verified Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('Not verified'),
                Tables\Columns\ToggleColumn::make('is_banned')
                    ->label(__('user.fields.banned'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label(__('user.fields.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->label(__('user.fields.updated_at')),
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
            RelationManagers\SubscriptionsRelationManager::class,
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
