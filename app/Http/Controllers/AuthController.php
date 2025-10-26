<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
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
            // Verify the Google ID token
            $client = new \Google_Client(['client_id' => config('services.google.client_id')]);
            $payload = $client->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google ID token',
                ], 401);
            }

            // Extract verified user information from token payload
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'] ?? 'Google User';
            $photoUrl = $payload['picture'] ?? null;
            $emailVerified = $payload['email_verified'] ?? false;

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
}
