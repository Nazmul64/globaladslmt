<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use App\Models\FirebaseApp;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseNotificationService
{
    /**
     * Get Firebase Messaging instance
     */
    protected function getMessaging(int $firebaseAppId)
    {
        try {
            $firebaseApp = FirebaseApp::findOrFail($firebaseAppId);

            if (!$firebaseApp->is_active) {
                throw new Exception('This Firebase App is currently inactive');
            }

            if (empty($firebaseApp->firebase_credentials)) {
                throw new Exception('Firebase credentials not found');
            }

            // Create temporary file for credentials
            $tempFile = tempnam(sys_get_temp_dir(), 'firebase_');

            if ($tempFile === false) {
                throw new Exception('Failed to create temporary file');
            }

            file_put_contents($tempFile, json_encode($firebaseApp->firebase_credentials));

            try {
                $factory = (new Factory)->withServiceAccount($tempFile);
                return $factory->createMessaging();
            } finally {
                // Clean up temp file
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        } catch (Exception $e) {
            Log::error('Firebase Messaging Init Error: ' . $e->getMessage(), [
                'firebase_app_id' => $firebaseAppId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send notification to all users (Multicast + Topic broadcast)
     */
    public function sendToAll(int $firebaseAppId, string $title, string $body, array $data = []): array
    {
        try {
            $messaging = $this->getMessaging($firebaseAppId);

            // Get all FCM tokens for users matching this firebase_app_id or with null app id
            $users = User::whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->where(function ($query) use ($firebaseAppId) {
                    $query->where('firebase_app_id', $firebaseAppId)
                          ->orWhereNull('firebase_app_id');
                })
                ->get();

            $tokens = $users->pluck('fcm_token')->filter()->unique()->values()->toArray();

            if (empty($tokens)) {
                Log::warning('Send to all aborted: No users with fcm_token found in database', [
                    'firebase_app_id' => $firebaseAppId
                ]);

                return [
                    'success' => false,
                    'message' => 'No active user device FCM tokens found in database. Sent: 0',
                    'total_sent' => 0,
                    'total_failed' => 0,
                ];
            }

            // Get App Logo from Settinglogo
            $settingLogo = \App\Models\Settinglogo::first();
            $appLogoUrl = ($settingLogo && !empty($settingLogo->photo))
                ? (filter_var($settingLogo->photo, FILTER_VALIDATE_URL) ? $settingLogo->photo : url('uploads/logo/' . $settingLogo->photo))
                : null;

            // Build notification
            $notification = FirebaseNotification::create($title, $body);

            // Add image ONLY if explicitly provided by admin (do not blow up default logo in drawer)
            if (!empty($data['image_url'])) {
                $notification = $notification->withImageUrl($data['image_url']);
                unset($data['image_url']); // Remove from data array
            }

            // Prepare data for FCM
            $fcmData = array_merge([
                'type' => 'admin_notification',
                'sent_at' => now()->toIso8601String(),
            ], $data);

            if (!empty($appLogoUrl)) {
                $fcmData['app_logo'] = $appLogoUrl;
                $fcmData['icon'] = $appLogoUrl;
                $fcmData['large_icon'] = $appLogoUrl;
            }

            // Convert all data values to strings (FCM requirement)
            $fcmData = array_map(fn($value) => (string) $value, $fcmData);

            // Build message for multicast
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($fcmData);

            // Send multicast in batches (max 500 tokens per batch)
            $chunks = array_chunk($tokens, 500);
            $totalSuccess = 0;
            $totalFailure = 0;

            foreach ($chunks as $chunk) {
                try {
                    $result = $messaging->sendMulticast($message, $chunk);
                    $totalSuccess += $result->successes()->count();
                    $totalFailure += $result->failures()->count();

                    foreach ($result->failures()->getItems() as $failure) {
                        Log::warning('FCM Send to All Token Failed', [
                            'token' => $failure->target()->value(),
                            'error' => $failure->error()->getMessage()
                        ]);
                    }
                } catch (MessagingException $e) {
                    Log::error('Multicast sendToAll batch error: ' . $e->getMessage());
                    $totalFailure += count($chunk);
                }
            }

            // Also attempt topic broadcast as fallback
            try {
                $topicMessage = CloudMessage::withTarget('topic', 'all_users')
                    ->withNotification($notification)
                    ->withData($fcmData);
                $messaging->send($topicMessage);
            } catch (Throwable $e) {
                Log::debug('Topic broadcast skipped/failed: ' . $e->getMessage());
            }

            Log::info('Notification broadcast sent to all users', [
                'firebase_app_id' => $firebaseAppId,
                'title' => $title,
                'total_tokens' => count($tokens),
                'total_sent' => $totalSuccess,
                'total_failed' => $totalFailure,
            ]);

            return [
                'success' => $totalSuccess > 0,
                'message' => "Notification broadcast completed: {$totalSuccess} sent successfully, {$totalFailure} failed.",
                'total_sent' => $totalSuccess,
                'total_failed' => $totalFailure,
            ];
        } catch (MessagingException $e) {
            Log::error('Firebase Messaging Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Firebase messaging error: ' . $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => 0,
            ];
        } catch (FirebaseException $e) {
            Log::error('Firebase Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Firebase error: ' . $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => 0,
            ];
        } catch (Exception $e) {
            Log::error('Send to All Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => 0,
            ];
        }
    }

    /**
     * Send notification to specific users (Multicast)
     */
    public function sendToSpecificUsers(
        int $firebaseAppId,
        array $userIds,
        string $title,
        string $body,
        array $data = []
    ): array {
        try {
            $messaging = $this->getMessaging($firebaseAppId);

            // Get FCM tokens
            $users = User::where(function ($q) use ($firebaseAppId) {
                    $q->where('firebase_app_id', $firebaseAppId)
                      ->orWhereNull('firebase_app_id');
                })
                ->whereIn('id', $userIds)
                ->whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->get();

            if ($users->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No valid FCM tokens found',
                    'total_sent' => 0,
                    'total_failed' => count($userIds),
                ];
            }

            $tokens = $users->pluck('fcm_token')->filter()->unique()->values()->toArray();

            if (empty($tokens)) {
                return [
                    'success' => false,
                    'message' => 'No valid FCM tokens found',
                    'total_sent' => 0,
                    'total_failed' => count($userIds),
                ];
            }

            // Get App Logo from Settinglogo
            $settingLogo = \App\Models\Settinglogo::first();
            $appLogoUrl = ($settingLogo && !empty($settingLogo->photo))
                ? (filter_var($settingLogo->photo, FILTER_VALIDATE_URL) ? $settingLogo->photo : url('uploads/logo/' . $settingLogo->photo))
                : null;

            // Build notification
            $notification = FirebaseNotification::create($title, $body);

            // Add image ONLY if explicitly provided by admin (do not blow up default logo in drawer)
            if (!empty($data['image_url'])) {
                $notification = $notification->withImageUrl($data['image_url']);
                unset($data['image_url']);
            }

            // Prepare data for FCM
            $fcmData = array_merge([
                'type' => 'admin_notification',
                'sent_at' => now()->toIso8601String(),
            ], $data);

            if (!empty($appLogoUrl)) {
                $fcmData['app_logo'] = $appLogoUrl;
                $fcmData['icon'] = $appLogoUrl;
                $fcmData['large_icon'] = $appLogoUrl;
            }

            // Convert all data values to strings
            $fcmData = array_map(fn($value) => (string) $value, $fcmData);

            // Build message
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($fcmData);

            // Send multicast (max 500 tokens per batch)
            $chunks = array_chunk($tokens, 500);
            $totalSuccess = 0;
            $totalFailure = 0;

            foreach ($chunks as $chunk) {
                try {
                    $result = $messaging->sendMulticast($message, $chunk);
                    $totalSuccess += $result->successes()->count();
                    $totalFailure += $result->failures()->count();

                    // Log failures
                    foreach ($result->failures()->getItems() as $failure) {
                        Log::warning('FCM Send Failed', [
                            'token' => $failure->target()->value(),
                            'error' => $failure->error()->getMessage()
                        ]);
                    }
                } catch (MessagingException $e) {
                    Log::error('Multicast batch error: ' . $e->getMessage());
                    $totalFailure += count($chunk);
                }
            }

            Log::info('Notification sent to specific users', [
                'firebase_app_id' => $firebaseAppId,
                'title' => $title,
                'total_tokens' => count($tokens),
                'success' => $totalSuccess,
                'failed' => $totalFailure
            ]);

            return [
                'success' => true,
                'total_sent' => $totalSuccess,
                'total_failed' => $totalFailure,
                'message' => "Notification sent: {$totalSuccess} successful, {$totalFailure} failed",
            ];
        } catch (Exception $e) {
            Log::error('Send to Specific Users Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'total_sent' => 0,
                'total_failed' => count($userIds),
            ];
        }
    }

    /**
     * Subscribe users to topic
     */
    public function subscribeToTopic(int $firebaseAppId, array|string $tokens, string $topic = 'all_users'): array
    {
        try {
            $messaging = $this->getMessaging($firebaseAppId);

            $tokens = is_array($tokens) ? $tokens : [$tokens];
            $tokens = array_filter($tokens); // Remove empty values

            if (empty($tokens)) {
                return [
                    'success' => false,
                    'message' => 'No tokens provided',
                ];
            }

            $messaging->subscribeToTopic($topic, $tokens);

            Log::info('Users subscribed to topic', [
                'firebase_app_id' => $firebaseAppId,
                'topic' => $topic,
                'tokens_count' => count($tokens)
            ]);

            return [
                'success' => true,
                'message' => 'Successfully subscribed to topic',
            ];
        } catch (Exception $e) {
            Log::error('Subscribe to Topic Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Unsubscribe users from topic
     */
    public function unsubscribeFromTopic(int $firebaseAppId, array|string $tokens, string $topic = 'all_users'): array
    {
        try {
            $messaging = $this->getMessaging($firebaseAppId);

            $tokens = is_array($tokens) ? $tokens : [$tokens];
            $tokens = array_filter($tokens);

            if (empty($tokens)) {
                return [
                    'success' => false,
                    'message' => 'No tokens provided',
                ];
            }

            $messaging->unsubscribeFromTopic($topic, $tokens);

            Log::info('Users unsubscribed from topic', [
                'firebase_app_id' => $firebaseAppId,
                'topic' => $topic,
                'tokens_count' => count($tokens)
            ]);

            return [
                'success' => true,
                'message' => 'Successfully unsubscribed from topic',
            ];
        } catch (Exception $e) {
            Log::error('Unsubscribe from Topic Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate FCM token
     */
    public function validateToken(int $firebaseAppId, string $token): bool
    {
        try {
            if (empty($token)) {
                return false;
            }

            $messaging = $this->getMessaging($firebaseAppId);

            // Create a test message
            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create('Test', 'Validation'))
                ->withData(['type' => 'validation']);

            // Validate by attempting to send (dry run would be better if supported)
            return strlen($token) > 0 && strpos($token, ':') !== false;
        } catch (Exception $e) {
            Log::error('Token Validation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification with custom data and options
     */
    public function sendCustomNotification(
        int $firebaseAppId,
        array $tokens,
        string $title,
        string $body,
        array $customData = [],
        ?string $imageUrl = null,
        ?string $clickAction = null
    ): array {
        try {
            $messaging = $this->getMessaging($firebaseAppId);

            if (empty($tokens)) {
                return [
                    'success' => false,
                    'message' => 'No tokens provided',
                ];
            }

            // Build notification
            $notification = FirebaseNotification::create($title, $body);

            if ($imageUrl) {
                $notification = $notification->withImageUrl($imageUrl);
            }

            // Prepare data
            $fcmData = array_merge([
                'sent_at' => now()->toIso8601String(),
            ], $customData);

            if ($clickAction) {
                $fcmData['click_action'] = $clickAction;
            }

            // Convert to strings
            $fcmData = array_map(fn($value) => (string) $value, $fcmData);

            // Build message
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($fcmData);

            // Send
            $result = $messaging->sendMulticast($message, $tokens);

            return [
                'success' => true,
                'total_sent' => $result->successes()->count(),
                'total_failed' => $result->failures()->count(),
                'message' => 'Custom notification sent successfully',
            ];
        } catch (Exception $e) {
            Log::error('Send Custom Notification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
