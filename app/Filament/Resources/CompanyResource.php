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

    protected static ?string $navigationGroup = 'Real Estate Management';

    protected static ?string $navigationLabel = 'Companies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Company Name'),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->disk('public')
                    ->directory('company-logos')
                    ->label('Company Logo')
                    ->imageEditor(),
                Forms\Components\TextInput::make('number_of_compounds')
                    ->numeric()
                    ->default(0)
                    ->label('Number of Compounds'),
                Forms\Components\TextInput::make('number_of_available_units')
                    ->numeric()
                    ->default(0)
                    ->label('Number of Available Units'),

                Forms\Components\Section::make('Sales Team')
                    ->schema([
                        Forms\Components\Placeholder::make('sales_team_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return 'Save the company first to see sales team members.';
                                }

                                $salesUsers = \App\Models\User::where('company_id', $record->id)
                                    ->where('role', 'sales')
                                    ->get();

                                if ($salesUsers->isEmpty()) {
                                    return 'No sales team members assigned yet.';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($salesUsers as $user) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                    $html .= '<div>';
                                    $html .= '<p class="font-semibold text-gray-900 dark:text-white">' . e($user->name) . '</p>';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">' . e($user->email) . '</p>';
                                    if ($user->phone) {
                                        $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">Phone: ' . e($user->phone) . '</p>';
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

                Forms\Components\Section::make('Compounds')
                    ->schema([
                        Forms\Components\Placeholder::make('compounds_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return 'Save the company first to see compounds.';
                                }

                                $compounds = \App\Models\Compound::where('company_id', $record->id)->get();

                                if ($compounds->isEmpty()) {
                                    return 'No compounds assigned yet.';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($compounds as $compound) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                    $html .= '<div class="flex-1">';
                                    $html .= '<p class="font-semibold text-gray-900 dark:text-white">' . e($compound->project) . '</p>';
                                    if ($compound->location) {
                                        $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">Location: ' . e($compound->location) . '</p>';
                                    }
                                    $html .= '</div>';
                                    $html .= '<div class="text-right">';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">Units: ' . $compound->units()->count() . '</p>';
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
                    ->label('Logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Company Name'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts([
                        'users' => fn ($query) => $query->where('role', 'sales')
                    ])
                    ->label('Sales Team')
                    ->sortable(),
                Tables\Columns\TextColumn::make('number_of_compounds')
                    ->numeric()
                    ->sortable()
                    ->label('Compounds'),
                Tables\Columns\TextColumn::make('number_of_available_units')
                    ->numeric()
                    ->sortable()
                    ->label('Available Units'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Updated At')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_sales_team')
                    ->label('Has Sales Team')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereHas('users', fn ($q) => $q->where('role', 'sales'))
                    ),
                Tables\Filters\Filter::make('has_compounds')
                    ->label('Has Compounds')
                    ->query(fn (Builder $query): Builder =>
                        $query->where('number_of_compounds', '>', 0)
                    ),
                Tables\Filters\SelectFilter::make('compounds_range')
                    ->label('Compounds Range')
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
