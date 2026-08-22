<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\ChatRequest;
use App\Models\User;
use App\Models\Usertoagentchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsertoagentChatController extends BaseController
{
    /* ==========================================================
     | 📥 GET : Fetch Messages - COMPLETE FIX (Agent + User images)
     ==========================================================*/
    public function fetchMessages(Request $request)
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id',
            ]);

            $userId  = Auth::id();
            $agentId = $request->agent_id;

            if (!$userId) return $this->unauthorized();

            // ✅ Get messages between user and agent (both directions)
            $messages = Usertoagentchat::where(function ($q) use ($userId, $agentId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $agentId);
                })
                ->orWhere(function ($q) use ($userId, $agentId) {
                    $q->where('sender_id', $agentId)->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'asc')
                ->paginate(50);

            Log::info("=== Fetching Messages ===");
            Log::info("User ID: {$userId}, Agent ID: {$agentId}");
            Log::info("Total Messages: {$messages->count()}");

            // ✅ CRITICAL FIX: Format messages properly with image URLs
            $data = $messages->getCollection()->map(function ($msg) use ($agentId, $userId) {
                $senderType = $msg->sender_id == $agentId ? 'agent' : 'user';

                // ✅ FIXED: Proper image URL generation for BOTH directions
                $imageUrl = null;
                $messageType = $msg->message_type ?? 'text';

                if ($msg->image && !empty($msg->image)) {
                    // Clean the path
                    $cleanPath = str_replace('public/', '', $msg->image);
                    $cleanPath = ltrim($cleanPath, '/');

                    // Check if file exists
                    $fullPath = public_path($cleanPath);

                    if (file_exists($fullPath)) {
                        $imageUrl = url($cleanPath);
                        $messageType = 'image';
                        Log::info("✅ Image found - Message ID: {$msg->id}, Sender: {$senderType}, URL: {$imageUrl}");
                    } else {
                        Log::warning("❌ Image NOT found - Message ID: {$msg->id}, Path: {$fullPath}");
                        Log::warning("   Original: {$msg->image}, Clean: {$cleanPath}");

                        // Try alternative paths
                        $altPaths = [
                            'uploads/chat/' . basename($msg->image),
                            'uploads/' . basename($msg->image),
                        ];

                        foreach ($altPaths as $altPath) {
                            $altFullPath = public_path($altPath);
                            if (file_exists($altFullPath)) {
                                $imageUrl = url($altPath);
                                $messageType = 'image';
                                Log::info("✅ Image found in alternative path: {$imageUrl}");

                                // Update database with correct path
                                DB::table('usertoagentchats')
                                    ->where('id', $msg->id)
                                    ->update(['image' => $altPath, 'message_type' => 'image']);
                                break;
                            }
                        }
                    }
                }

                return [
                    'id'           => $msg->id,
                    'sender_id'    => $msg->sender_id,
                    'receiver_id'  => $msg->receiver_id,
                    'sender_type'  => $senderType,
                    'message'      => $msg->message ?? '',
                    'message_type' => $messageType,
                    'image_url'    => $imageUrl,
                    'is_read'      => (bool) $msg->is_read,
                    'created_at'   => $msg->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'messages'   => $data,
                    'pagination' => [
                        'page'  => $messages->currentPage(),
                        'total' => $messages->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('FetchMessages Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | 📤 POST : Send Message - COMPLETE FIX (User & Agent)
     ==========================================================*/
    public function sendMessage(Request $request)
    {
        try {
            Log::info('=== Send Message Request ===');
            Log::info('Agent ID: ' . $request->agent_id);
            Log::info('Message: ' . $request->message);
            Log::info('Has Image: ' . ($request->hasFile('image') ? 'YES' : 'NO'));
            Log::info('Auth User ID: ' . Auth::id());

            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'message'  => 'nullable|string|max:1000',
                'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            ]);

            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            if (!$request->message && !$request->hasFile('image')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message or image is required',
                ], 422);
            }

            $imagePath = null;
            $type      = 'text';

            // ✅ FIXED: Upload image to uploads/chat folder
            if ($request->hasFile('image')) {
                $image     = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $filename  = 'chat_' . uniqid() . '_' . time() . '.' . $extension;
                $folder    = 'uploads/chat';

                // Create directory if not exists
                $fullPath = public_path($folder);
                if (!file_exists($fullPath)) {
                    mkdir($fullPath, 0755, true);
                    Log::info("📁 Created folder: {$folder}");
                }

                try {
                    $image->move($fullPath, $filename);
                    $imagePath = $folder . '/' . $filename;
                    $type      = 'image';

                    Log::info("✅ Image uploaded successfully");
                    Log::info("   Folder: {$folder}");
                    Log::info("   Filename: {$filename}");
                    Log::info("   Full Path: {$fullPath}/{$filename}");
                    Log::info("   DB Path: {$imagePath}");

                    // Verify file exists
                    $verifyPath = $fullPath . '/' . $filename;
                    Log::info("   File exists: " . (file_exists($verifyPath) ? 'YES ✓' : 'NO ✗'));

                    if (!file_exists($verifyPath)) {
                        throw new \Exception("File upload verification failed");
                    }
                } catch (\Exception $e) {
                    Log::error("❌ Image upload failed: " . $e->getMessage());
                    throw $e;
                }
            }

            // ✅ Create chat message
            $chat = Usertoagentchat::create([
                'sender_id'    => $userId,
                'receiver_id'  => $request->agent_id,
                'message'      => $request->message ?? '',
                'image'        => $imagePath,
                'message_type' => $type,
                'is_read'      => false,
            ]);

            Log::info("💾 Message saved to DB with ID: {$chat->id}");
            Log::info("   Message Type: {$type}");
            Log::info("   Image Path: " . ($imagePath ?? 'null'));

            // ✅ FIXED: Return proper image URL
            $imageUrl = null;
            if ($imagePath) {
                $imageUrl = url($imagePath);
                Log::info("📤 Returning image URL: {$imageUrl}");

                // Final verification
                $testPath = public_path($imagePath);
                Log::info("🔍 Final Check - File exists: " . (file_exists($testPath) ? 'YES ✓' : 'NO ✗'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id'           => $chat->id,
                    'sender_id'    => $chat->sender_id,
                    'receiver_id'  => $chat->receiver_id,
                    'sender_type'  => 'user',
                    'message'      => $chat->message ?? '',
                    'message_type' => $type,
                    'image_url'    => $imageUrl,
                    'is_read'      => false,
                    'created_at'   => $chat->created_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('SendMessage Error: ' . $e->getMessage());
            Log::error('Stack: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ==========================================================
     | 🗑 DELETE : Delete Message - FIXED
     ==========================================================*/
    public function deleteMessage($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            $message = Usertoagentchat::find($id);
            if (!$message) return $this->notFound('Message not found');

            if ($message->sender_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied',
                ], 403);
            }

            // ✅ Delete image file if exists
            if ($message->image) {
                $cleanPath = str_replace('public/', '', $message->image);
                $cleanPath = ltrim($cleanPath, '/');
                $fullPath = public_path($cleanPath);

                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    Log::info("🗑️ Image deleted: {$cleanPath}");
                }
            }

            $message->delete();
            return $this->success('Message deleted successfully');
        } catch (\Exception $e) {
            Log::error('DeleteMessage Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | ✅ POST : Mark Messages As Read
     ==========================================================*/
    public function markAsRead(Request $request)
    {
        try {
            $request->validate(['agent_id' => 'required|exists:users,id']);

            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            $updated = Usertoagentchat::where('sender_id', $request->agent_id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->success('Messages marked as read', ['updated' => $updated]);
        } catch (\Exception $e) {
            Log::error('MarkAsRead Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | 📊 GET : Unread Count
     ==========================================================*/
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            $count = Usertoagentchat::where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();

            return $this->success('Unread count loaded', ['count' => $count]);
        } catch (\Exception $e) {
            Log::error('UnreadCount Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | 👥 GET : Agents List
     ==========================================================*/
    public function getAgentsList()
    {
        try {
            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            $agents = User::where('role', 'agent')->get();
            $agentIds = $agents->pluck('id')->toArray();

            // Fetch unread counts in bulk (1 query)
            $unreadCounts = collect();
            if (!empty($agentIds)) {
                $unreadCounts = Usertoagentchat::where('receiver_id', $userId)
                    ->whereIn('sender_id', $agentIds)
                    ->where('is_read', false)
                    ->select('sender_id', DB::raw('COUNT(*) as unread_count'))
                    ->groupBy('sender_id')
                    ->pluck('unread_count', 'sender_id');
            }

            $list = $agents->map(fn ($agent) => $this->formatAgent($agent, $userId, $unreadCounts));

            return $this->success('Agents loaded', ['agents' => $list]);
        } catch (\Exception $e) {
            Log::error('AgentsList Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | 👤 GET : Single Agent Info
     ==========================================================*/
    public function getAgentInfo()
    {
        try {
            $userId = Auth::id();
            if (!$userId) return $this->unauthorized();

            $agent = ChatRequest::where('sender_id', $userId)
                ->where('status', 'accepted')
                ->with('receiver')
                ->first()
                ?->receiver;

            if (!$agent) {
                $agent = User::where('role', 'agent')->first();
            }

            if (!$agent) return $this->notFound('No agent available');

            return $this->success('Agent loaded', $this->formatAgent($agent, $userId));
        } catch (\Exception $e) {
            Log::error('GetAgentInfo Error: ' . $e->getMessage());
            return $this->serverError();
        }
    }

    /* ==========================================================
     | 🔧 PRIVATE : Format Agent Data
     ==========================================================*/
    private function formatAgent($agent, $userId, $unreadCounts = null)
    {
        $photoPath = null;
        if ($agent->photo) {
            $cleanPath = str_replace('uploads', '', $agent->photo);
            $cleanPath = ltrim($cleanPath, '/');

            if (strpos($cleanPath, 'uploads/agent/') === false) {
                $cleanPath = 'uploads/agent/' . basename($cleanPath);
            }

            $fullPath = public_path($cleanPath);
            if (file_exists($fullPath)) {
                $photoPath = url($cleanPath);
            }
        }

        $unreadCount = 0;
        if ($unreadCounts) {
            $unreadCount = $unreadCounts->get($agent->id, 0);
        } else {
            $unreadCount = Usertoagentchat::where('sender_id', $agent->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
        }

        return [
            'id'           => $agent->id,
            'name'         => $agent->name ?? 'Support Agent',
            'email'        => $agent->email,
            'avatar'       => $photoPath,
            'status'       => 'available',
            'unread_count' => $unreadCount,
        ];
    }

    /* ==========================================================
     | 🧩 Helper Methods
     ==========================================================*/
    private function success($message, $data = [])
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function unauthorized()
    {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    private function notFound($message)
    {
        return response()->json(['success' => false, 'message' => $message], 404);
    }

    private function serverError()
    {
        return response()->json(['success' => false, 'message' => 'Server error'], 500);
    }
}
