<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class FCMNotificationService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = base_path(config('firebase.credentials.file'));

            if (!file_exists($credentialsPath)) {
                Log::error('Firebase credentials file not found at: ' . $credentialsPath);
                throw new \Exception('Firebase credentials file not found');
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize FCM: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send notification to all users
     *
     * @param string $title
     * @param string $body
     * @param array $data Additional data to send
     * @return void
     */
    public function sendToAllUsers($title, $body, $data = [])
    {
        try {
            // Get all users with FCM tokens
            $users = User::whereNotNull('fcm_token')->get();

            if ($users->isEmpty()) {
                Log::info('No users with FCM tokens found');
                return;
            }

            foreach ($users as $user) {
                $this->sendToUser($user->fcm_token, $title, $body, $data);
            }

            Log::info("Notification sent to {$users->count()} users: {$title}");
        } catch (\Exception $e) {
            Log::error('Failed to send notification to all users: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific user by token
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToUser($token, $title, $body, $data = [])
    {
        try {
            Log::info("═══════════════════════════════════════");
            Log::info("📤 FCM: Starting notification send");
            Log::info("   Token: " . substr($token, 0, 50) . "...");
            Log::info("   Title: {$title}");
            Log::info("   Body: {$body}");
            Log::info("   Data: " . json_encode($data));

            $messageArray = [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ],
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1,
                        ],
                    ],
                ],
            ];

            Log::info("📦 FCM: Message payload prepared");
            Log::info("   Payload: " . json_encode($messageArray));

            $message = CloudMessage::fromArray($messageArray);

            Log::info("🚀 FCM: Sending to Firebase Cloud Messaging...");
            $response = $this->messaging->send($message);

            Log::info("✅ FCM: Notification sent successfully!");
            Log::info("   Response: " . json_encode($response));
            Log::info("═══════════════════════════════════════");

            return true;
        } catch (\Exception $e) {
            Log::error("═══════════════════════════════════════");
            Log::error("❌ FCM: Failed to send notification");
            Log::error("   Token: " . substr($token, 0, 50) . "...");
            Log::error("   Error Type: " . get_class($e));
            Log::error("   Error Message: " . $e->getMessage());
            Log::error("   Error Code: " . $e->getCode());
            Log::error("   Stack Trace: " . $e->getTraceAsString());
            Log::error("═══════════════════════════════════════");
            return false;
        }
    }

    /**
     * Send notification to multiple users by tokens
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendToMultipleUsers(array $tokens, $title, $body, $data = [])
    {
        foreach ($tokens as $token) {
            $this->sendToUser($token, $title, $body, $data);
        }
    }

    /**
     * Send notification to users with specific role
     *
     * @param string $role (buyer, seller, agent)
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendToUsersByRole($role, $title, $body, $data = [])
    {
        try {
            $users = User::where('role', $role)
                ->whereNotNull('fcm_token')
                ->get();

            if ($users->isEmpty()) {
                Log::info("No {$role} users with FCM tokens found");
                return;
            }

            foreach ($users as $user) {
                $this->sendToUser($user->fcm_token, $title, $body, $data);
            }

            Log::info("Notification sent to {$users->count()} {$role} users: {$title}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to {$role} users: " . $e->getMessage());
        }
    }

    /**
     * Send notification using topic (requires users to be subscribed to topic)
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        try {
            $message = CloudMessage::fromArray([
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ],
            ]);

            $this->messaging->send($message);
            Log::info("Notification sent to topic '{$topic}': {$title}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to topic {$topic}: " . $e->getMessage());
        }
    }
}
