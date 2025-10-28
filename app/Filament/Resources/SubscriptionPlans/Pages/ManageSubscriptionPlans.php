<?php

namespace App\Filament\Resources\SubscriptionPlans\Pages;

use App\Filament\Resources\SubscriptionPlansResource;
use App\Models\SubscriptionFeature;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPricingTier;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageSubscriptionPlans extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected static string $resource = SubscriptionPlansResource::class;

    protected static string $view = 'filament.resources.subscription-plans.pages.manage-subscription-plans';

    protected static ?string $title = 'باقات الاشتراك';

    public string $activeTab = 'plans';

    public function table(Table $table): Table
    {
        $query = match ($this->activeTab) {
            'plans' => SubscriptionPlan::query(),
            'features' => SubscriptionFeature::query(),
            'pricing_tiers' => SubscriptionPricingTier::query(),
            default => SubscriptionPlan::query(),
        };

        $columns = match ($this->activeTab) {
            'plans' => $this->getPlanColumns(),
            'features' => $this->getFeatureColumns(),
            'pricing_tiers' => $this->getPricingTierColumns(),
            default => [],
        };

        return $table
            ->query($query)
            ->columns($columns)
            ->actions([
                EditAction::make()
                    ->form($this->getEditForm()),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_plan')
                ->label('إضافة باقة')
                ->icon('heroicon-o-plus')
                ->visible(fn() => $this->activeTab === 'plans')
                ->form([
                    TextInput::make('name')
                        ->label('اسم الباقة بالعربية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('name_en')
                        ->label('اسم الباقة بالإنجليزية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('slug')
                        ->label('المعرف الفريد')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('lite, growth, professional'),
                    Textarea::make('description')
                        ->label('الوصف بالعربية')
                        ->rows(3),
                    Textarea::make('description_en')
                        ->label('الوصف بالإنجليزية')
                        ->rows(3),
                    TextInput::make('monthly_price')
                        ->label('السعر الشهري')
                        ->numeric()
                        ->prefix('جنيه')
                        ->required(),
                    TextInput::make('yearly_price')
                        ->label('السعر السنوي')
                        ->numeric()
                        ->prefix('جنيه')
                        ->required(),
                    TextInput::make('max_users')
                        ->label('عدد المستخدمين المسموح')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    TextInput::make('icon')
                        ->label('الأيقونة')
                        ->placeholder('heroicon-o-star'),
                    ColorPicker::make('color')
                        ->label('اللون'),
                    TextInput::make('badge')
                        ->label('شارة بالعربية')
                        ->placeholder('الأكثر شعبية'),
                    TextInput::make('badge_en')
                        ->label('شارة بالإنجليزية')
                        ->placeholder('Most Popular'),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                    Toggle::make('is_featured')
                        ->label('مميزة')
                        ->default(false),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->numeric()
                        ->default(0),
                    Select::make('features')
                        ->label('الميزات')
                        ->multiple()
                        ->relationship('features', 'feature')
                        ->preload()
                        ->searchable()
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $features = $data['features'] ?? [];
                    unset($data['features']);

                    $plan = SubscriptionPlan::create($data);

                    if (!empty($features)) {
                        $plan->features()->attach($features);
                    }

                    Notification::make()
                        ->success()
                        ->title('تم إضافة الباقة بنجاح')
                        ->send();
                }),

            Action::make('add_feature')
                ->label('إضافة ميزة')
                ->icon('heroicon-o-plus')
                ->visible(fn() => $this->activeTab === 'features')
                ->form([
                    TextInput::make('feature')
                        ->label('الميزة بالعربية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('feature_en')
                        ->label('الميزة بالإنجليزية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('value')
                        ->label('القيمة بالعربية')
                        ->placeholder('غير محدود، 5 أشخاص'),
                    TextInput::make('value_en')
                        ->label('القيمة بالإنجليزية')
                        ->placeholder('Unlimited, 5 people'),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->numeric()
                        ->default(0),
                ])
                ->action(function (array $data) {
                    SubscriptionFeature::create($data);
                    Notification::make()
                        ->success()
                        ->title('تم إضافة الميزة بنجاح')
                        ->send();
                }),

            Action::make('add_pricing_tier')
                ->label('إضافة رسوم معاملات')
                ->icon('heroicon-o-plus')
                ->visible(fn() => $this->activeTab === 'pricing_tiers')
                ->form([
                    Select::make('subscription_plan_id')
                        ->label('الباقة')
                        ->options(SubscriptionPlan::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Select::make('type')
                        ->label('نوع الدفع')
                        ->options([
                            'bank_transfer' => 'تحويل بنكي',
                            'local_card' => 'بطاقة محلية',
                            'international_card' => 'بطاقة دولية',
                        ])
                        ->required(),
                    TextInput::make('name')
                        ->label('الاسم بالعربية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('name_en')
                        ->label('الاسم بالإنجليزية')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('percentage')
                        ->label('النسبة المئوية')
                        ->numeric()
                        ->suffix('%')
                        ->required(),
                    TextInput::make('fixed_fee')
                        ->label('الرسوم الثابتة')
                        ->numeric()
                        ->prefix('جنيه')
                        ->required(),
                    TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->numeric()
                        ->default(0),
                ])
                ->action(function (array $data) {
                    SubscriptionPricingTier::create($data);
                    Notification::make()
                        ->success()
                        ->title('تم إضافة رسوم المعاملات بنجاح')
                        ->send();
                }),
        ];
    }

    protected function getPlanColumns(): array
    {
        return [
            TextColumn::make('id')->label('المعرف')->sortable(),
            TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
            TextColumn::make('name_en')->label('الاسم الإنجليزي')->searchable()->sortable(),
            TextColumn::make('slug')->label('المعرف')->searchable()->badge(),
            TextColumn::make('monthly_price')->label('السعر الشهري')->money('EGP')->sortable(),
            TextColumn::make('yearly_price')->label('السعر السنوي')->money('EGP')->sortable(),
            TextColumn::make('max_users')->label('عدد المستخدمين')->sortable(),
            IconColumn::make('is_active')
                ->label('نشط')
                ->boolean(),
            IconColumn::make('is_featured')
                ->label('مميزة')
                ->boolean(),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
        ];
    }

    protected function getFeatureColumns(): array
    {
        return [
            TextColumn::make('id')->label('المعرف')->sortable(),
            TextColumn::make('feature')->label('الميزة')->searchable()->sortable(),
            TextColumn::make('feature_en')->label('الميزة (EN)')->searchable()->sortable(),
            TextColumn::make('value')->label('القيمة')->searchable(),
            TextColumn::make('value_en')->label('القيمة (EN)')->searchable(),
            TextColumn::make('subscriptionPlans.name')
                ->label('الباقات')
                ->badge()
                ->separator(','),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
        ];
    }

    protected function getPricingTierColumns(): array
    {
        return [
            TextColumn::make('id')->label('المعرف')->sortable(),
            TextColumn::make('subscriptionPlan.name')->label('الباقة')->searchable()->sortable(),
            TextColumn::make('type')->label('النوع')->badge()->searchable(),
            TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
            TextColumn::make('percentage')->label('النسبة %')->sortable(),
            TextColumn::make('fixed_fee')->label('الرسوم الثابتة')->money('EGP')->sortable(),
            TextColumn::make('sort_order')->label('الترتيب')->sortable(),
        ];
    }

    protected function getEditForm(): array
    {
        return match ($this->activeTab) {
            'plans' => [
                TextInput::make('name')->label('اسم الباقة بالعربية')->required(),
                TextInput::make('name_en')->label('اسم الباقة بالإنجليزية')->required(),
                TextInput::make('slug')->label('المعرف الفريد')->required(),
                Textarea::make('description')->label('الوصف بالعربية')->rows(3),
                Textarea::make('description_en')->label('الوصف بالإنجليزية')->rows(3),
                TextInput::make('monthly_price')->label('السعر الشهري')->numeric()->prefix('جنيه')->required(),
                TextInput::make('yearly_price')->label('السعر السنوي')->numeric()->prefix('جنيه')->required(),
                TextInput::make('max_users')->label('عدد المستخدمين')->numeric()->required(),
                TextInput::make('icon')->label('الأيقونة'),
                ColorPicker::make('color')->label('اللون'),
                TextInput::make('badge')->label('شارة بالعربية'),
                TextInput::make('badge_en')->label('شارة بالإنجليزية'),
                Toggle::make('is_active')->label('نشط'),
                Toggle::make('is_featured')->label('مميزة'),
                TextInput::make('sort_order')->label('الترتيب')->numeric(),
                Select::make('features')
                    ->label('الميزات')
                    ->multiple()
                    ->relationship('features', 'feature')
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
            ],
            'features' => [
                TextInput::make('feature')->label('الميزة بالعربية')->required(),
                TextInput::make('feature_en')->label('الميزة بالإنجليزية')->required(),
                TextInput::make('value')->label('القيمة بالعربية'),
                TextInput::make('value_en')->label('القيمة بالإنجليزية'),
                TextInput::make('sort_order')->label('الترتيب')->numeric(),
            ],
            'pricing_tiers' => [
                Select::make('subscription_plan_id')
                    ->label('الباقة')
                    ->options(SubscriptionPlan::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('type')
                    ->label('نوع الدفع')
                    ->options([
                        'bank_transfer' => 'تحويل بنكي',
                        'local_card' => 'بطاقة محلية',
                        'international_card' => 'بطاقة دولية',
                    ])
                    ->required(),
                TextInput::make('name')->label('الاسم بالعربية')->required(),
                TextInput::make('name_en')->label('الاسم بالإنجليزية')->required(),
                TextInput::make('percentage')->label('النسبة المئوية')->numeric()->suffix('%')->required(),
                TextInput::make('fixed_fee')->label('الرسوم الثابتة')->numeric()->prefix('جنيه')->required(),
                TextInput::make('sort_order')->label('الترتيب')->numeric(),
            ],
            default => [],
        };
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }
}
