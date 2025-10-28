<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\PaymentGateway;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PaymentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get all transactions
        $totalTransactions = PaymentTransaction::count();
        $successfulTransactions = PaymentTransaction::where('status', 'success')->count();
        $pendingTransactions = PaymentTransaction::where('status', 'pending')->count();
        $failedTransactions = PaymentTransaction::where('status', 'failed')->count();

        // Get total amounts
        $totalAmount = PaymentTransaction::where('status', 'success')->sum('amount');
        $pendingAmount = PaymentTransaction::where('status', 'pending')->sum('amount');

        // Get today's stats
        $todayTransactions = PaymentTransaction::whereDate('created_at', today())->count();
        $todaySuccess = PaymentTransaction::whereDate('created_at', today())
            ->where('status', 'success')
            ->count();
        $todayAmount = PaymentTransaction::whereDate('created_at', today())
            ->where('status', 'success')
            ->sum('amount');

        // Calculate success rate
        $successRate = $totalTransactions > 0
            ? round(($successfulTransactions / $totalTransactions) * 100, 1)
            : 0;

        // Get most used gateway
        $mostUsedGateway = PaymentTransaction::select('payment_gateway_id')
            ->selectRaw('count(*) as total')
            ->groupBy('payment_gateway_id')
            ->orderByDesc('total')
            ->with('paymentGateway')
            ->first();

        return [
            Stat::make('إجمالي المعاملات', Number::format($totalTransactions))
                ->description('جميع المعاملات المسجلة')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info')
                ->chart($this->getTransactionsTrend()),

            Stat::make('معاملات ناجحة', Number::format($successfulTransactions))
                ->description("نسبة النجاح: {$successRate}%")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('إجمالي المبالغ', 'EGP ' . Number::format($totalAmount, 2))
                ->description('المبالغ المدفوعة بنجاح')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('قيد الانتظار', Number::format($pendingTransactions))
                ->description('EGP ' . Number::format($pendingAmount, 2))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('معاملات فاشلة', Number::format($failedTransactions))
                ->description($failedTransactions > 0 ? 'تحتاج للمراجعة' : 'لا توجد معاملات فاشلة')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('اليوم', Number::format($todayTransactions) . ' معاملة')
                ->description("{$todaySuccess} ناجحة - EGP " . Number::format($todayAmount, 2))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info')
                ->chart($this->getTodayHourlyTrend()),
        ];
    }

    protected function getTransactionsTrend(): array
    {
        // Get last 7 days transactions
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = PaymentTransaction::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    protected function getTodayHourlyTrend(): array
    {
        // Get last 7 hours transactions
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $hour = now()->subHours($i)->startOfHour();
            $count = PaymentTransaction::whereBetween('created_at', [
                $hour,
                $hour->copy()->endOfHour()
            ])->count();
            $data[] = $count;
        }
        return $data;
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
    }
}
