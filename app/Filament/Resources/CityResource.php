<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\RelationManagers;
use App\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'إدارة العقارات';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'المدن';
    }

    public static function getModelLabel(): string
    {
        return 'مدينة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المدن';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المدينة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المدينة')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('name_ar')
                            ->label('الاسم بالعربية')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('name_en')
                            ->label('الاسم بالإنجليزية')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('يتم إنشاؤه تلقائياً من الاسم إذا ترك فارغاً')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('governorate')
                            ->label('المحافظة (EN)')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('governorate_ar')
                            ->label('المحافظة (AR)')
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('المناطق')
                    ->schema([
                        Forms\Components\Placeholder::make('areas_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) {
                                    return 'احفظ المدينة أولاً لعرض المناطق المرتبطة بها';
                                }

                                $areas = \App\Models\Area::where('city_id', $record->id)->get();

                                if ($areas->isEmpty()) {
                                    return 'لا توجد مناطق في هذه المدينة بعد';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($areas as $area) {
                                    $html .= '<div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                    $html .= '<div class="flex-1">';
                                    $html .= '<p class="font-semibold text-gray-900 dark:text-white">' . e($area->name) . '</p>';
                                    $html .= '</div>';
                                    $html .= '<div class="text-right">';
                                    $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">الشركات: ' . $area->companies()->count() . '</p>';
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
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المدينة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('governorate_ar')
                    ->label('المحافظة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('areas_count')
                    ->counts('areas')
                    ->label('عدد المناطق')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('areas_count', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_areas')
                    ->label('لديها مناطق')
                    ->query(fn (Builder $query): Builder =>
                        $query->has('areas')
                    ),

                Tables\Filters\SelectFilter::make('governorate')
                    ->label('المحافظة')
                    ->options(function () {
                        return \App\Models\City::whereNotNull('governorate_ar')
                            ->distinct()
                            ->pluck('governorate_ar', 'governorate_ar')
                            ->toArray();
                    })
                    ->searchable(),
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
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
        ];
    }
}
