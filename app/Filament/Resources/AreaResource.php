<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'إدارة العقارات';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'المناطق';
    }

    public static function getModelLabel(): string
    {
        return 'منطقة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المناطق';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المنطقة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المنطقة')
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

                        Forms\Components\Select::make('city_id')
                            ->label('المدينة')
                            ->relationship('city', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم المدينة')
                                    ->required(),
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم بالعربية'),
                                Forms\Components\TextInput::make('governorate_ar')
                                    ->label('المحافظة'),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الشركات')
                    ->schema([
                        Forms\Components\Select::make('companies')
                            ->label('الشركات العاملة في هذه المنطقة')
                            ->relationship('companies', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
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
                    ->label('اسم المنطقة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('المدينة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('companies_count')
                    ->counts('companies')
                    ->label('عدد الشركات')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('companies')
                    ->label('الشركات')
                    ->getStateUsing(function ($record) {
                        return $record->companies->take(3)->pluck('name')->toArray();
                    })
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->tooltip(function ($record) {
                        $companies = $record->companies;
                        if ($companies->count() > 3) {
                            return $companies->pluck('name')->join(', ');
                        }
                        return null;
                    }),

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
            ->defaultSort('companies_count', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_companies')
                    ->label('لديها شركات')
                    ->query(fn (Builder $query): Builder =>
                        $query->has('companies')
                    ),

                Tables\Filters\SelectFilter::make('companies_count')
                    ->label('عدد الشركات')
                    ->options([
                        '1-5' => '1 - 5 شركات',
                        '6-10' => '6 - 10 شركات',
                        '11-20' => '11 - 20 شركة',
                        '20+' => 'أكثر من 20 شركة',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($q, $value) {
                            if ($value === '20+') {
                                return $q->withCount('companies')->having('companies_count', '>=', 20);
                            }
                            [$min, $max] = explode('-', $value);
                            return $q->withCount('companies')->havingBetween('companies_count', [(int)$min, (int)$max]);
                        });
                    }),
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
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
