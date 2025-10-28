<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSubscriptionResource\Pages;
use App\Filament\Resources\UserSubscriptionResource\RelationManagers;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'اشتراكات العملاء';

    protected static ?string $modelLabel = 'اشتراك';

    protected static ?string $pluralModelLabel = 'اشتراكات العملاء';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'النظام';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الاشتراك')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('العميل')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('subscription_plan_id')
                            ->label('الباقة')
                            ->relationship('subscriptionPlan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $plan = SubscriptionPlan::find($state);
                                    if ($plan && $plan->validity_days > 0) {
                                        $set('expires_at', now()->addDays($plan->validity_days));
                                    }
                                }
                            })
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('تاريخ البدء')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->helperText('اتركه فارغاً للاشتراكات غير المحدودة')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'expired' => 'منتهي',
                                'cancelled' => 'ملغي',
                                'pending' => 'قيد الانتظار',
                            ])
                            ->default('active')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('searches_used')
                            ->label('عدد البحوث المستخدمة')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'cancelled',
                        'secondary' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('searches_used')
                    ->label('البحوث المستخدمة')
                    ->sortable()
                    ->description(function (UserSubscription $record) {
                        $limit = $record->subscriptionPlan->search_limit;
                        return $limit === -1 ? 'غير محدود' : "من {$limit}";
                    }),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('تاريخ البدء')
                    ->dateTime('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->placeholder('غير محدود')
                    ->color(function ($record) {
                        if (!$record->expires_at) {
                            return 'success';
                        }
                        if ($record->expires_at->isPast()) {
                            return 'danger';
                        }
                        if ($record->expires_at->diffInDays() <= 7) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('الباقة')
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('تفعيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (UserSubscription $record) => $record->status !== 'active')
                    ->requiresConfirmation()
                    ->action(fn (UserSubscription $record) => $record->update(['status' => 'active'])),

                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (UserSubscription $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (UserSubscription $record) => $record->markAsCancelled()),
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
            'index' => Pages\ListUserSubscriptions::route('/'),
            'create' => Pages\CreateUserSubscription::route('/create'),
            'edit' => Pages\EditUserSubscription::route('/{record}/edit'),
        ];
    }
}
