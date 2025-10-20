<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FCMNotificationService;

class NotificationController extends Controller
{
    protected $fcmService;

    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            $this->fcmService = null;
        }
    }

    /**
     * Send custom notification to all users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToAll(Request $request)
    {
        if (!$this->fcmService) {
            return response()->json([
                'success' => false,
                'message' => 'FCM service not available'
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->fcmService->sendToAllUsers(
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to all users'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send custom notification to users by role
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToRole(Request $request)
    {
        if (!$this->fcmService) {
            return response()->json([
                'success' => false,
                'message' => 'FCM service not available'
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:buyer,seller,agent',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->fcmService->sendToUsersByRole(
                $request->role,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return response()->json([
                'success' => true,
                'message' => "Notification sent to all {$request->role} users"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send custom notification to topic
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToTopic(Request $request)
    {
        if (!$this->fcmService) {
            return response()->json([
                'success' => false,
                'message' => 'FCM service not available'
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->fcmService->sendToTopic(
                $request->topic,
                $request->title,
                $request->body,
                $request->data ?? []
            );

            return response()->json([
                'success' => true,
                'message' => "Notification sent to topic: {$request->topic}"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
