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
        return __('company.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('company.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('company.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('company.model.plural');
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
                    ->label(__('company.fields.name')),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->label(__('company.fields.email')),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->label(__('company.fields.phone')),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('company-images')
                    ->label(__('company.fields.image')),
                Forms\Components\Hidden::make('role')
                    ->default('company'),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('company.fields.email_verified_at')),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label(__('company.fields.password'))
                    ->revealable(),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->required(fn (string $context): bool => $context === 'create')
                    ->label(__('company.fields.password_confirmation'))
                    ->revealable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->label(__('company.fields.image'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('company.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('company.fields.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('company.fields.phone'))
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
                    ->label(__('company.fields.verified'))
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_banned')
                    ->label(__('company.fields.banned'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('company.fields.created_at'))
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
