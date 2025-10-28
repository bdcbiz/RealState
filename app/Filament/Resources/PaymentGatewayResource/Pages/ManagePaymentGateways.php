<?php

namespace App\Filament\Resources\PaymentGatewayResource\Pages;

use App\Filament\Resources\PaymentGatewayResource;
use App\Models\PaymentGateway;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ManagePaymentGateways extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PaymentGatewayResource::class;

    protected static string $view = 'filament.resources.payment-gateway-resource.pages.manage-payment-gateways';

    protected static ?string $title = 'إدارة بوابات الدفع';

    public string $activeTab = 'paysky';

    public ?array $stripeData = [];
    public ?array $payskyData = [];
    public ?array $paymobData = [];
    public ?array $fawryData = [];
    public ?array $geideaData = [];
    public ?array $easykashData = [];
    public ?array $afsData = [];

    public function mount(): void
    {
        $this->loadGatewaysData();
    }

    protected function loadGatewaysData(): void
    {
        // Load Stripe data
        $stripe = PaymentGateway::where('slug', 'stripe')->first();
        $this->stripeData = $this->getGatewayData($stripe, 'stripe', [
            'secret_key' => '',
            'publishable_key' => '',
            'webhook_secret' => '',
        ]);

        // Load PaySky data
        $paysky = PaymentGateway::where('slug', 'paysky')->first();
        $this->payskyData = $this->getGatewayData($paysky, 'paysky', [
            'merchant_id' => '',
            'terminal_id' => '',
            'secure_hash' => '',
        ], ['EG'], 'EGP');

        // Load PayMob data
        $paymob = PaymentGateway::where('slug', 'paymob')->first();
        $this->paymobData = $this->getGatewayData($paymob, 'paymob', [
            'api_key' => '',
            'integration_id' => '',
            'iframe_id' => '',
            'hmac_secret' => '',
        ], ['EG'], 'EGP');

        // Load Fawry data
        $fawry = PaymentGateway::where('slug', 'fawry')->first();
        $this->fawryData = $this->getGatewayData($fawry, 'fawry', [
            'merchant_code' => '',
            'security_key' => '',
        ], ['EG'], 'EGP');

        // Load Geidea data
        $geidea = PaymentGateway::where('slug', 'geidea')->first();
        $this->geideaData = $this->getGatewayData($geidea, 'geidea', [
            'merchant_key' => '',
            'password' => '',
            'merchant_id' => '',
        ], ['SA', 'AE', 'KW', 'QA', 'BH', 'OM'], 'SAR');

        // Load EasyKash data
        $easykash = PaymentGateway::where('slug', 'easykash')->first();
        $this->easykashData = $this->getGatewayData($easykash, 'easykash', [
            'api_key' => '',
            'hmac_secret' => '',
            'callback_url' => '',
            'redirect_url' => '',
        ], ['EG'], 'EGP');

        // Load AFS data
        $afs = PaymentGateway::where('slug', 'afs')->first();
        $this->afsData = $this->getGatewayData($afs, 'afs', [
            'merchant_id' => '',
            'api_password' => '',
            'api_username' => '',
        ]);

        // Fill forms
        $this->stripeForm->fill($this->stripeData);
        $this->payskyForm->fill($this->payskyData);
        $this->paymobForm->fill($this->paymobData);
        $this->fawryForm->fill($this->fawryData);
        $this->geideaForm->fill($this->geideaData);
        $this->easykashForm->fill($this->easykashData);
        $this->afsForm->fill($this->afsData);
    }

    protected function getGatewayData($gateway, $slug, $credentials, $countries = [], $currency = 'USD'): array
    {
        $data = [
            'is_active' => $gateway?->is_active ?? false,
            'is_default' => $gateway?->is_default ?? ($slug === 'stripe'),
            'is_test_mode' => $gateway?->is_test_mode ?? true,
            'countries' => $gateway?->countries ?? $countries,
            'currency' => $gateway?->currency ?? $currency,
            'description' => $gateway?->description ?? '',
        ];

        foreach ($credentials as $key => $default) {
            $data[$key] = $gateway ? $gateway->getCredential($key, $default) : $default;
        }

        return $data;
    }

    protected function getForms(): array
    {
        return [
            'stripeForm',
            'payskyForm',
            'paymobForm',
            'fawryForm',
            'geideaForm',
            'easykashForm',
            'afsForm',
        ];
    }


    // ============= FORMS =============

    public function stripeForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات Stripe')
                    ->description('بوابة Stripe العالمية للدفع الإلكتروني')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->helperText('للدول غير المخصصة')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->helperText('استخدام Test API Keys')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->helperText('اختر الدول (فارغ = عالمية)')
                            ->multiple()
                            ->options([
                                'SA' => '🇸🇦 السعودية', 'AE' => '🇦🇪 الإمارات', 'KW' => '🇰🇼 الكويت',
                                'QA' => '🇶🇦 قطر', 'BH' => '🇧🇭 البحرين', 'OM' => '🇴🇲 عمان',
                                'JO' => '🇯🇴 الأردن', 'LB' => '🇱🇧 لبنان', 'EG' => '🇪🇬 مصر',
                                'IQ' => '🇮🇶 العراق', 'PK' => '🇵🇰 باكستان', 'US' => '🇺🇸 أمريكا',
                            ])
                            ->searchable(),
                        TextInput::make('currency')->label('العملة الافتراضية')->placeholder('USD')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(2)->schema([
                            TextInput::make('secret_key')->label('Secret Key')->placeholder('sk_test_...')->password()->revealable(),
                            TextInput::make('publishable_key')->label('Publishable Key')->placeholder('pk_test_...'),
                        ]),
                        TextInput::make('webhook_secret')->label('Webhook Secret')->placeholder('whsec_...')->password()->revealable(),
                        Placeholder::make('docs')->content('الوثائق: https://stripe.com/docs'),
                    ])->columns(2),
            ])
            ->statePath('stripeData');
    }

    public function payskyForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات PaySky')
                    ->description('بوابة PaySky - مصر')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->multiple()
                            ->options(['EG' => '🇪🇬 مصر'])
                            ->default(['EG']),
                        TextInput::make('currency')->label('العملة')->default('EGP')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(3)->schema([
                            TextInput::make('merchant_id')->label('Merchant ID'),
                            TextInput::make('terminal_id')->label('Terminal ID'),
                            TextInput::make('secure_hash')->label('Secure Hash')->password()->revealable(),
                        ]),
                        Placeholder::make('docs')->content('للتواصل مع PaySky: support@paysky.io'),
                    ])->columns(2),
            ])
            ->statePath('payskyData');
    }

    public function paymobForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات PayMob')
                    ->description('بوابة PayMob - مصر')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->multiple()
                            ->options(['EG' => '🇪🇬 مصر'])
                            ->default(['EG']),
                        TextInput::make('currency')->label('العملة')->default('EGP')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(2)->schema([
                            TextInput::make('api_key')->label('API Key')->password()->revealable(),
                            TextInput::make('integration_id')->label('Integration ID'),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('iframe_id')->label('iFrame ID'),
                            TextInput::make('hmac_secret')->label('HMAC Secret')->password()->revealable(),
                        ]),
                        Placeholder::make('docs')->content('الوثائق: https://paymob.com/en/developers'),
                    ])->columns(2),
            ])
            ->statePath('paymobData');
    }

    public function fawryForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات Fawry')
                    ->description('بوابة فوري - مصر')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->multiple()
                            ->options(['EG' => '🇪🇬 مصر'])
                            ->default(['EG']),
                        TextInput::make('currency')->label('العملة')->default('EGP')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(2)->schema([
                            TextInput::make('merchant_code')->label('Merchant Code'),
                            TextInput::make('security_key')->label('Security Key')->password()->revealable(),
                        ]),
                        Placeholder::make('docs')->content('للتواصل مع Fawry: https://fawry.com'),
                    ])->columns(2),
            ])
            ->statePath('fawryData');
    }

    public function geideaForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات Geidea')
                    ->description('بوابة جيديا - السعودية والخليج')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->multiple()
                            ->options([
                                'SA' => '🇸🇦 السعودية', 'AE' => '🇦🇪 الإمارات', 'KW' => '🇰🇼 الكويت',
                                'QA' => '🇶🇦 قطر', 'BH' => '🇧🇭 البحرين', 'OM' => '🇴🇲 عمان',
                            ])
                            ->default(['SA']),
                        TextInput::make('currency')->label('العملة')->default('SAR')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(3)->schema([
                            TextInput::make('merchant_key')->label('Merchant Key')->password()->revealable(),
                            TextInput::make('password')->label('Password')->password()->revealable(),
                            TextInput::make('merchant_id')->label('Merchant ID'),
                        ]),
                        Placeholder::make('docs')->content('الوثائق: https://docs.geidea.net'),
                    ])->columns(2),
            ])
            ->statePath('geideaData');
    }

    public function easykashForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات EasyKash')
                    ->description('بوابة EasyKash - مصر 🇪🇬 | بوابة دفع مصرية متكاملة')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->helperText('EasyKash متاحة في مصر بشكل أساسي')
                            ->multiple()
                            ->options(['EG' => '🇪🇬 مصر - Egypt'])
                            ->default(['EG']),
                        TextInput::make('currency')->label('العملة')->default('EGP')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(2)->schema([
                            TextInput::make('api_key')
                                ->label('API Key')
                                ->helperText('مفتاح API للمصادقة في الطلب الأولي')
                                ->placeholder('مثال: 7l5qpkgntufqf9b6')
                                ->password()
                                ->revealable()
                                ->required(),
                            TextInput::make('hmac_secret')
                                ->label('HMAC Secret Key')
                                ->helperText('مطلوب للتحقق من صحة Callback من EasyKash')
                                ->placeholder('مثال: 8a0ff1dae002b7d76a8f8136272ece29')
                                ->password()
                                ->revealable()
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('callback_url')
                                ->label('Callback URL')
                                ->helperText('رابط استقبال تحديثات المعاملات من EasyKash')
                                ->placeholder('https://yourdomain.com/api/payment/easykash/callback')
                                ->url()
                                ->required(),
                            TextInput::make('redirect_url')
                                ->label('Redirect URL')
                                ->helperText('رابط إعادة التوجيه بعد إتمام الدفع')
                                ->placeholder('https://yourdomain.com/payment/success')
                                ->url(),
                        ]),
                        Placeholder::make('docs')
                            ->content('📚 الوثائق: https://easykash.gitbook.io/easykash-apis-documentation | 🌐 الموقع: https://www.easykash.net'),
                    ])->columns(2),
            ])
            ->statePath('easykashData');
    }

    public function afsForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات AFS (Mastercard)')
                    ->description('بوابة AFS Payment Gateway - Mastercard (عالمية)')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')->label('تفعيل البوابة')->inline(false),
                            Toggle::make('is_default')->label('البوابة الافتراضية')->inline(false),
                        ]),
                        Toggle::make('is_test_mode')->label('وضع الاختبار')->inline(false),
                        Select::make('countries')
                            ->label('الدول المخصصة')
                            ->helperText('اتركه فارغاً للاستخدام العالمي')
                            ->multiple()
                            ->options([
                                'SA' => '🇸🇦 السعودية', 'AE' => '🇦🇪 الإمارات', 'EG' => '🇪🇬 مصر',
                                'US' => '🇺🇸 أمريكا', 'GB' => '🇬🇧 بريطانيا',
                            ])
                            ->searchable(),
                        TextInput::make('currency')->label('العملة')->default('USD')->maxLength(3),
                        Textarea::make('description')->label('الوصف')->rows(2),
                        Grid::make(3)->schema([
                            TextInput::make('merchant_id')->label('Merchant ID'),
                            TextInput::make('api_username')->label('API Username'),
                            TextInput::make('api_password')->label('API Password')->password()->revealable(),
                        ]),
                        Placeholder::make('docs')->content('الوثائق: https://afs.gateway.mastercard.com/api/documentation'),
                    ])->columns(2),
            ])
            ->statePath('afsData');
    }


    public function updatedActiveTab(): void
    {
        // Refresh when tab changes
}

    // ============= HEADER ACTIONS =============

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_stripe')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'stripe')
                ->action(fn() => $this->saveGateway('stripe', 'Stripe', $this->stripeForm->getState())),

            Action::make('save_paysky')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'paysky')
                ->action(fn() => $this->saveGateway('paysky', 'PaySky', $this->payskyForm->getState())),

            Action::make('save_paymob')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'paymob')
                ->action(fn() => $this->saveGateway('paymob', 'PayMob', $this->paymobForm->getState())),

            Action::make('save_fawry')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'fawry')
                ->action(fn() => $this->saveGateway('fawry', 'Fawry', $this->fawryForm->getState())),

            Action::make('save_geidea')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'geidea')
                ->action(fn() => $this->saveGateway('geidea', 'Geidea', $this->geideaForm->getState())),

            Action::make('save_easykash')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'easykash')
                ->action(fn() => $this->saveGateway('easykash', 'EasyKash', $this->easykashForm->getState())),

            Action::make('save_afs')->label('حفظ')->icon('heroicon-o-check')
                ->visible(fn() => $this->activeTab === 'afs')
                ->action(fn() => $this->saveGateway('afs', 'AFS Mastercard', $this->afsForm->getState())),
        ];
    }

    // ============= SAVE METHODS =============

    protected function saveGateway(string $slug, string $name, array $data): void
    {
        // Extract credentials from data
        $credentials = [];
        $excludedKeys = ['is_active', 'is_default', 'is_test_mode', 'countries', 'currency', 'description'];
        
        foreach ($data as $key => $value) {
            if (!in_array($key, $excludedKeys)) {
                $credentials[$key] = $value;
            }
        }

        PaymentGateway::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'provider' => $slug,
                'description' => $data['description'] ?? '',
                'is_active' => $data['is_active'] ?? false,
                'is_default' => $data['is_default'] ?? false,
                'is_test_mode' => $data['is_test_mode'] ?? true,
                'countries' => $data['countries'] ?? [],
                'currency' => $data['currency'] ?? 'USD',
                'credentials' => $credentials,
            ]
        );

        // If set as default, remove default from others
        if ($data['is_default'] ?? false) {
            PaymentGateway::where('slug', '!=', $slug)->update(['is_default' => false]);
        }

        Notification::make()
            ->success()
            ->title("تم حفظ إعدادات {$name} بنجاح")
            ->body("تم تحديث إعدادات بوابة {$name}.")
            ->send();

        $this->loadGatewaysData();
    }

    // ============= PUBLIC SAVE METHODS (called from Blade) =============

    public function savePaysky(): void
    {
        $this->saveGateway('paysky', 'PaySky', $this->payskyForm->getState());
    }

    public function saveEasykash(): void
    {
        $this->saveGateway('easykash', 'EasyKash', $this->easykashForm->getState());
    }

    public function saveAfs(): void
    {
        $this->saveGateway('afs', 'AFS Mastercard', $this->afsForm->getState());
    }

    public function savePaymob(): void
    {
        $this->saveGateway('paymob', 'PayMob', $this->paymobForm->getState());
    }

    public function saveFawry(): void
    {
        $this->saveGateway('fawry', 'Fawry', $this->fawryForm->getState());
    }

    public function saveGeidea(): void
    {
        $this->saveGateway('geidea', 'Geidea', $this->geideaForm->getState());
    }

    public function saveStripe(): void
    {
        $this->saveGateway('stripe', 'Stripe', $this->stripeForm->getState());
    }
}
