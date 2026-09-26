<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Deposite;
use App\Models\Depositelimite;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositeUserController extends BaseController
{
    /**
     * Submit new deposit request
     * Route: POST /api/deposite
     */
    public function deposite(Request $request)
    {
        $user = Auth::user();

        // Get deposit limit setting
        $limits = Depositelimite::first();
        if (!$limits) {
            return $this->sendError('Deposit limit not set in the system.', [], 400);
        }

        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'transaction_id' => 'required|string|max:255',
            'sender_account' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Check amount limit
        if ($request->amount < $limits->min_deposit || $request->amount > $limits->max_deposit) {
            return $this->sendError("Deposit amount must be between {$limits->min_deposit} and {$limits->max_deposit}.", [], 422);
        }

        $deposit = new Deposite();
        $deposit->user_id = $user->id;
        $deposit->amount = $request->amount;
        $deposit->transaction_id = $request->transaction_id;
        $deposit->sender_account = $request->sender_account;
        $deposit->status = 'pending';

        // Upload photo if exists
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/deposits'), $filename);
            $deposit->photo = $filename;
        }

        $deposit->save();

        PushNotificationService::send(
            $user->id,
            "Deposit Request Submitted",
            "Your deposit request of {$deposit->amount} USDT has been submitted successfully.",
            "deposit",
            ['deposit_id' => $deposit->id, 'amount' => $deposit->amount]
        );

        $responseData = [
            'id' => $deposit->id,
            'amount' => $deposit->amount,
            'transaction_id' => $deposit->transaction_id,
            'sender_account' => $deposit->sender_account,
            'status' => $deposit->status,
            'photo' => $deposit->photo ? url('uploads/deposits/' . $deposit->photo) : null,
            'min_limit' => $limits->min_deposit,
            'max_limit' => $limits->max_deposit,
        ];

        return $this->sendResponse($responseData, 'Deposit request submitted successfully and pending for approval.');
    }

    /**
     * Get user balance from users table
     * Route: GET /api/totaldeposite
     */
    public function totaldeposite(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Refresh user data to get latest balance
            $user->refresh();
            $userBalance = $user->balance ?? 0;

            return response()->json([
                'success' => true,
                'data' => $userBalance,
                'message' => 'Total approved deposit (balance) fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user balance
     * Route: GET /api/user/balance
     */
    public function getUserBalance(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Refresh user to get latest balance
            $user->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $user->balance ?? 0,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'message' => 'User balance fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 NEW: Recalculate and sync user balance
     * This will calculate approved deposits from both tables and update users.balance
     * Route: POST /api/user/sync-balance
     */
    public function syncUserBalance(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Calculate approved from deposites table
            $depositesApproved = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount') ?? 0;

            // Calculate approved from userdepositerequests table
            $userRequestsApproved = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount') ?? 0;

            // Total approved
            $totalApproved = $depositesApproved + $userRequestsApproved;

            // Update user balance
            $oldBalance = $user->balance;
            $user->balance = $totalApproved;
            $user->save();

            Log::info("Balance Sync: User {$user->id} - Old: {$oldBalance}, New: {$totalApproved}");

            return response()->json([
                'success' => true,
                'data' => [
                    'old_balance' => $oldBalance,
                    'new_balance' => $totalApproved,
                    'deposites_approved' => $depositesApproved,
                    'requests_approved' => $userRequestsApproved,
                ],
                'message' => 'Balance synchronized successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error("Balance Sync Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error syncing balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all deposits from BOTH tables with summary
     * Route: GET /api/userDeposits
     */
    public function userDeposits(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Get deposits from 'deposites' table
            $deposites = DB::table('deposites')
                ->where('user_id', $user->id)
                ->select([
                    'id',
                    'amount',
                    'transaction_id',
                    'sender_account',
                    'status',
                    'photo',
                    'created_at',
                    'updated_at',
                    DB::raw("'deposites' as source")
                ])
                ->get();

            // Get deposits from 'userdepositerequests' table
            $userDepositRequests = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->select([
                    'id',
                    'amount',
                    DB::raw("COALESCE(transaction_id, 'N/A') as transaction_id"),
                    DB::raw("COALESCE(sender_account, 'N/A') as sender_account"),
                    'status',
                    DB::raw("NULL as photo"),
                    'created_at',
                    'updated_at',
                    DB::raw("'userdepositerequests' as source")
                ])
                ->get();

            // Merge both collections and sort by created_at desc
            $allDeposits = $deposites->merge($userDepositRequests)
                ->sortByDesc('created_at')
                ->values()
                ->toArray();

            // Calculate summary from BOTH tables
            $depositesApproved = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            $userRequestsApproved = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            $depositesPending = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount');

            $userRequestsPending = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->sum('amount');

            $depositesRejected = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', 'rejected')
                ->sum('amount');

            $userRequestsRejected = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', 'rejected')
                ->sum('amount');

            // Calculate totals from both tables
            $totalApproved = ($depositesApproved ?? 0) + ($userRequestsApproved ?? 0);
            $totalPending = ($depositesPending ?? 0) + ($userRequestsPending ?? 0);
            $totalRejected = ($depositesRejected ?? 0) + ($userRequestsRejected ?? 0);

            // Get current balance from users table (refresh first)
            $user->refresh();
            $currentBalance = $user->balance ?? 0;

            // 🔥 Auto-sync if balance doesn't match
            if ($currentBalance != $totalApproved) {
                Log::warning("Balance Mismatch Detected: User {$user->id} - DB: {$currentBalance}, Calculated: {$totalApproved}");

                // Auto-fix the balance
                $user->balance = $totalApproved;
                $user->save();
                $currentBalance = $totalApproved;

                Log::info("Balance Auto-Fixed: User {$user->id} updated to {$totalApproved}");
            }

            return response()->json([
                'success' => true,
                'data' => $allDeposits,
                'summary' => [
                    'total_approved' => $totalApproved,
                    'total_pending' => $totalPending,
                    'total_rejected' => $totalRejected,
                    'total_deposits' => count($allDeposits),
                    'current_balance' => $currentBalance,
                ],
                'breakdown' => [
                    'deposites_table' => [
                        'approved' => $depositesApproved ?? 0,
                        'pending' => $depositesPending ?? 0,
                        'rejected' => $depositesRejected ?? 0,
                    ],
                    'userdepositerequests_table' => [
                        'approved' => $userRequestsApproved ?? 0,
                        'pending' => $userRequestsPending ?? 0,
                        'rejected' => $userRequestsRejected ?? 0,
                    ],
                ],
                'message' => 'User deposits fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user deposits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deposits by status from BOTH tables
     * Route: GET /api/userDeposits/{status}
     */
    public function userDepositsByStatus(Request $request, $status)
    {
        try {
            $validStatuses = ['pending', 'approved', 'rejected'];
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status. Must be: pending, approved, or rejected'
                ], 400);
            }

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $deposites = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', $status)
                ->select([
                    'id',
                    'amount',
                    'transaction_id',
                    'sender_account',
                    'status',
                    'photo',
                    'created_at',
                    'updated_at',
                    DB::raw("'deposites' as source")
                ])
                ->get();

            $userRequests = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', $status)
                ->select([
                    'id',
                    'amount',
                    DB::raw("COALESCE(transaction_id, 'N/A') as transaction_id"),
                    DB::raw("COALESCE(sender_account, 'N/A') as sender_account"),
                    'status',
                    DB::raw("NULL as photo"),
                    'created_at',
                    'updated_at',
                    DB::raw("'userdepositerequests' as source")
                ])
                ->get();

            $allDeposits = $deposites->merge($userRequests)
                ->sortByDesc('created_at')
                ->values();

            $total = $allDeposits->sum('amount');

            return response()->json([
                'success' => true,
                'data' => $allDeposits,
                'summary' => [
                    'total_amount' => $total ?? 0,
                    'count' => $allDeposits->count(),
                    'status' => $status,
                ],
                'message' => ucfirst($status) . ' deposits fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching deposits by status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single deposit details from BOTH tables
     * Route: GET /api/deposit/{id}
     */
    public function getDepositById(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $deposit = DB::table('deposites')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            $source = 'deposites';

            if (!$deposit) {
                $deposit = DB::table('userdepositerequests')
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();
                $source = 'userdepositerequests';
            }

            if (!$deposit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $deposit,
                'source' => $source,
                'message' => 'Deposit details fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching deposit details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deposit statistics
     * Route: GET /api/deposit/statistics
     */
    public function getDepositStatistics(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $stats = [
                'user_balance' => $user->balance ?? 0,
                'deposites_table' => [
                    'total_count' => DB::table('deposites')->where('user_id', $user->id)->count(),
                    'approved_count' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'approved')->count(),
                    'pending_count' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'pending')->count(),
                    'rejected_count' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'rejected')->count(),
                    'approved_sum' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'approved')->sum('amount') ?? 0,
                    'pending_sum' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'pending')->sum('amount') ?? 0,
                    'rejected_sum' => DB::table('deposites')->where('user_id', $user->id)->where('status', 'rejected')->sum('amount') ?? 0,
                ],
                'userdepositerequests_table' => [
                    'total_count' => DB::table('userdepositerequests')->where('user_id', $user->id)->count(),
                    'approved_count' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'approved')->count(),
                    'pending_count' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'pending')->count(),
                    'rejected_count' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'rejected')->count(),
                    'approved_sum' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'approved')->sum('amount') ?? 0,
                    'pending_sum' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'pending')->sum('amount') ?? 0,
                    'rejected_sum' => DB::table('userdepositerequests')->where('user_id', $user->id)->where('status', 'rejected')->sum('amount') ?? 0,
                ],
            ];

            $combinedStats = [
                'total_deposits_count' => $stats['deposites_table']['total_count'] + $stats['userdepositerequests_table']['total_count'],
                'total_approved_amount' => $stats['deposites_table']['approved_sum'] + $stats['userdepositerequests_table']['approved_sum'],
                'total_pending_amount' => $stats['deposites_table']['pending_sum'] + $stats['userdepositerequests_table']['pending_sum'],
                'total_rejected_amount' => $stats['deposites_table']['rejected_sum'] + $stats['userdepositerequests_table']['rejected_sum'],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'user_balance' => $stats['user_balance'],
                    'combined_statistics' => $combinedStats,
                    'detailed_breakdown' => $stats,
                ],
                'message' => 'Deposit statistics fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
