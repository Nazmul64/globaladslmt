<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoadminchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * ====================================================================
 * ✅ ADMIN CHAT CONTROLLER - COMPLETE REWRITE
 * ✅ Fixed: Image upload to uploads/adminchat (NO storage link)
 * ✅ Features: Real-time chat, unread counts, image upload
 * ====================================================================
 */
class AdminandchatuserController extends Controller
{
    /**
     * ====================================================================
     * ✅ SHOW CHAT PAGE WITH USER LIST
     * Display all users with role='user'
     * ====================================================================
     */
    public function adminuserchat()
    {
        $adminId = Auth::id();

        $users = User::where('role', 'user')
            ->select('id', 'name', 'email', 'photo')
            ->get()
            ->map(function ($user) use ($adminId) {
                // Get latest message between this user and admin
                $latestMsg = Usertoadminchat::where(function ($q) use ($user, $adminId) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $adminId);
                    })
                    ->orWhere(function ($q) use ($user, $adminId) {
                        $q->where('sender_id', $adminId)->where('receiver_id', $user->id);
                    })
                    ->latest()
                    ->first();

                // Get unread count from this user to admin
                $unreadCount = Usertoadminchat::where('sender_id', $user->id)
                    ->where('receiver_id', $adminId)
                    ->where('is_read', false)
                    ->count();

                $user->latest_message_time = $latestMsg ? $latestMsg->created_at->timestamp : 0;
                $user->latest_message_id = $latestMsg ? $latestMsg->id : 0;
                $user->last_message_text = $latestMsg ? ($latestMsg->message ? Str::limit($latestMsg->message, 30) : ($latestMsg->image ? '📷 Image' : '')) : '';
                $user->unread_count = $unreadCount;
                return $user;
            })
            ->sortByDesc('latest_message_time')
            ->values();

        return view('admin.userchat.index', compact('users'));
    }

    /**
     * ====================================================================
     * ✅ FETCH MESSAGES BETWEEN ADMIN AND USER
     * Load messages after last_id for real-time updates
     * ====================================================================
     */
    public function fetchMessages($user_id, Request $request)
    {
        $adminId = Auth::id();
        $lastId = (int) $request->query('last_id', 0);

        // ✅ Fetch messages between admin and user
        $messages = Usertoadminchat::where(function ($q) use ($user_id, $adminId, $lastId) {
                $q->where('sender_id', $user_id)
                  ->where('receiver_id', $adminId)
                  ->where('id', '>', $lastId);
            })
            ->orWhere(function ($q) use ($user_id, $adminId, $lastId) {
                $q->where('sender_id', $adminId)
                  ->where('receiver_id', $user_id)
                  ->where('id', '>', $lastId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        // ✅ Mark user messages as read
        Usertoadminchat::where('sender_id', $user_id)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // ✅ Transform messages with proper image paths
        $messages = $messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'message' => $msg->message,
                'image' => $msg->image, // Will be like: uploads/adminchat/filename.jpg
                'message_type' => $msg->message_type ?? 'text',
                'is_read' => (bool) $msg->is_read,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json($messages);
    }

    /**
     * ====================================================================
     * ✅ SEND MESSAGE (TEXT OR IMAGE)
     * ✅ CRITICAL FIX: Upload to uploads/adminchat (NO storage link)
     * ====================================================================
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
        ]);

        $adminId = Auth::id();
        $imagePath = null;
        $messageType = 'text';

        /**
         * ====================================================================
         * ✅ CRITICAL: IMAGE UPLOAD TO uploads/adminchat (NO STORAGE)
         * Store directly in public/uploads/adminchat folder
         * ====================================================================
         */
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // ✅ Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

            // ✅ Define upload path (public/uploads/adminchat)
            $uploadPath = public_path('uploads/adminchat');

            // ✅ Create folder if not exists
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // ✅ Move file to public folder (NO storage link needed)
            $image->move($uploadPath, $fileName);

            // ✅ Store relative path in database
            $imagePath = 'uploads/adminchat/' . $fileName;
            $messageType = 'image';

            \Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            \Log::info('✅ ADMIN IMAGE UPLOADED');
            \Log::info('File name: ' . $fileName);
            \Log::info('Upload path: ' . $uploadPath);
            \Log::info('Stored in DB: ' . $imagePath);
            \Log::info('Full path: ' . $uploadPath . '/' . $fileName);
            \Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        }

        /**
         * ====================================================================
         * ✅ CREATE MESSAGE RECORD
         * ====================================================================
         */
        $chat = Usertoadminchat::create([
            'sender_id' => $adminId,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
            'image' => $imagePath,
            'message_type' => $messageType,
            'is_read' => false,
        ]);

        /**
         * ====================================================================
         * ✅ RETURN RESPONSE WITH IMAGE URL
         * ====================================================================
         */
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'chat' => [
                'id' => $chat->id,
                'sender_id' => $chat->sender_id,
                'receiver_id' => $chat->receiver_id,
                'message' => $chat->message,
                'image' => $chat->image, // uploads/adminchat/filename.jpg
                'message_type' => $chat->message_type,
                'is_read' => false,
                'created_at' => $chat->created_at->toIso8601String(),
            ],
            'time' => $chat->created_at->format('h:i A'),
        ]);
    }

    /**
     * ====================================================================
     * ✅ GET UNREAD MESSAGE COUNT PER USER
     * Returns: { user_id: count, user_id: count, ... }
     * ====================================================================
     */
    public function unreadCount()
    {
        $adminId = Auth::id();

        $unreadCounts = Usertoadminchat::where('receiver_id', $adminId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->selectRaw('sender_id, COUNT(*) as count')
            ->pluck('count', 'sender_id');

        // Fetch latest message timestamp and preview for each user conversation
        $latestMessages = Usertoadminchat::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->latest()
            ->get()
            ->groupBy(function($msg) use ($adminId) {
                return $msg->sender_id == $adminId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(function($group) {
                $latest = $group->first();
                return [
                    'last_time' => $latest->created_at->timestamp,
                    'last_message' => $latest->message ? Str::limit($latest->message, 30) : ($latest->image ? '📷 Image' : ''),
                    'last_id' => $latest->id,
                ];
            });

        return response()->json([
            'unread' => $unreadCounts,
            'latest' => $latestMessages,
        ]);
    }

    /**
     * ====================================================================
     * ✅ MARK MESSAGES AS READ
     * Mark all messages from specific user as read
     * ====================================================================
     */
    public function markRead($user_id)
    {
        $adminId = Auth::id();

        $updated = Usertoadminchat::where('sender_id', $user_id)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        \Log::info("✅ Marked {$updated} messages as read from user ID: {$user_id}");

        return response()->json([
            'success' => true,
            'marked_count' => $updated,
        ]);
    }

    /**
     * ====================================================================
     * ✅ DELETE CHAT HISTORY (OPTIONAL)
     * Delete all messages between admin and specific user
     * ====================================================================
     */
    public function deleteChatHistory($user_id)
    {
        $adminId = Auth::id();

        // Delete images first
        $messages = Usertoadminchat::where(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $user_id)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $user_id);
            })
            ->whereNotNull('image')
            ->get();

        // Delete image files from disk
        foreach ($messages as $msg) {
            if ($msg->image) {
                $filePath = public_path($msg->image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    \Log::info("🗑️ Deleted image: {$filePath}");
                }
            }
        }

        // Delete database records
        $deleted = Usertoadminchat::where(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $user_id)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $user_id);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
            'message' => "Chat history cleared successfully",
        ]);
    }

    /**
     * ====================================================================
     * ✅ GET CHAT STATISTICS (OPTIONAL)
     * Get total messages, unread count, etc.
     * ====================================================================
     */
    public function getChatStats()
    {
        $adminId = Auth::id();

        $totalMessages = Usertoadminchat::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->count();

        $unreadMessages = Usertoadminchat::where('receiver_id', $adminId)
            ->where('is_read', false)
            ->count();

        $totalUsers = Usertoadminchat::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->distinct('sender_id')
            ->count('sender_id');

        $recentChats = Usertoadminchat::where('receiver_id', $adminId)
            ->orWhere('sender_id', $adminId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->with('sender:id,name,email,photo')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_messages' => $totalMessages,
                'unread_messages' => $unreadMessages,
                'total_users' => $totalUsers,
            ],
            'recent_chats' => $recentChats,
        ]);
    }

    /**
     * ====================================================================
     * ✅ SEARCH CHAT MESSAGES (OPTIONAL)
     * Search messages by keyword
     * ====================================================================
     */
    public function searchMessages(Request $request)
    {
        $adminId = Auth::id();
        $keyword = $request->input('keyword', '');

        if (empty($keyword)) {
            return response()->json([
                'success' => false,
                'message' => 'Keyword is required',
            ], 400);
        }

        $messages = Usertoadminchat::where(function ($q) use ($adminId) {
                $q->where('sender_id', $adminId)
                  ->orWhere('receiver_id', $adminId);
            })
            ->where('message', 'LIKE', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'results' => $messages,
        ]);
    }

    /**
     * ====================================================================
     * ✅ EXPORT CHAT HISTORY (OPTIONAL)
     * Export chat as JSON or CSV
     * ====================================================================
     */
    public function exportChatHistory($user_id, Request $request)
    {
        $adminId = Auth::id();
        $format = $request->input('format', 'json'); // json or csv

        $messages = Usertoadminchat::where(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $user_id)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($user_id, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $user_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        if ($format === 'csv') {
            $filename = "chat_history_user_{$user_id}_" . date('Y-m-d') . ".csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ];

            $callback = function() use ($messages) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Sender', 'Message', 'Image', 'Type', 'Read', 'Date']);

                foreach ($messages as $msg) {
                    fputcsv($file, [
                        $msg->id,
                        $msg->sender_id == Auth::id() ? 'Admin' : 'User',
                        $msg->message,
                        $msg->image ? asset($msg->image) : '',
                        $msg->message_type,
                        $msg->is_read ? 'Yes' : 'No',
                        $msg->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // JSON format
        return response()->json([
            'success' => true,
            'user_id' => $user_id,
            'message_count' => $messages->count(),
            'messages' => $messages,
        ]);
    }
}
