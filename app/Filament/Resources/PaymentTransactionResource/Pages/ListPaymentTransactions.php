<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Filament\Resources\PaymentTransactionResource;
use App\Filament\Resources\PaymentTransactionResource\Widgets\PaymentStatsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\RecentTransactionsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

    protected static ?string $title = 'معاملات الدفع';

    protected function getHeaderActions(): array
    {
        return [
            // No create action - transactions are created automatically
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentStatsWidget::class,
        ];
    }
}
