<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user profile by email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserByEmail(Request $request): JsonResponse
    {
        try {
            $email = $request->get('email');

            if (!$email) {
                return response()->json([
                    'error' => 'Email parameter is required'
                ], 400);
            }

            $user = User::where('email', $email)
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'is_verified',
                    'verification_token',
                    'verification_token_expires_at',
                    'created_at'
                ])
                ->first();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            $verificationUrl = null;
            if ($user->verification_token) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'] ?? '192.168.1.33';
                $verificationUrl = $protocol . "://" . $host . "/api/auth?action=verify&token=" . $user->verification_token;
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_verified' => (bool)$user->is_verified,
                'has_token' => !empty($user->verification_token),
                'token' => $user->verification_token,
                'token_expires_at' => $user->verification_token_expires_at,
                'created_at' => $user->created_at,
                'verification_url' => $verificationUrl
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            // In production, get user_id from authenticated user (JWT token)
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 400);
            }

            $user = User::select([
                'id',
                'name',
                'email',
                'phone',
                'role',
                'is_verified',
                'created_at'
            ])->find($userId);

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            return response()->json($user, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            // In production, get user_id from authenticated user (JWT token)
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            $updates = [];

            if ($request->has('name')) {
                $updates['name'] = $request->name;
            }

            if ($request->has('phone')) {
                $updates['phone'] = $request->phone;
            }

            if ($request->has('password')) {
                $updates['password'] = Hash::make($request->password);
            }

            if (empty($updates)) {
                return response()->json([
                    'error' => 'No fields to update'
                ], 400);
            }

            $user->update($updates);

            return response()->json([
                'message' => 'Profile updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get salespeople by compound name
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSalespeopleByCompound(Request $request): JsonResponse
    {
        try {
            $compoundName = $request->get('compound_name');

            if (!$compoundName) {
                return response()->json([
                    'success' => false,
                    'error' => 'Compound name is required',
                    'message' => 'Please provide compound_name parameter'
                ], 400);
            }

            // Find the compound by name (search in both project and project_en fields)
            $compound = \App\Models\Compound::where('project', 'LIKE', "%{$compoundName}%")
                ->orWhere('project_en', 'LIKE', "%{$compoundName}%")
                ->orWhere('project_ar', 'LIKE', "%{$compoundName}%")
                ->first();

            if (!$compound) {
                return response()->json([
                    'success' => false,
                    'error' => 'Compound not found',
                    'message' => "No compound found with name: {$compoundName}"
                ], 404);
            }

            // Get all salespeople for this company
            $salespeople = User::where('role', 'sales')
                ->where('company_id', $compound->company_id)
                ->select([
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                    'company_id',
                    'is_verified',
                    'created_at'
                ])
                ->get();

            if ($salespeople->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No salespeople found',
                    'message' => 'No salespeople assigned to this compound\'s company'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'compound' => [
                    'id' => $compound->id,
                    'name' => $compound->project,
                    'company_id' => $compound->company_id
                ],
                'salespeople' => $salespeople
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            // In production, get user from authenticated token
            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'error' => 'User ID is required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',      // At least one uppercase
                    'regex:/[a-z]/',      // At least one lowercase
                    'regex:/[0-9]/',      // At least one number
                    'regex:/[@$!%*?&#]/'  // At least one special character
                ]
            ], [
                'new_password.required' => 'New password is required',
                'new_password.min' => 'Password must be at least 8 characters long',
                'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'error' => 'User not found'
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
                'reset_token' => null,
                'reset_token_expires_at' => null
            ]);

            return response()->json([
                'message' => 'Password has been successfully changed',
                'success' => true
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to change password',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
