<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    /**
     * Send verification email
     */
    public function sendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني تم التحقق منه مسبقاً',
                'message_en' => 'Email already verified',
            ], 400);
        }

        // Check if there's a valid pending verification
        $existingVerification = EmailVerification::findValidForUser($user);
        if ($existingVerification && !$existingVerification->canResend()) {
            $waitTime = 60 - now()->diffInSeconds($existingVerification->created_at);
            return response()->json([
                'success' => false,
                'message' => "يرجى الانتظار {$waitTime} ثانية قبل إعادة إرسال الرمز",
                'message_en' => "Please wait {$waitTime} seconds before resending",
                'wait_time' => $waitTime,
            ], 429);
        }

        // Create new verification
        $verification = EmailVerification::createForUser(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Send email notification
        try {
            $user->notify(new EmailVerificationNotification($verification));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال البريد الإلكتروني. حاول مرة أخرى',
                'message_en' => 'Failed to send email. Please try again',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
            'message_en' => 'Verification code sent to your email',
            'data' => [
                'email' => $user->email,
                'expires_in_minutes' => 15,
                'can_resend_in_seconds' => 60,
            ],
        ]);
    }

    /**
     * Verify email with code
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال رمز التحقق المكون من 6 أرقام',
                'message_en' => 'Please enter the 6-digit verification code',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'البريد الإلكتروني تم التحقق منه مسبقاً',
                'message_en' => 'Email already verified',
            ], 400);
        }

        // Find valid verification
        $verification = EmailVerification::findValidForUser($user);

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد رمز تحقق صالح. يرجى طلب رمز جديد',
                'message_en' => 'No valid verification code found. Please request a new code',
            ], 404);
        }

        // Verify the code
        if ($verification->verify($request->code)) {
            return response()->json([
                'success' => true,
                'message' => 'تم التحقق من بريدك الإلكتروني بنجاح',
                'message_en' => 'Email verified successfully',
                'data' => [
                    'email' => $user->email,
                    'verified_at' => $user->fresh()->email_verified_at,
                ],
            ]);
        }

        // Verification failed
        $remainingAttempts = $verification->getRemainingAttempts();

        if ($remainingAttempts === 0) {
            return response()->json([
                'success' => false,
                'message' => 'تم تجاوز الحد الأقصى للمحاولات. يرجى طلب رمز جديد',
                'message_en' => 'Maximum attempts exceeded. Please request a new code',
            ], 429);
        }

        return response()->json([
            'success' => false,
            'message' => "رمز التحقق غير صحيح. لديك {$remainingAttempts} محاولة متبقية",
            'message_en' => "Incorrect verification code. You have {$remainingAttempts} attempts left",
            'remaining_attempts' => $remainingAttempts,
        ], 400);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        return $this->sendVerificationEmail($request);
    }

    /**
     * Check verification status
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'is_verified' => true,
                'verified_at' => $user->email_verified_at,
                'message' => 'البريد الإلكتروني تم التحقق منه',
                'message_en' => 'Email is verified',
            ]);
        }

        $verification = EmailVerification::findValidForUser($user);

        if ($verification) {
            return response()->json([
                'success' => true,
                'is_verified' => false,
                'has_pending_verification' => true,
                'expires_in_seconds' => $verification->getRemainingTime(),
                'remaining_attempts' => $verification->getRemainingAttempts(),
                'can_resend' => $verification->canResend(),
                'message' => 'لديك رمز تحقق قيد الانتظار',
                'message_en' => 'You have a pending verification code',
            ]);
        }

        return response()->json([
            'success' => true,
            'is_verified' => false,
            'has_pending_verification' => false,
            'message' => 'لا يوجد رمز تحقق قيد الانتظار',
            'message_en' => 'No pending verification code',
        ]);
    }
}
