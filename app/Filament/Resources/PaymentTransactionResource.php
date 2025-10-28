<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Filament\Resources\PaymentTransactionResource\RelationManagers;
use App\Models\PaymentTransaction;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'معاملات الدفع';

    protected static ?string $modelLabel = 'معاملة دفع';

    protected static ?string $pluralModelLabel = 'معاملات الدفع';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'المدفوعات';

    protected static ?string $recordTitleAttribute = 'transaction_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('معرف المعاملة')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-hashtag'),

                Tables\Columns\TextColumn::make('paymentGateway.name')
                    ->label('بوابة الدفع')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'EasyKash' => 'success',
                        'Stripe' => 'info',
                        'PayMob' => 'warning',
                        default => 'gray',
                    })
                    ->icon('heroicon-o-building-library'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'processing' => 'info',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'refunded' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => 'ناجح ✓',
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'failed' => 'فشل ✗',
                        'cancelled' => 'ملغى',
                        'refunded' => 'مسترجع',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money(fn (PaymentTransaction $record): string => $record->currency)
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('currency')
                    ->label('العملة')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->placeholder('غير محدد')
                    ->color('info')
                    ->icon('heroicon-o-credit-card'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable()
                    ->placeholder('غير محدد')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('gateway_transaction_id')
                    ->label('معرف البوابة')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير متوفر')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fee')
                    ->label('الرسوم')
                    ->money(fn (PaymentTransaction $record): string => $record->currency)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('المبلغ الصافي')
                    ->money(fn (PaymentTransaction $record): string => $record->currency)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('لم يتم الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success'),

                Tables\Columns\TextColumn::make('failed_at')
                    ->label('تاريخ الفشل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('failure_reason')
                    ->label('سبب الفشل')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('-')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by status
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'success' => 'ناجح',
                        'failed' => 'فشل',
                        'cancelled' => 'ملغى',
                        'refunded' => 'مسترجع',
                    ])
                    ->multiple()
                    ->placeholder('جميع الحالات'),

                // Filter by payment gateway
                SelectFilter::make('payment_gateway_id')
                    ->label('بوابة الدفع')
                    ->relationship('paymentGateway', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('جميع البوابات'),

                // Filter by currency
                SelectFilter::make('currency')
                    ->label('العملة')
                    ->options([
                        'EGP' => '🇪🇬 جنيه مصري (EGP)',
                        'SAR' => '🇸🇦 ريال سعودي (SAR)',
                        'USD' => '🇺🇸 دولار أمريكي (USD)',
                        'AED' => '🇦🇪 درهم إماراتي (AED)',
                        'KWD' => '🇰🇼 دينار كويتي (KWD)',
                    ])
                    ->multiple()
                    ->placeholder('جميع العملات'),

                // Filter by amount range
                Filter::make('amount')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('amount_from')
                                ->label('المبلغ من')
                                ->numeric()
                                ->placeholder('0.00'),
                            Forms\Components\TextInput::make('amount_to')
                                ->label('المبلغ إلى')
                                ->numeric()
                                ->placeholder('1000.00'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['amount_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('المبلغ من: ' . $data['amount_from'])
                                ->removeField('amount_from');
                        }

                        if ($data['amount_to'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('المبلغ إلى: ' . $data['amount_to'])
                                ->removeField('amount_to');
                        }

                        return $indicators;
                    }),

                // Filter by date range
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ')
                            ->placeholder('اختر التاريخ'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ')
                            ->placeholder('اختر التاريخ'),
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
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('من: ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y'))
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('إلى: ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y'))
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),

                // Filter by payment method
                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'card' => 'بطاقة ائتمان',
                        'wallet' => 'محفظة إلكترونية',
                        'cash' => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                    ])
                    ->multiple()
                    ->placeholder('جميع الطرق'),

                // Only successful transactions
                Filter::make('successful')
                    ->label('المعاملات الناجحة فقط')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'success'))
                    ->toggle(),

                // Only failed transactions
                Filter::make('failed')
                    ->label('المعاملات الفاشلة فقط')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'failed'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->label('تصدير')
                        ->icon('heroicon-o-arrow-down-tray'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s') // Auto-refresh every 30 seconds
            ->striped()
            ->emptyStateHeading('لا توجد معاملات دفع')
            ->emptyStateDescription('ستظهر معاملات الدفع هنا بمجرد إجرائها')
            ->emptyStateIcon('heroicon-o-credit-card');
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
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المعاملة')
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_id')
                            ->label('معرف المعاملة')
                            ->icon('heroicon-o-hashtag')
                            ->copyable()
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('gateway_transaction_id')
                            ->label('معرف البوابة')
                            ->icon('heroicon-o-key')
                            ->placeholder('غير متوفر')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'success' => 'success',
                                'pending' => 'warning',
                                'processing' => 'info',
                                'failed' => 'danger',
                                'cancelled' => 'gray',
                                'refunded' => 'purple',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'success' => 'ناجح ✓',
                                'pending' => 'قيد الانتظار',
                                'processing' => 'قيد المعالجة',
                                'failed' => 'فشل ✗',
                                'cancelled' => 'ملغى',
                                'refunded' => 'مسترجع',
                                default => $state,
                            }),
                    ]),

                Infolists\Components\Section::make('المبالغ المالية')
                    ->icon('heroicon-o-banknotes')
                    ->columns(4)
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->label('المبلغ الإجمالي')
                            ->money(fn ($record): string => $record->currency)
                            ->color('success')
                            ->size('lg')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('fee')
                            ->label('الرسوم')
                            ->money(fn ($record): string => $record->currency)
                            ->color('warning')
                            ->placeholder('0.00'),

                        Infolists\Components\TextEntry::make('net_amount')
                            ->label('المبلغ الصافي')
                            ->money(fn ($record): string => $record->currency)
                            ->color('info')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('currency')
                            ->label('العملة')
                            ->badge(),
                    ]),

                Infolists\Components\Section::make('بوابة الدفع')
                    ->icon('heroicon-o-building-library')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('paymentGateway.name')
                            ->label('اسم البوابة')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('طريقة الدفع')
                            ->badge()
                            ->placeholder('غير محدد')
                            ->icon('heroicon-o-credit-card'),

                        Infolists\Components\TextEntry::make('paymentGateway.currency')
                            ->label('عملة البوابة')
                            ->badge(),
                    ]),

                Infolists\Components\Section::make('معلومات العميل')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('اسم المستخدم')
                            ->icon('heroicon-o-user')
                            ->placeholder('غير محدد'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('غير محدد')
                            ->copyable(),

                        Infolists\Components\KeyValueEntry::make('customer_data')
                            ->label('بيانات العميل')
                            ->columnSpanFull()
                            ->placeholder('لا توجد بيانات')
                            ->hidden(fn ($record) => empty($record->customer_data)),
                    ]),

                Infolists\Components\Section::make('التفاصيل')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('الوصف')
                            ->columnSpanFull()
                            ->placeholder('لا يوجد وصف'),

                        Infolists\Components\TextEntry::make('failure_reason')
                            ->label('سبب الفشل')
                            ->columnSpanFull()
                            ->color('danger')
                            ->placeholder('لا يوجد')
                            ->visible(fn ($record) => !empty($record->failure_reason)),
                    ]),

                Infolists\Components\Section::make('التواريخ')
                    ->icon('heroicon-o-calendar')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-clock'),

                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->placeholder('لم يتم الدفع'),

                        Infolists\Components\TextEntry::make('failed_at')
                            ->label('تاريخ الفشل')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->placeholder('-')
                            ->visible(fn ($record) => !empty($record->failed_at)),

                        Infolists\Components\TextEntry::make('refunded_at')
                            ->label('تاريخ الاسترجاع')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-arrow-uturn-left')
                            ->color('purple')
                            ->placeholder('-')
                            ->visible(fn ($record) => !empty($record->refunded_at)),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('d/m/Y H:i:s')
                            ->icon('heroicon-o-arrow-path'),
                    ]),

                Infolists\Components\Section::make('البيانات التقنية')
                    ->icon('heroicon-o-code-bracket')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('request_data')
                            ->label('بيانات الطلب')
                            ->columnSpanFull()
                            ->placeholder('لا توجد بيانات')
                            ->hidden(fn ($record) => empty($record->request_data)),

                        Infolists\Components\KeyValueEntry::make('response_data')
                            ->label('بيانات الاستجابة')
                            ->columnSpanFull()
                            ->placeholder('لا توجد بيانات')
                            ->hidden(fn ($record) => empty($record->response_data)),

                        Infolists\Components\KeyValueEntry::make('callback_data')
                            ->label('بيانات Callback')
                            ->columnSpanFull()
                            ->placeholder('لا توجد بيانات')
                            ->hidden(fn ($record) => empty($record->callback_data)),
                    ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false; // Prevent manual creation of transactions
    }

    public static function canEdit($record): bool
    {
        return false; // Prevent manual editing of transactions
    }

    public static function canDelete($record): bool
    {
        return false; // Prevent deletion of transactions
    }
}
