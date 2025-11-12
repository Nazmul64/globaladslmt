<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\ChatRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Agentfrientrequest extends BaseController
{
    /**
     * Send a friend request from the authenticated user to an agent.
     */
    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
        ]);

        $sender_id = Auth::id();
        $receiver_id = $request->receiver_id;

        // Check if user is trying to send request to themselves
        if ($sender_id === $receiver_id) {
            return $this->sendError('You cannot send a friend request to yourself.', [], 400);
        }

        // Check if the receiver is actually an agent
        $receiver = User::find($receiver_id);
        if (!$receiver || $receiver->role !== 'agent') {
            return $this->sendError('Invalid agent selected.', [], 400);
        }

        // Check if friend request already exists in either direction
        $exists = ChatRequest::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
        })
        ->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
        })
        ->first();

        if ($exists) {
            // Return different messages based on status
            if ($exists->status === 'pending') {
                return $this->sendError('Friend request already pending!', [], 409);
            } elseif ($exists->status === 'accepted') {
                return $this->sendError('You are already connected with this agent!', [], 409);
            } elseif ($exists->status === 'rejected') {
                return $this->sendError('Your previous request was declined. Please try again later.', [], 409);
            }
        }

        try {
            // Create new friend request
            $chatRequest = ChatRequest::create([
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'status' => 'pending',
            ]);

            // Load sender and receiver information
            $chatRequest->load(['sender', 'receiver']);

            return $this->sendResponse($chatRequest, 'Friend request sent successfully!');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Check friend request status between authenticated user and an agent.
     */
    public function checkFriendRequestStatus($agent_id)
    {
        $user_id = Auth::id();

        // Check if request exists in either direction
        $request = ChatRequest::where(function ($query) use ($user_id, $agent_id) {
            $query->where('sender_id', $user_id)
                  ->where('receiver_id', $agent_id);
        })
        ->orWhere(function ($query) use ($user_id, $agent_id) {
            $query->where('sender_id', $agent_id)
                  ->where('receiver_id', $user_id);
        })
        ->first();

        if (!$request) {
            return $this->sendResponse([
                'status' => 'none',
                'request_id' => null,
            ], 'No friend request found.');
        }

        return $this->sendResponse([
            'status' => $request->status, // pending, accepted, rejected
            'request_id' => $request->id,
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'created_at' => $request->created_at,
        ], 'Friend request status retrieved successfully.');
    }

    /**
     * Accept a friend request (for agents).
     */
    public function acceptFriendRequest($request_id)
    {
        $agent_id = Auth::id();

        // Find the request where authenticated user is the receiver
        $chatRequest = ChatRequest::where('id', $request_id)
            ->where('receiver_id', $agent_id)
            ->where('status', 'pending')
            ->first();

        if (!$chatRequest) {
            return $this->sendError('Friend request not found or already processed.', [], 404);
        }

        try {
            // Update request status to accepted
            $chatRequest->status = 'accepted';
            $chatRequest->save();

            $chatRequest->load(['sender', 'receiver']);

            return $this->sendResponse($chatRequest, 'Friend request accepted successfully!');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Reject a friend request (for agents).
     */
    public function rejectFriendRequest($request_id)
    {
        $agent_id = Auth::id();

        // Find the request where authenticated user is the receiver
        $chatRequest = ChatRequest::where('id', $request_id)
            ->where('receiver_id', $agent_id)
            ->where('status', 'pending')
            ->first();

        if (!$chatRequest) {
            return $this->sendError('Friend request not found or already processed.', [], 404);
        }

        try {
            // Update request status to rejected
            $chatRequest->status = 'rejected';
            $chatRequest->save();

            return $this->sendResponse($chatRequest, 'Friend request rejected.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Get all friend requests received by the authenticated agent.
     */
    public function getReceivedRequests()
    {
        $agent_id = Auth::id();

        try {
            $requests = ChatRequest::where('receiver_id', $agent_id)
                ->with(['sender' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'photo');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->sendResponse($requests, 'Friend requests retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Get all friend requests sent by the authenticated user.
     */
    public function getSentRequests()
    {
        $user_id = Auth::id();

        try {
            $requests = ChatRequest::where('sender_id', $user_id)
                ->with(['receiver' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'photo');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->sendResponse($requests, 'Sent requests retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Get all accepted friend requests (connected agents/users).
     */
    public function getConnectedAgents()
    {
        $user_id = Auth::id();

        try {
            $connections = ChatRequest::where('status', 'accepted')
                ->where(function ($query) use ($user_id) {
                    $query->where('sender_id', $user_id)
                          ->orWhere('receiver_id', $user_id);
                })
                ->with(['sender' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'photo', 'role');
                }, 'receiver' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'photo', 'role');
                }])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Format the response to show the "other" user
            $formattedConnections = $connections->map(function ($connection) use ($user_id) {
                $otherUser = $connection->sender_id === $user_id
                    ? $connection->receiver
                    : $connection->sender;

                return [
                    'request_id' => $connection->id,
                    'user' => $otherUser,
                    'connected_at' => $connection->updated_at,
                ];
            });

            return $this->sendResponse($formattedConnections, 'Connected agents retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancelFriendRequest($request_id)
    {
        $user_id = Auth::id();

        // Find the request where authenticated user is the sender
        $chatRequest = ChatRequest::where('id', $request_id)
            ->where('sender_id', $user_id)
            ->where('status', 'pending')
            ->first();

        if (!$chatRequest) {
            return $this->sendError('Friend request not found or already processed.', [], 404);
        }

        try {
            // Delete the request
            $chatRequest->delete();

            return $this->sendResponse([], 'Friend request cancelled successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Something went wrong!', [$e->getMessage()], 500);
        }
    }
}
