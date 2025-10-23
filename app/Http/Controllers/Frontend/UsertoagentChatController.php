<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertoagentchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UsertoagentChatController extends Controller
{
    /**
     * Display chat page with list of agents
     */
public function frontend_user_toagent_chat()
{
    // শুধু যাদের role 'agent' বা 'user' তারা আসবে
    $agents = User::whereIn('role', ['agent'])
                ->select('id', 'name', 'email')
                ->orderBy('name', 'asc')
                ->get();

    return view('frontend.agentchat.index', compact('agents'));
}




    /**
     * Send message (AJAX POST)
     */
    public function frontend_chat_submit(Request $request)
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'message' => 'nullable|string|max:5000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $currentUserId = Auth::id();
            $receiverId = $validated['receiver_id'];

            // Check if receiver is an agent
            $receiver = User::where('id', $receiverId)
                ->where('role', 'agent')
                ->first();

            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid agent selected'
                ], 400);
            }

            // Prevent sending empty messages
            if (empty($validated['message']) && !$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a message or select an image'
                ], 400);
            }

            DB::beginTransaction();

            $chat = new Usertoagentchat();
            $chat->sender_id = $currentUserId;
            $chat->receiver_id = $receiverId;
            $chat->message = $validated['message'] ?? null;
            $chat->is_read = false;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'chat_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $uploadPath = public_path('uploads/chat');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $image->move($uploadPath, $imageName);
                $chat->image = 'uploads/chat/' . $imageName;
            }

            $chat->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $chat->id,
                    'sender_id' => $chat->sender_id,
                    'receiver_id' => $chat->receiver_id,
                    'message' => $chat->message,
                    'image' => $chat->image,
                    'is_read' => $chat->is_read,
                    'created_at' => $chat->created_at->toDateTimeString(),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    /**
     * Load chat messages between user and agent (AJAX GET)
     */
    public function frontend_chat_messages(Request $request)
    {
        try {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id'
            ]);

            $currentUserId = Auth::id();
            $receiverId = $validated['receiver_id'];

            // Verify receiver is an agent
            $receiver = User::where('id', $receiverId)
                ->where('role', 'agent')
                ->first();

            if (!$receiver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid agent selected'
                ], 400);
            }

            // Get all messages between current user and selected agent
            $messages = Usertoagentchat::where(function ($query) use ($currentUserId, $receiverId) {
                    $query->where('sender_id', $currentUserId)
                          ->where('receiver_id', $receiverId);
                })
                ->orWhere(function ($query) use ($currentUserId, $receiverId) {
                    $query->where('sender_id', $receiverId)
                          ->where('receiver_id', $currentUserId);
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'receiver_id' => $message->receiver_id,
                        'message' => $message->message,
                        'image' => $message->image,
                        'is_read' => $message->is_read,
                        'created_at' => $message->created_at->toDateTimeString(),
                    ];
                });

            // Mark received messages as read
            Usertoagentchat::where('receiver_id', $currentUserId)
                ->where('sender_id', $receiverId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error loading messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load messages'
            ], 500);
        }
    }

    /**
     * Get unread message counts (AJAX)
     */
    public function getUnreadCounts()
    {
        try {
            $currentUserId = Auth::id();

            // Get unread count per agent
            $unreadCounts = Usertoagentchat::select('sender_id', DB::raw('COUNT(*) as count'))
                ->where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->groupBy('sender_id')
                ->pluck('count', 'sender_id');

            // Total unread count
            $totalUnreadCount = $unreadCounts->sum();

            return response()->json([
                'success' => true,
                'total_unread_count' => $totalUnreadCount,
                'unread_by_user' => $unreadCounts
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting unread counts: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'total_unread_count' => 0,
                'unread_by_user' => []
            ]);
        }
    }

    /**
     * Get last messages for each conversation (Optional)
     */
    public function getLastMessages()
    {
        try {
            $currentUserId = Auth::id();

            $lastMessages = Usertoagentchat::select('usertoagentchats.*')
                ->whereIn('id', function ($query) use ($currentUserId) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('usertoagentchats')
                        ->where(function ($q) use ($currentUserId) {
                            $q->where('sender_id', $currentUserId)
                              ->orWhere('receiver_id', $currentUserId);
                        })
                        ->groupBy(DB::raw('CASE
                            WHEN sender_id = ' . $currentUserId . ' THEN receiver_id
                            ELSE sender_id
                        END'));
                })
                ->with(['sender:id,name', 'receiver:id,name'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lastMessages
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting last messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get last messages'
            ], 500);
        }
    }

    /**
     * Delete a message (Optional)
     */
    public function deleteMessage($message_id)
    {
        try {
            $message = Usertoagentchat::findOrFail($message_id);

            // Only sender can delete their message
            if ($message->sender_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own messages'
                ], 403);
            }

            // Delete image file if exists
            if ($message->image && file_exists(public_path($message->image))) {
                unlink(public_path($message->image));
            }

            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message'
            ], 500);
        }
    }
}
