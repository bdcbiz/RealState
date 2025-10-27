<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:buyer,sales,admin,company',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'is_verified' => false, // User not verified yet
            'email_verified_at' => null,
        ]);

        // Create email verification record
        $verification = EmailVerification::createForUser(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        // Send verification email
        try {
            $user->notify(new EmailVerificationNotification($verification));

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for the verification code.',
                'message_ar' => 'تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني للحصول على رمز التحقق.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'verification' => [
                        'email_sent' => true,
                        'expires_in_minutes' => 15,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            // Delete user if email sending fails
            $user->delete();

            \Log::error('Failed to send verification email', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Could not send verification email. Please try again.',
                'message_ar' => 'فشل التسجيل. تعذر إرسال بريد التحقق. حاول مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login user (supports both manual and Google login)
     */
    public function login(Request $request)
    {
        $loginMethod = $request->input('login_method', 'manual');

        if ($loginMethod === 'google') {
            return $this->handleGoogleLogin($request);
        } else {
            return $this->handleManualLogin($request);
        }
    }

    /**
     * Handle Google OAuth login with ID token verification
     */
    private function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            // Clean the token (remove whitespace, newlines, etc.)
            $token = trim($request->id_token);

            // Validate token format
            $tokenParts = explode('.', $token);
            $segmentCount = count($tokenParts);

            \Log::info('Google login attempt', [
                'segments_count' => $segmentCount,
                'token_length' => strlen($token),
            ]);

            // Handle different token types
            if ($segmentCount === 3) {
                // This is an ID token (JWT) - verify with Google Client
                \Log::info('Processing ID token (3 segments)');

                $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
                $payload = $client->verifyIdToken($token);

                if (!$payload) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid Google ID token',
                    ], 401);
                }

                // Extract user info from ID token payload
                $googleId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'] ?? 'Google User';
                $photoUrl = $payload['picture'] ?? null;
                $emailVerified = $payload['email_verified'] ?? false;

            } elseif ($segmentCount === 2 || $segmentCount === 1) {
                // This is an access token - call Google's userinfo API
                \Log::info('Processing access token (2 segments) - calling Google userinfo API');

                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token
                    ])->get('https://www.googleapis.com/oauth2/v2/userinfo');

                    if (!$response->successful()) {
                        \Log::error('Google userinfo API failed', [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);

                        throw new \Exception('Failed to get user info from Google');
                    }

                    $userData = $response->json();

                    // Extract user info from userinfo API response
                    $googleId = $userData['id'] ?? null;
                    $email = $userData['email'] ?? null;
                    $name = $userData['name'] ?? 'Google User';
                    $photoUrl = $userData['picture'] ?? null;
                    $emailVerified = $userData['verified_email'] ?? false;

                    if (!$googleId || !$email) {
                        throw new \Exception('Missing required user data from Google');
                    }

                    \Log::info('User info obtained from Google API', [
                        'email' => $email,
                        'name' => $name,
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Access token verification failed', [
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid access token: ' . $e->getMessage(),
                    ], 401);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format. Expected JWT with 3 segments or access token with 2 segments, got ' . $segmentCount . ' segments.',
                    'debug' => [
                        'token_segments' => $segmentCount,
                        'token_length' => strlen($token),
                    ],
                ], 400);
            }

            // Find or create user
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Create new user with verified Google account
                $user = User::create([
                    'name'          => $name,
                    'email'         => $email,
                    'google_id'     => $googleId,
                    'photo_url'     => $photoUrl,
                    'login_method'  => 'google',
                    'password'      => Hash::make(uniqid()), // random dummy password
                    'role'          => 'buyer', // default role
                    'is_verified'   => $emailVerified, // Use Google's email verification status
                ]);
            } else {
                // Update existing user with Google data
                $user->update([
                    'google_id'     => $googleId,
                    'photo_url'     => $photoUrl ?? $user->photo_url,
                    'login_method'  => 'google',
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle manual email/password login
     */
    private function handleManualLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Verify email with 6-digit code
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'message_ar' => 'المستخدم غير موجود',
            ], 404);
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified',
                'message_ar' => 'البريد الإلكتروني تم التحقق منه مسبقاً',
                'data' => [
                    'user' => $user,
                ],
            ], 200);
        }

        // Find valid verification record
        $verification = EmailVerification::findValidForUser($user);

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'No valid verification code found. Please request a new code.',
                'message_ar' => 'لا يوجد رمز تحقق صالح. يرجى طلب رمز جديد.',
            ], 400);
        }

        // Verify the code
        $verified = $verification->verify($request->code);

        if (!$verified) {
            $remainingAttempts = $verification->getRemainingAttempts();

            if ($verification->status === 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum attempts reached. Please request a new code.',
                    'message_ar' => 'تم الوصول إلى الحد الأقصى من المحاولات. يرجى طلب رمز جديد.',
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code',
                'message_ar' => 'رمز التحقق غير صحيح',
                'data' => [
                    'remaining_attempts' => $remainingAttempts,
                ],
            ], 400);
        }

        // Update user verification status
        $user->update(['is_verified' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'message_ar' => 'تم التحقق من البريد الإلكتروني بنجاح',
            'data' => [
                'user' => $user->fresh(),
            ],
        ], 200);
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'message_ar' => 'المستخدم غير موجود',
            ], 404);
        }

        // Check if already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
                'message_ar' => 'البريد الإلكتروني تم التحقق منه مسبقاً',
            ], 400);
        }

        // Check if there's a valid pending verification
        $existingVerification = EmailVerification::findValidForUser($user);
        if ($existingVerification && !$existingVerification->canResend()) {
            $waitTime = 60 - now()->diffInSeconds($existingVerification->created_at);
            return response()->json([
                'success' => false,
                'message' => "Please wait {$waitTime} seconds before resending",
                'message_ar' => "يرجى الانتظار {$waitTime} ثانية قبل إعادة الإرسال",
                'data' => [
                    'wait_time' => $waitTime,
                ],
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

            return response()->json([
                'success' => true,
                'message' => 'New verification code sent to your email',
                'message_ar' => 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني',
                'data' => [
                    'email' => $user->email,
                    'expires_in_minutes' => 15,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to resend verification email', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.',
                'message_ar' => 'فشل إرسال البريد. حاول مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
