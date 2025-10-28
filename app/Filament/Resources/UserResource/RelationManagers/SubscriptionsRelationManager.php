<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'الاشتراكات';

    protected static ?string $modelLabel = 'اشتراك';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    }),

                Forms\Components\DateTimePicker::make('started_at')
                    ->label('تاريخ البدء')
                    ->default(now())
                    ->required(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->helperText('اتركه فارغاً للاشتراكات غير المحدودة'),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                    ])
                    ->default('active')
                    ->required(),

                Forms\Components\TextInput::make('searches_used')
                    ->label('عدد البحوث المستخدمة')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('الباقة')
                    ->badge()
                    ->color('info')
                    ->sortable(),

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
                    ->label('البحوث')
                    ->description(function (UserSubscription $record) {
                        $limit = $record->subscriptionPlan->search_limit;
                        return $limit === -1 ? 'غير محدود' : "من {$limit}";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
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
                    })
                    ->sortable(),
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة اشتراك')
                    ->icon('heroicon-o-plus'),
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
