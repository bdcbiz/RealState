<?php

namespace App\Filament\Resources\EmailVerifications\Tables;

use App\Models\User;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailVerificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('المعرف')
                    ->sortable(),

                TextColumn::make('user_type')
                    ->label('نوع المستخدم')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::class => 'مستخدم',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        User::class => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('user_id')
                    ->label('معرف المستخدم')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('code')
                    ->label('رمز التحقق')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                IconColumn::make('verified_at')
                    ->label('تم التحقق')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('expires_at')
                    ->label('تنتهي في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'success'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label('نوع المستخدم')
                    ->options([
                        User::class => 'المستخدمين',
                    ]),

                SelectFilter::make('verified')
                    ->label('حالة التحقق')
                    ->query(fn (Builder $query, array $data) =>
                        $data['value'] === 'verified'
                            ? $query->whereNotNull('verified_at')
                            : $query->whereNull('verified_at')
                    )
                    ->options([
                        'verified' => 'تم التحقق',
                        'pending' => 'في الانتظار',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
