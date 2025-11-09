<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Deposite;
use App\Models\Depositelimite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositeUserController extends BaseController
{
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

// app/Http/Controllers/DepositeUserController.php

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

            // Sum APPROVED deposits from 'deposites' table
            $depositesApproved = DB::table('deposites')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            // Sum APPROVED deposits from 'userdepositerequests' table
            $userDepositRequestsApproved = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->sum('amount');

            // Total approved = Both tables combined
            $totalApproved = ($depositesApproved ?? 0) + ($userDepositRequestsApproved ?? 0);

            return response()->json([
                'success' => true,
                'data' => $totalApproved,
                'breakdown' => [
                    'deposites_approved' => $depositesApproved ?? 0,
                    'userdepositerequests_approved' => $userDepositRequestsApproved ?? 0,
                ],
                'message' => 'Total approved deposit fetched successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching total deposit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all deposits from BOTH tables (approved, pending, rejected)
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
                    DB::raw("transaction_id as transaction_id"),
                    DB::raw("sender_account as sender_account"),
                    'status',
                    DB::raw("NULL as photo"),
                    'created_at',
                    'updated_at',
                    DB::raw("'userdepositerequests' as source")
                ])
                ->get();

            // Merge both collections
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

            // Total from both tables
            $totalApproved = ($depositesApproved ?? 0) + ($userRequestsApproved ?? 0);
            $totalPending = ($depositesPending ?? 0) + ($userRequestsPending ?? 0);
            $totalRejected = ($depositesRejected ?? 0) + ($userRequestsRejected ?? 0);

            return response()->json([
                'success' => true,
                'data' => $allDeposits,
                'summary' => [
                    'total_approved' => $totalApproved,
                    'total_pending' => $totalPending,
                    'total_rejected' => $totalRejected,
                    'total_deposits' => count($allDeposits),
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
            // Validate status
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

            // Get from 'deposites' table
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
                    DB::raw("'deposites' as source")
                ])
                ->get();

            // Get from 'userdepositerequests' table
            $userRequests = DB::table('userdepositerequests')
                ->where('user_id', $user->id)
                ->where('status', $status)
                ->select([
                    'id',
                    'amount',
                    'transaction_id',
                    'sender_account',
                    'status',
                    DB::raw("NULL as photo"),
                    'created_at',
                    DB::raw("'userdepositerequests' as source")
                ])
                ->get();

            // Merge both
            $allDeposits = $deposites->merge($userRequests)
                ->sortByDesc('created_at')
                ->values();

            $total = $allDeposits->sum('amount');

            return response()->json([
                'success' => true,
                'data' => $allDeposits,
                'total_amount' => $total ?? 0,
                'count' => $allDeposits->count(),
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

            // Try to find in 'deposites' table first
            $deposit = DB::table('deposites')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            $source = 'deposites';

            // If not found, try 'userdepositerequests' table
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
}
