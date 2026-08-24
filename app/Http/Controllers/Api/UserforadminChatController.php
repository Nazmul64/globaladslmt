<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserforadminChatController extends Controller
{
    /**
     * Get Admin User ID dynamically
     */
    protected function getAdminId()
    {
        $admin = User::where('role', 'is_admin')
            ->orWhere('role', 'admin')
            ->orWhere('email', 'admin@gmail.com')
            ->orWhere('name', 'like', '%Admin%')
            ->first();

        return $admin ? $admin->id : 3;
    }

    /**
     * Send Message (Text / Image)
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message'      => 'nullable|string|max:1000',
                'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'message_type' => 'nullable|in:text,image',
            ]);

            $userId  = Auth::id();
            $adminId = $this->getAdminId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $imagePath   = null;
            $messageType = $request->input('message_type', 'text');

            /* ===============================
               IMAGE UPLOAD (NO STORAGE)
            ===============================*/
            if ($request->hasFile('image')) {
                $image      = $request->file('image');
                $fileName   = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $uploadPath = public_path('uploads/adminchat');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $fileName);

                $imagePath   = 'uploads/adminchat/' . $fileName;
                $messageType = 'image';

                Log::info('Image uploaded', [
                    'fileName' => $fileName,
                    'imagePath' => $imagePath,
                    'fullPath' => $uploadPath . '/' . $fileName,
                ]);
            }

            /* ===============================
               SAVE MESSAGE
            ===============================*/
            $chat = Usertoadminchat::create([
                'sender_id'    => $userId,
                'receiver_id'  => $adminId,
                'message'      => $request->message ?? '',
                'image'        => $imagePath,
                'message_type' => $messageType,
                'is_read'      => false,
            ]);

            $imageUrl = null;
            if ($imagePath) {
                $imageUrl = asset($imagePath);
                Log::info('Returning image URL', [
                    'imagePath' => $imagePath,
                    'imageUrl' => $imageUrl,
                ]);
            }

            $createdAtStr = $chat->created_at ? $chat->created_at->toIso8601String() : now()->toIso8601String();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data'    => [
                    'id'           => $chat->id,
                    'sender_id'    => $chat->sender_id,
                    'sender_type'  => 'user',
                    'receiver_id'  => $chat->receiver_id,
                    'message'      => $chat->message,
                    'message_type' => $chat->message_type,
                    'image_url'    => $imageUrl,
                    'is_read'      => (bool) $chat->is_read,
                    'created_at'   => $createdAtStr,
                ]
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error in sendMessage: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch Chat Messages (Pagination)
     */
    public function fetchMessages(Request $request)
    {
        try {
            $userId  = Auth::id();
            $adminId = $this->getAdminId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $perPage = (int) $request->input('per_page', 20);
            $page    = (int) $request->input('page', 1);

            $messages = Usertoadminchat::where(function ($q) use ($userId, $adminId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $adminId);
                })
                ->orWhere(function ($q) use ($userId, $adminId) {
                    $q->where('sender_id', $adminId)
                      ->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = $messages->getCollection()
                ->reverse()
                ->values()
                ->map(function ($msg) use ($adminId) {
                    $imageUrl = null;
                    if ($msg->image) {
                        $cleanImage = ltrim($msg->image, '/');
                        if (str_starts_with($cleanImage, 'http://') || str_starts_with($cleanImage, 'https://')) {
                            $imageUrl = $cleanImage;
                        } else {
                            $imageUrl = asset($cleanImage);
                        }
                    }

                    $createdAtStr = $msg->created_at ? $msg->created_at->toIso8601String() : now()->toIso8601String();

                    return [
                        'id'           => $msg->id,
                        'sender_id'    => $msg->sender_id,
                        'sender_type'  => $msg->sender_id == $adminId ? 'admin' : 'user',
                        'receiver_id'  => $msg->receiver_id,
                        'message'      => $msg->message ?? '',
                        'message_type' => $msg->message_type ?? ($imageUrl ? 'image' : 'text'),
                        'image_url'    => $imageUrl,
                        'is_read'      => (bool) $msg->is_read,
                        'created_at'   => $createdAtStr,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Messages loaded successfully',
                'messages' => $data,
                'data'    => [
                    'messages'   => $data,
                    'pagination' => [
                        'current_page' => $messages->currentPage(),
                        'last_page'    => $messages->lastPage(),
                        'per_page'     => $messages->perPage(),
                        'total'        => $messages->total(),
                    ],
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error in fetchMessages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load messages',
                'data'    => [
                    'messages'   => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page'    => 1,
                        'per_page'     => 20,
                        'total'        => 0,
                    ],
                ],
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mark Admin Messages as Read
     */
    public function markAsRead()
    {
        try {
            $userId  = Auth::id();
            $adminId = $this->getAdminId();

            if ($userId) {
                Usertoadminchat::where('sender_id', $adminId)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking messages as read',
            ], 200);
        }
    }
}