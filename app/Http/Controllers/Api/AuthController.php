<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'role' => 'required|string|in:buyer,company',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));
        $verificationExpiry = now()->addHours(24);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_verified' => false,
            'verification_token' => $verificationToken,
            'verification_token_expires_at' => $verificationExpiry,
        ]);

        // Generate verification URL
        $verificationUrl = 'http://192.168.8.58:8000/api/verify?token=' . $verificationToken;

        // Send verification email using Laravel Mail
        try {
            \Mail::send([], [], function ($message) use ($user, $verificationUrl) {
                $message->to($user->email, $user->name)
                    ->subject('Verify Your Email Address - Real Estate Platform')
                    ->html($this->getVerificationEmailTemplate($user->name, $verificationUrl));
            });
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::error('Verification email failed: ' . $e->getMessage());
            $emailSent = false;
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email to verify your account.',
            'data' => [
                'user' => $user,
                'email_sent' => $emailSent,
                'verification_url' => $verificationUrl, // For testing
            ]
        ], 201);
    }

    private function getVerificationEmailTemplate($name, $verificationUrl)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .email-container { background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background-color: #2d3748; color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 40px 30px; }
        .button { display: inline-block; padding: 14px 40px; background-color: #3182ce; color: white !important; text-decoration: none; border-radius: 6px; margin: 25px 0; }
        .footer { background-color: #f7fafc; padding: 20px; text-align: center; color: #718096; font-size: 12px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏠 Real Estate Platform</h1>
        </div>
        <div class="content">
            <h2>Hello {$name}!</h2>
            <p>Thank you for registering. Please verify your email address by clicking the button below:</p>
            <p style="text-align: center;">
                <a href="{$verificationUrl}" class="button">Verify Email Address</a>
            </p>
            <p>Or copy this link: {$verificationUrl}</p>
            <p>This link expires in 24 hours.</p>
        </div>
        <div class="footer">
            <p>&copy; 2025 Real Estate Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public function login(Request $request)
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
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function verify(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Verification token is required'
            ], 400);
        }

        $user = User::where('verification_token', $token)->first();

        // If token not found, check if user was already verified with this token
        if (!$user) {
            // Check if any user was recently verified (token cleared after verification)
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token'
            ], 404);
        }

        if ($user->is_verified) {
            return $this->getVerificationSuccessPage('Email Already Verified', 'Your email has already been verified. You can now login to your account.');
        }

        if ($user->verification_token_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification token has expired'
            ], 400);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        // Get the verification URL for display
        $verificationUrl = 'http://192.168.8.58:8000/api/verify?token=' . $token;
        return $this->getVerificationSuccessPage('Email Verified Successfully!', 'Your email has been verified successfully. You can now login to your account.', $verificationUrl);
    }

    private function getVerificationSuccessPage($title, $message, $verificationUrl = null)
    {
        $urlSection = $verificationUrl ? "<p style='margin-top: 30px;'><strong>Verification URL:</strong><br><code style='background: #f7fafc; padding: 10px; display: block; margin-top: 10px; word-break: break-all;'>{$verificationUrl}</code></p>" : '';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            background: #48bb78;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon svg {
            width: 50px;
            height: 50px;
            fill: white;
        }
        h1 {
            color: #2d3748;
            margin: 0 0 15px 0;
            font-size: 28px;
        }
        p {
            color: #718096;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
        </div>
        <h1>{$title}</h1>
        <p>{$message}</p>
        {$urlSection}
    </div>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }
}
