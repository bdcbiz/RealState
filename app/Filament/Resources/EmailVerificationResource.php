<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailVerifications\Pages;
use App\Filament\Resources\EmailVerifications\Tables\EmailVerificationsTable;
use App\Models\EmailVerification;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class EmailVerificationResource extends Resource
{
    protected static ?string $model = EmailVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'التحقق من البريد الإلكتروني';

    protected static ?string $modelLabel = 'عملية تحقق';

    protected static ?string $pluralModelLabel = 'عمليات التحقق من البريد';

    protected static ?int $navigationSort = 9;

    public static function table(Table $table): Table
    {
        return EmailVerificationsTable::configure($table);
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
            'index' => Pages\ManageEmailVerifications::route('/'),
        ];
    }
}
