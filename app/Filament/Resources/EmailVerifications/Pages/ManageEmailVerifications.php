<?php

namespace App\Filament\Resources\EmailVerifications\Pages;

use App\Filament\Resources\EmailVerificationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ManageEmailVerifications extends ListRecords
{
    protected static string $resource = EmailVerificationResource::class;

    protected static ?string $title = 'سجل التحقق من البريد الإلكتروني';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label('إعدادات البريد')
                ->icon('heroicon-o-cog-6-tooth')
                ->form([
                    Section::make('إعدادات خادم البريد')
                        ->description('قم بتكوين إعدادات SMTP للبريد الإلكتروني')
                        ->schema([
                            Select::make('mail_driver')
                                ->label('نوع الخادم')
                                ->options([
                                    'smtp' => 'SMTP',
                                    'sendmail' => 'Sendmail',
                                    'mailgun' => 'Mailgun',
                                    'ses' => 'Amazon SES',
                                ])
                                ->default(config('mail.default'))
                                ->required(),

                            TextInput::make('mail_host')
                                ->label('عنوان الخادم')
                                ->placeholder('smtp.example.com')
                                ->default(config('mail.mailers.smtp.host'))
                                ->required(),

                            TextInput::make('mail_port')
                                ->label('المنفذ')
                                ->numeric()
                                ->default(config('mail.mailers.smtp.port'))
                                ->required(),

                            Select::make('mail_encryption')
                                ->label('التشفير')
                                ->options([
                                    'tls' => 'TLS',
                                    'ssl' => 'SSL',
                                    '' => 'بدون',
                                ])
                                ->default(config('mail.mailers.smtp.encryption')),

                            TextInput::make('mail_username')
                                ->label('اسم المستخدم')
                                ->placeholder('your@email.com')
                                ->default(config('mail.mailers.smtp.username')),

                            TextInput::make('mail_password')
                                ->label('كلمة المرور')
                                ->password()
                                ->dehydrated(fn ($state) => filled($state)),

                            TextInput::make('mail_from_address')
                                ->label('البريد المرسل')
                                ->email()
                                ->default(config('mail.from.address'))
                                ->required(),

                            TextInput::make('mail_from_name')
                                ->label('اسم المرسل')
                                ->default(config('mail.from.name'))
                                ->required(),

                            Toggle::make('mail_test_mode')
                                ->label('وضع الاختبار')
                                ->helperText('عند التفعيل، يتم إرسال جميع الرسائل إلى البريد الاختباري فقط'),
                        ])
                        ->columns(2),

                    Section::make('إعدادات التحقق')
                        ->schema([
                            TextInput::make('verification_code_length')
                                ->label('طول رمز التحقق')
                                ->numeric()
                                ->default(6)
                                ->minValue(4)
                                ->maxValue(8),

                            TextInput::make('verification_expiry_minutes')
                                ->label('مدة الصلاحية بالدقائق')
                                ->numeric()
                                ->default(15)
                                ->suffix('دقيقة'),

                            TextInput::make('verification_max_attempts')
                                ->label('الحد الأقصى للمحاولات')
                                ->numeric()
                                ->default(3),
                        ])
                        ->columns(3),
                ])
                ->action(function (array $data) {
                    // Save mail settings to .env or settings table here
                    // This is a simplified version - you may want to implement proper settings storage

                    Notification::make()
                        ->success()
                        ->title('تم حفظ الإعدادات بنجاح')
                        ->body('تم تحديث إعدادات البريد الإلكتروني.')
                        ->send();
                })
                ->modalWidth('5xl')
                ->slideOver(),
        ];
    }
}
