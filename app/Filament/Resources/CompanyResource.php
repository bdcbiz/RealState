<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): ?string
    {
        return __('companies.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('companies.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('companies.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('companies.model.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label(__('companies.fields.name')),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->disk('public')
                    ->directory('company-logos')
                    ->label(__('companies.fields.logo'))
                    ->imageEditor(),
                Forms\Components\TextInput::make('number_of_compounds')
                    ->numeric()
                    ->default(0)
                    ->label(__('companies.fields.number_of_compounds')),
                Forms\Components\TextInput::make('number_of_available_units')
                    ->numeric()
                    ->default(0)
                    ->label(__('companies.fields.number_of_available_units')),

                Forms\Components\Section::make(__('companies.sections.sales_team'))
                    ->schema([
                        Forms\Components\Placeholder::make('sales_team_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('companies.messages.save_first_sales');
                                }

                                $salesUsers = \App\Models\User::where('company_id', $record->id)
                                    ->where('role', 'sales')
                                    ->get();

                                if ($salesUsers->isEmpty()) {
                                    return __('companies.messages.no_sales_members');
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($salesUsers as $user) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                    $html .= '<div>';
                                    $html .= '<p class="font-semibold text-gray-900 dark:text-white">' . e($user->name) . '</p>';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">' . e($user->email) . '</p>';
                                    if ($user->phone) {
                                        $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">' . __('companies.messages.phone') . ': ' . e($user->phone) . '</p>';
                                    }
                                    $html .= '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),

                Forms\Components\Section::make(__('companies.sections.compounds'))
                    ->schema([
                        Forms\Components\Placeholder::make('compounds_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return __('companies.messages.save_first_compounds');
                                }

                                $compounds = \App\Models\Compound::where('company_id', $record->id)->get();

                                if ($compounds->isEmpty()) {
                                    return __('companies.messages.no_compounds');
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($compounds as $compound) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                    $html .= '<div class="flex-1">';
                                    $html .= '<p class="font-semibold text-gray-900 dark:text-white">' . e($compound->project) . '</p>';
                                    if ($compound->location) {
                                        $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">' . __('companies.messages.location') . ': ' . e($compound->location) . '</p>';
                                    }
                                    $html .= '</div>';
                                    $html .= '<div class="text-right">';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">' . __('companies.messages.units') . ': ' . $compound->units()->count() . '</p>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->disk('public')
                    ->label(__('companies.fields.logo'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('companies.fields.name')),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts([
                        'users' => fn ($query) => $query->where('role', 'sales')
                    ])
                    ->label(__('companies.fields.sales_team'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_compounds')
                    ->numeric()
                    ->sortable()
                    ->label(__('companies.fields.number_of_compounds')),
                Tables\Columns\TextColumn::make('number_of_available_units')
                    ->numeric()
                    ->sortable()
                    ->label(__('companies.fields.number_of_available_units')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('companies.fields.created_at'))
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('companies.fields.updated_at'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_sales_team')
                    ->label(__('companies.filters.has_sales_team'))
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('users', fn ($q) => $q->where('role', 'sales'))
                    ),
                Tables\Filters\Filter::make('has_compounds')
                    ->label(__('companies.filters.has_compounds'))
                    ->query(fn (Builder $query): Builder =>
                        $query->where('number_of_compounds', '>', 0)
                    ),
                Tables\Filters\SelectFilter::make('compounds_range')
                    ->label(__('companies.filters.compounds_range'))
                    ->options([
                        '1-5' => '1 - 5',
                        '6-10' => '6 - 10',
                        '11-20' => '11 - 20',
                        '20+' => '20+',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '20+') {
                                return $q->where('number_of_compounds', '>=', 20);
                            }
                            [$min, $max] = explode('-', $value);
                            return $q->whereBetween('number_of_compounds', [(int)$min, (int)$max]);
                        });
                    }),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
