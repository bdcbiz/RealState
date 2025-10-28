<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlans\Pages;
use Filament\Resources\Resource;

class SubscriptionPlansResource extends Resource
{
    protected static ?string $model = \App\Models\SubscriptionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'باقات الاشتراك';

    protected static ?string $modelLabel = 'باقة';

    protected static ?string $pluralModelLabel = 'باقات الاشتراك';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'النظام';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSubscriptionPlans::route('/'),
        ];
    }
}
