<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TotalwidthrawhistoryController extends Controller
{
    /**
     * Get total withdrawal history for authenticated user
     * ডাটাবেজ থেকে user_widthdraws টেবিল থেকে সব ডাটা নিয়ে আসবে
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function totalWidthrawHistory(Request $request)
    {
        try {
            // ✅ Get authenticated user via Sanctum
            $user = $request->user();

            if (!$user) {
                Log::warning('❌ Unauthorized access attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized. Please login first.',
                    'data'    => null
                ], 401);
            }

            $userId = $user->id;

            Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', ['context' => 'API Request']);
            Log::info('📥 Withdrawal history request started', [
                'user_id' => $userId,
                'user_email' => $user->email ?? 'N/A',
                'user_name' => $user->name ?? 'N/A',
                'request_time' => now()->toDateTimeString()
            ]);

            // ✅ Query থেকে ডাটা নিয়ে আসো - user_widthraws table থেকে (correct spelling)
            try {
                // Total withdrawals count
                $totalWithdrawals = DB::table('user_widthraws')
                    ->where('user_id', $userId)
                    ->count();

                Log::info('✅ Total withdrawals counted', [
                    'user_id' => $userId,
                    'count' => $totalWithdrawals
                ]);

                // Total amount withdrawn (sum of amount column)
                $totalAmountWithdrawn = DB::table('user_widthraws')
                    ->where('user_id', $userId)
                    ->sum('amount');

                Log::info('✅ Total amount calculated', [
                    'user_id' => $userId,
                    'amount' => $totalAmountWithdrawn
                ]);

                // Count by status - pending
                $pendingWithdrawals = DB::table('user_widthraws')
                    ->where('user_id', $userId)
                    ->where('status', 'pending')
                    ->count();

                Log::info('✅ Pending withdrawals counted', [
                    'user_id' => $userId,
                    'count' => $pendingWithdrawals
                ]);

                // Count by status - approved
                $approvedWithdrawals = DB::table('user_widthraws')
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->count();

                Log::info('✅ Approved withdrawals counted', [
                    'user_id' => $userId,
                    'count' => $approvedWithdrawals
                ]);

                // Count by status - rejected
                $rejectedWithdrawals = DB::table('user_widthraws')
                    ->where('user_id', $userId)
                    ->where('status', 'rejected')
                    ->count();

                Log::info('✅ Rejected withdrawals counted', [
                    'user_id' => $userId,
                    'count' => $rejectedWithdrawals
                ]);

            } catch (\Exception $dbError) {
                Log::error('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', ['context' => 'Database Error']);
                Log::error('❌ Database query error', [
                    'user_id' => $userId,
                    'error' => $dbError->getMessage(),
                    'file' => $dbError->getFile(),
                    'line' => $dbError->getLine(),
                    'trace' => $dbError->getTraceAsString()
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Database error: ' . $dbError->getMessage(),
                    'data'    => null
                ], 500);
            }

            // ✅ Prepare response data
            $responseData = [
                'total_withdrawals'       => (int) $totalWithdrawals,
                'total_amount_withdrawn'  => (float) ($totalAmountWithdrawn ?? 0.0),
                'pending_withdrawals'     => (int) $pendingWithdrawals,
                'approved_withdrawals'    => (int) $approvedWithdrawals,
                'rejected_withdrawals'    => (int) $rejectedWithdrawals,
            ];

            Log::info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', ['context' => 'Success']);
            Log::info('✅ Withdrawal history fetched successfully', [
                'user_id' => $userId,
                'data' => $responseData
            ]);

            // ✅ Return success response
            return response()->json([
                'status'  => true,
                'message' => 'Total withdrawal history fetched successfully',
                'data'    => $responseData
            ], 200);

        } catch (\Exception $e) {
            // ✅ Catch any unexpected errors
            Log::error('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', ['context' => 'Unexpected Error']);
            Log::error('❌ Unexpected error in withdrawal history', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch withdrawal history. Please try again later.',
                'data'    => null,
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Get detailed withdrawal list with pagination
     * সব withdrawal এর লিস্ট দেখাবে
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdrawalList(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized',
                    'data'    => null
                ], 401);
            }

            $userId = $user->id;
            $perPage = $request->input('per_page', 10);
            $status = $request->input('status'); // Optional filter: pending, approved, rejected

            Log::info('📥 Withdrawal list request', [
                'user_id' => $userId,
                'per_page' => $perPage,
                'status_filter' => $status
            ]);

            $query = DB::table('user_widthraws')
                ->where('user_id', $userId)
                ->select(
                    'id',
                    'payment_method_id',
                    'account_number',
                    'wallet_address',
                    'amount',
                    'status',
                    'created_at',
                    'updated_at'
                );

            // Apply status filter if provided
            if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $status);
            }

            // Order by latest first
            $withdrawals = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            Log::info('✅ Withdrawal list fetched', [
                'user_id' => $userId,
                'total' => $withdrawals->total(),
                'count' => $withdrawals->count()
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Withdrawal list fetched successfully',
                'data'    => $withdrawals
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error fetching withdrawal list', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch withdrawal list',
                'data'    => null
            ], 500);
        }
    }

    /**
     * Get single withdrawal details
     * একটা নির্দিষ্ট withdrawal এর ডিটেইলস দেখাবে
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWithdrawalDetails(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized',
                    'data'    => null
                ], 401);
            }

            $userId = $user->id;

            Log::info('📥 Withdrawal details request', [
                'user_id' => $userId,
                'withdrawal_id' => $id
            ]);

            // Get withdrawal details - নিশ্চিত করো যে এটা ঐ user এর withdrawal
            $withdrawal = DB::table('user_widthraws')
                ->where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$withdrawal) {
                Log::warning('⚠️ Withdrawal not found', [
                    'user_id' => $userId,
                    'withdrawal_id' => $id
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Withdrawal not found',
                    'data'    => null
                ], 404);
            }

            Log::info('✅ Withdrawal details fetched', [
                'user_id' => $userId,
                'withdrawal_id' => $id
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Withdrawal details fetched successfully',
                'data'    => $withdrawal
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error fetching withdrawal details', [
                'error' => $e->getMessage(),
                'withdrawal_id' => $id
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch withdrawal details',
                'data'    => null
            ], 500);
        }
    }

    /**
     * Debug endpoint - ডিবাগিং এর জন্য (Production এ remove করে দিও!)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function debugInfo(Request $request)
    {
        try {
            $user = $request->user();

            $debugInfo = [
                'authenticated' => $user ? true : false,
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? null,
                'user_email' => $user->email ?? null,
                'token_abilities' => $request->user()?->currentAccessToken()?->abilities ?? [],
                'database_connection' => DB::connection()->getDatabaseName(),
                'table_exists' => DB::getSchemaBuilder()->hasTable('user_widthdraws'),
                'withdrawal_count' => $user ? DB::table('user_widthdraws')->where('user_id', $user->id)->count() : 0,
                'sample_withdrawal' => $user ? DB::table('user_widthdraws')->where('user_id', $user->id)->first() : null,
            ];

            Log::info('🔍 Debug info requested', $debugInfo);

            return response()->json([
                'status' => true,
                'message' => 'Debug info',
                'data' => $debugInfo
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Debug failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check table structure
     * টেবিল structure চেক করার জন্য
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTableStructure(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthorized',
                    'data'    => null
                ], 401);
            }

            // Check if table exists
            $tableExists = DB::getSchemaBuilder()->hasTable('user_widthdraws');

            if (!$tableExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Table user_widthdraws does not exist!',
                    'data' => [
                        'table_exists' => false,
                        'database' => DB::connection()->getDatabaseName()
                    ]
                ], 500);
            }

            // Get table columns
            $columns = DB::select('SHOW COLUMNS FROM user_widthdraws');

            // Get sample data count
            $totalCount = DB::table('user_widthdraws')->count();
            $userCount = DB::table('user_widthdraws')->where('user_id', $user->id)->count();

            Log::info('✅ Table structure checked', [
                'table' => 'user_widthdraws',
                'columns_count' => count($columns),
                'total_records' => $totalCount,
                'user_records' => $userCount
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Table structure',
                'data' => [
                    'table_exists' => true,
                    'database' => DB::connection()->getDatabaseName(),
                    'columns' => $columns,
                    'total_records' => $totalCount,
                    'user_records' => $userCount,
                    'sample_record' => DB::table('user_widthdraws')->where('user_id', $user->id)->first()
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error checking table structure', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to check table structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
