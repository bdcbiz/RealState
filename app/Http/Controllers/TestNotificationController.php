<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FCMNotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TestNotificationController extends Controller
{
    /**
     * Test sending a notification to all users
     */
    public function test()
    {
        try {
            $fcm = new FCMNotificationService();

            // Test sending notification
            $fcm->sendToAllUsers(
                'Test Notification',
                'If you receive this, FCM is working perfectly!',
                [
                    'test' => 'true',
                    'timestamp' => now()->toIso8601String(),
                    'message' => 'This is a test notification from your Laravel API'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully',
                'users_with_tokens' => User::whereNotNull('fcm_token')->count(),
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Check FCM system status
     */
    public function status()
    {
        $credPath = storage_path(config('firebase.credentials.file'));

        return response()->json([
            'fcm_enabled' => true,
            'credentials_file' => config('firebase.credentials.file'),
            'credentials_path' => $credPath,
            'credentials_exists' => file_exists($credPath),
            'total_users' => User::count(),
            'users_with_fcm_tokens' => User::whereNotNull('fcm_token')->count(),
            'buyers_with_tokens' => User::where('role', 'buyer')->whereNotNull('fcm_token')->count(),
            'sellers_with_tokens' => User::where('role', 'seller')->whereNotNull('fcm_token')->count(),
            'agents_with_tokens' => User::where('role', 'agent')->whereNotNull('fcm_token')->count(),
            'server_time' => now()->toIso8601String()
        ]);
    }

    /**
     * Test creating a unit (triggers automatic notification)
     */
    public function testUnitNotification()
    {
        try {
            // Get first available compound
            $compound = \App\Models\Compound::first();

            if (!$compound) {
                return response()->json([
                    'success' => false,
                    'error' => 'No compounds found in database. Please create a compound first.'
                ], 404);
            }

            $unit = new \App\Models\Unit();
            $unit->compound_id = $compound->id;
            $unit->unit_code = 'TEST-' . rand(1000, 9999);
            $unit->unit_name = 'Test Apartment ' . rand(100, 999);
            $unit->normal_price = rand(1000000, 5000000);
            $unit->is_sold = 0;
            $unit->save();

            return response()->json([
                'success' => true,
                'message' => 'Test unit created - notification should be sent automatically',
                'unit' => $unit,
                'compound' => [
                    'id' => $compound->id,
                    'name' => $compound->compound_name ?? 'N/A'
                ],
                'note' => 'Check your device or logs to verify notification was sent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test creating a sale (triggers automatic notification)
     */
    public function testSaleNotification()
    {
        try {
            // Get first available company
            $company = \App\Models\Company::first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'error' => 'No companies found in database. Please create a company first.'
                ], 404);
            }

            // Get first available compound
            $compound = \App\Models\Compound::first();

            if (!$compound) {
                return response()->json([
                    'success' => false,
                    'error' => 'No compounds found in database. Please create a compound first.'
                ], 404);
            }

            $sale = new \App\Models\Sale();
            $sale->company_id = $company->id;
            $sale->sale_type = 'compound';
            $sale->compound_id = $compound->id;
            $sale->sale_name = 'Test Sale ' . rand(100, 999);
            $sale->description = 'This is a test sale created for notification testing';
            $sale->discount_percentage = rand(10, 50);
            $sale->old_price = 2000000;
            $sale->new_price = 1500000;
            $sale->start_date = now();
            $sale->end_date = now()->addDays(30);
            $sale->is_active = 1;
            $sale->save();

            return response()->json([
                'success' => true,
                'message' => 'Test sale created - notification should be sent automatically',
                'sale' => $sale,
                'company' => [
                    'id' => $company->id,
                    'name' => $company->company_name ?? 'N/A'
                ],
                'compound' => [
                    'id' => $compound->id,
                    'name' => $compound->compound_name ?? 'N/A'
                ],
                'note' => 'Check your device or logs to verify notification was sent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a test FCM token to a user
     */
    public function addTestToken(Request $request)
    {
        try {
            $token = $request->input('token', 'test_dummy_token_' . time());

            // Get joh@example.com user instead of first user
            $user = User::where('email', 'joh@example.com')->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User joh@example.com not found in database.'
                ], 404);
            }

            $user->fcm_token = $token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Test FCM token added to user',
                'user_id' => $user->id,
                'user_email' => $user->email,
                'fcm_token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
