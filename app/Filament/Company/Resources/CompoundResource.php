<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\CompoundResource\Pages;
use App\Filament\Company\Resources\CompoundResource\RelationManagers;
use App\Models\Compound;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompoundResource extends Resource
{
    protected static ?string $model = Compound::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', auth()->user()->company_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('project')
                    ->label('Project Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('built_up_area')
                    ->numeric(),
                Forms\Components\TextInput::make('how_many_floors')
                    ->numeric(),
                Forms\Components\DatePicker::make('planned_delivery_date'),
                Forms\Components\DatePicker::make('actual_delivery_date'),
                Forms\Components\TextInput::make('completion_progress')
                    ->numeric()
                    ->suffix('%'),
                Forms\Components\TextInput::make('land_area')
                    ->numeric(),
                Forms\Components\TextInput::make('built_area')
                    ->numeric(),
                Forms\Components\Textarea::make('finish_specs')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('club'),
                Forms\Components\Toggle::make('is_sold')
                    ->label('Sold Out'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Total Units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('built_up_area')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_progress')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Sold')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->options(function () {
                        return \App\Models\Compound::whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location');
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
            'index' => Pages\ListCompounds::route('/'),
            'create' => Pages\CreateCompound::route('/create'),
            'edit' => Pages\EditCompound::route('/{record}/edit'),
        ];
    }
}
