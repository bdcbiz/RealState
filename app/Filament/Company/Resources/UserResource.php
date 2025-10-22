<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\UserResource\Pages;
use App\Filament\Company\Resources\UserResource\RelationManagers;
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

    protected static ?string $navigationLabel = 'Buyers';

    protected static ?string $navigationGroup = 'People Management';

    public static function getEloquentQuery(): Builder
    {
        // Company IS the authenticated user, so use auth()->user()?->company_id
        // Only show buyers who have purchased units from this company's compounds
        return parent::getEloquentQuery()
            ->where('role', 'buyer')
            ->whereHas('purchasedUnits.compound', function ($query) {
                $query->where('company_id', auth()->user()?->company_id);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'buyer' => 'Buyer',
                        'company' => 'Company',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Buyer Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchased_compounds')
                    ->label('Compound')
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(function ($record) {
                        return $record->purchasedUnits()
                            ->whereHas('compound', fn($q) => $q->where('company_id', auth()->user()?->company_id))
                            ->with('compound')
                            ->get()
                            ->pluck('compound.project')
                            ->unique()
                            ->values();
                    }),
                Tables\Columns\TextColumn::make('purchased_units')
                    ->label('Unit')
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(function ($record) {
                        return $record->purchasedUnits()
                            ->whereHas('compound', fn($q) => $q->where('company_id', auth()->user()?->company_id))
                            ->get()
                            ->pluck('unit_name');
                    }),
                Tables\Columns\TextColumn::make('purchased_prices')
                    ->label('Price')
                    ->money('EGP')
                    ->separator(',')
                    ->getStateUsing(function ($record) {
                        return $record->purchasedUnits()
                            ->whereHas('compound', fn($q) => $q->where('company_id', auth()->user()?->company_id))
                            ->get()
                            ->pluck('total_pricing');
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Purchase Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('purchased_compound')
                    ->label('Compound')
                    ->relationship('purchasedUnits.compound', 'project')
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Purchase From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Purchase Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
