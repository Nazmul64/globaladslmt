<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AgentlistController extends Controller
{
    /**
     * Get list of approved agents with their KYC status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function agentlist()
    {
        try {
            // Check if phone column exists in users table
            $hasPhoneColumn = Schema::hasColumn('users', 'phone');

            // Build select query based on available columns
            $selectColumns = ['id', 'name', 'email', 'status', 'photo'];

            // Add phone only if column exists
            if ($hasPhoneColumn) {
                $selectColumns[] = 'phone';
            }

            // Fetch approved agents with their KYC data
            $agents = User::with(['agentkyc' => function($query) {
                $query->select('user_id', 'status');
            }])
            ->where('role', 'agent')
            ->where('status', 'approved')
            ->select($selectColumns)
            ->get()
            ->map(function ($agent) use ($hasPhoneColumn) {
                // Get KYC status
                $kycStatus = $agent->agentkyc ? $agent->agentkyc->status : null;

                return [
                    'id' => $agent->id,
                    'name' => $agent->name ?? 'Unknown Agent',
                    'email' => $agent->email ?? null,
                    'phone' => $hasPhoneColumn ? ($agent->phone ?? null) : null,
                    'status' => $agent->status,
                    'photo' => $agent->photo ?? null,
                    'agentkyc' => $kycStatus ? [
                        'status' => $kycStatus
                    ] : null,
                ];
            });

            Log::info('Agent list requested. Total agents: ' . $agents->count());

            return response()->json([
                'status' => true,
                'message' => 'Approved agents list retrieved successfully',
                'data' => $agents,
                'total' => $agents->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Agent list error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch agents',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test endpoint to verify API connectivity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function test()
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'API connection successful',
                'timestamp' => now()->toDateTimeString(),
                'server' => 'Laravel ' . app()->version()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'API test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single agent details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $hasPhoneColumn = Schema::hasColumn('users', 'phone');
            $selectColumns = ['id', 'name', 'email', 'status', 'photo'];

            if ($hasPhoneColumn) {
                $selectColumns[] = 'phone';
            }

            $agent = User::with(['agentkyc' => function($query) {
                $query->select('user_id', 'status');
            }])
            ->where('role', 'agent')
            ->where('id', $id)
            ->select($selectColumns)
            ->first();

            if (!$agent) {
                return response()->json([
                    'status' => false,
                    'message' => 'Agent not found'
                ], 404);
            }

            $kycStatus = $agent->agentkyc ? $agent->agentkyc->status : null;

            $agentData = [
                'id' => $agent->id,
                'name' => $agent->name ?? 'Unknown Agent',
                'email' => $agent->email ?? null,
                'phone' => $hasPhoneColumn ? ($agent->phone ?? null) : null,
                'status' => $agent->status,
                'photo' => $agent->photo ?? null,
                'agentkyc' => $kycStatus ? [
                    'status' => $kycStatus
                ] : null,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Agent details retrieved successfully',
                'data' => $agentData
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get agent error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch agent details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
