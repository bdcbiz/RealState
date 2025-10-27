<?php

namespace App\Notifications;

use App\Models\EmailVerification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{

    public EmailVerification $verification;

    /**
     * Create a new notification instance.
     */
    public function __construct(EmailVerification $verification)
    {
        $this->verification = $verification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'Real Estate');
        $code = $this->verification->code;
        $expiresIn = $this->verification->getRemainingTime();
        $expiresMinutes = ceil($expiresIn / 60);

        // Default template
        return (new MailMessage)
            ->subject('رمز التحقق من البريد الإلكتروني - Email Verification Code')
            ->greeting("مرحباً {$notifiable->name}!")
            ->line('شكراً لتسجيلك في ' . $appName)
            ->line('Thank you for registering with ' . $appName)
            ->line('')
            ->line('**رمز التحقق الخاص بك:**')
            ->line('**Your verification code:**')
            ->line('')
            ->line("# **{$code}**")
            ->line('')
            ->line("⏰ هذا الرمز صالح لمدة {$expiresMinutes} دقيقة فقط")
            ->line("⏰ This code is valid for {$expiresMinutes} minutes only")
            ->line('')
            ->line('⚠️ إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.')
            ->line('⚠️ If you did not request this code, please ignore this message.')
            ->line('')
            ->line('مع أطيب التحيات')
            ->line('Best regards')
            ->salutation($appName . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'verification_id' => $this->verification->id,
            'code' => $this->verification->code,
            'expires_at' => $this->verification->expires_at,
        ];
    }
}
