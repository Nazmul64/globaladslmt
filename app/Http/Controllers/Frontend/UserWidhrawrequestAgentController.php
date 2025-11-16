<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\Agentbuysellpost;
use App\Models\Category;
use App\Models\User;
use App\Models\AgentDeposite;
use App\Models\Agentcommissonsetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserWidhrawrequestAgentController extends Controller
{
    /**
     * Display Buy/Sell Posts Page
     */
    public function buysellpost()
    {
        try {
            $categories = Category::all();
            $all_agentbuysellpost = Agentbuysellpost::with(['category', 'user', 'dollarsign', 'agentamounts'])
                ->where('status', 'approved')
                ->latest()
                ->get();

            return view('frontend.buyandsellpost.index', compact('categories', 'all_agentbuysellpost'));
        } catch (\Exception $e) {
            Log::error('Buy/Sell Post Page Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'Failed to load posts. Please try again.');
        }
    }

    /**
     * User Sends Deposit/Withdraw Request (Step 1)
     * Route: POST user/deposit/request OR POST user/withdraw/request
     */
    public function userwidhraw_request(Request $request)
    {
        DB::beginTransaction();

        try {
            Log::info('Request Started', [
                'type' => $request->type,
                'user_id' => Auth::id(),
                'data' => $request->except('_token')
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Check if user has sufficient balance for withdraw
            if ($request->type === 'withdraw') {
                $user = User::lockForUpdate()->find(Auth::id());

                if (!$user) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found'
                    ], 404);
                }

                if ($user->balance < $request->amount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient balance. Your balance: ' . number_format($user->balance, 2) . ' USDT'
                    ], 400);
                }
            }

            // Check deposit/withdraw limits from post
            $post = Agentbuysellpost::find($request->post_id);
            if (!$post) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"
                ], 400);
            }

            // Check for pending requests
            if ($request->type === 'deposit') {
                $existingRequest = Userdepositerequest::where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])
                    ->first();
            } else {
                $existingRequest = UserWidhrawrequest::where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'agent_confirmed'])
                    ->first();
            }

            if ($existingRequest) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending ' . $request->type . ' request. Please complete or cancel it first.'
                ], 400);
            }

            // Prepare data for creation
            $data = [
                'user_id' => Auth::id(),
                'agent_id' => $request->agent_id,
                'amount' => $request->amount,
                'status' => 'pending',
                'transaction_id' => null,
                'sender_account' => null,
                'photo' => null,
                'agent_commission' => 0,
                'admin_commission' => 0,
            ];

            // Create request based on type
            if ($request->type === 'deposit') {
                // Deposits table HAS type field
                $data['type'] = 'deposit';
                $record = Userdepositerequest::create($data);
                $message = 'Deposit request sent successfully! Waiting for agent confirmation.';

                Log::info('Deposit Request Created', [
                    'deposit_id' => $record->id,
                    'user_id' => Auth::id(),
                    'agent_id' => $request->agent_id,
                    'amount' => $request->amount
                ]);
            } else {
                // Withdraws table does NOT have type field
                $record = UserWidhrawrequest::create($data);
                $message = 'Withdraw request sent successfully! Waiting for agent confirmation.';

                Log::info('Withdraw Request Created', [
                    'withdraw_id' => $record->id,
                    'user_id' => Auth::id(),
                    'agent_id' => $request->agent_id,
                    'amount' => $request->amount
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'request_id' => $record->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request Creation Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'type' => $request->type ?? 'unknown',
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Deposit Status (Polling - Step 2)
     * Route: GET user/deposit/status
     */
    public function checkDepositStatus()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($deposit) {
                Log::info('Deposit Status Check - Agent Confirmed', [
                    'deposit_id' => $deposit->id,
                    'user_id' => Auth::id(),
                    'amount' => $deposit->amount
                ]);

                return response()->json([
                    'status' => 'agent_confirmed',
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount
                ]);
            }

            return response()->json(['status' => 'pending']);

        } catch (\Exception $e) {
            Log::error('Deposit Status Check Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User Submits Deposit Details (Step 3)
     * Route: POST user/deposit/submit/{id}
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Log::info('Deposit Submit Started', [
                'deposit_id' => $id,
                'user_id' => Auth::id(),
                'has_photo' => $request->hasFile('photo'),
                'request_data' => $request->except('photo')
            ]);

            // Validate input
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::error('Deposit Validation Failed', [
                    'errors' => $validator->errors()->toArray(),
                    'deposit_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Find and lock deposit record
            $deposit = Userdepositerequest::lockForUpdate()->find($id);

            if (!$deposit) {
                DB::rollBack();
                Log::error('Deposit Not Found', ['deposit_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit record not found'
                ], 404);
            }

            // Security check - verify ownership
            if ($deposit->user_id !== Auth::id()) {
                DB::rollBack();
                Log::warning('Unauthorized Deposit Access Attempt', [
                    'deposit_id' => $id,
                    'actual_user' => $deposit->user_id,
                    'attempted_user' => Auth::id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Status check - must be agent_confirmed
            if ($deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                Log::warning('Invalid Deposit Status', [
                    'deposit_id' => $id,
                    'current_status' => $deposit->status,
                    'expected_status' => 'agent_confirmed'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit not confirmed by agent. Current status: ' . $deposit->status
                ], 400);
            }

            // Prevent double submission
            if ($deposit->status === 'user_submitted' || $deposit->status === 'completed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This deposit has already been submitted'
                ], 400);
            }

            // Handle photo upload
           $photoPath = null;

   if ($request->hasFile('photo')) {
    try {
        $file = $request->file('photo');

        // Validate file
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        // Generate unique file name
        $filename = 'deposit_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Upload folder path
        $uploadPath = public_path('uploads/deposit');

        // Create directory if not exists
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Delete old photo if exists
        if ($deposit->photo && file_exists(public_path($deposit->photo))) {
            @unlink(public_path($deposit->photo));
        }

        // Move uploaded file
        $file->move($uploadPath, $filename);

        // Save relative path to DB
        $photoPath = 'uploads/deposit/' . $filename;

        Log::info('Photo Uploaded Successfully', [
            'deposit_id' => $id,
            'filename' => $filename,
            'path' => $photoPath
        ]);

        // Assign to model
        $deposit->photo = $photoPath;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Photo Upload Error', [
            'error' => $e->getMessage(),
            'deposit_id' => $id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to upload photo: ' . $e->getMessage()
        ], 500);
    }
}


            // Update deposit record
            $deposit->transaction_id = $request->transaction_id;
            $deposit->sender_account = $request->sender_account;
            $deposit->photo = $photoPath;
            $deposit->status = 'user_submitted';

            if (!$deposit->save()) {
                DB::rollBack();
                Log::error('Failed to Save Deposit', ['deposit_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save deposit details'
                ], 500);
            }

            Log::info('Deposit Details Submitted Successfully', [
                'deposit_id' => $deposit->id,
                'user_id' => Auth::id(),
                'amount' => $deposit->amount,
                'transaction_id' => $deposit->transaction_id,
                'sender_account' => $deposit->sender_account,
                'photo' => $deposit->photo,
                'status' => $deposit->status
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit details submitted successfully! Waiting for final approval.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deposit Submit Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'deposit_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Withdraw Status (Polling - Step 2)
     * Route: GET user/withdraw/status
     */
    public function checkWithdrawStatus()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($withdraw) {
                Log::info('Withdraw Status Check - Agent Confirmed', [
                    'withdraw_id' => $withdraw->id,
                    'user_id' => Auth::id(),
                    'amount' => $withdraw->amount
                ]);

                return response()->json([
                    'status' => 'agent_confirmed',
                    'withdraw_id' => $withdraw->id,
                    'amount' => $withdraw->amount
                ]);
            }

            return response()->json(['status' => 'pending']);

        } catch (\Exception $e) {
            Log::error('Withdraw Status Check Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User Releases Withdraw Funds (Step 3)
     * Route: POST user/withdraw/submit/{id}
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Log::info('Withdraw Release Started', [
                'withdraw_id' => $id,
                'user_id' => Auth::id()
            ]);

            // Find and lock withdraw request
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw) {
                DB::rollBack();
                Log::error('Withdraw Not Found', ['withdraw_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Withdraw record not found'
                ], 404);
            }

            // Security check - verify ownership
            if ($withdraw->user_id !== Auth::id()) {
                DB::rollBack();
                Log::warning('Unauthorized Withdraw Access Attempt', [
                    'withdraw_id' => $id,
                    'actual_user' => $withdraw->user_id,
                    'attempted_user' => Auth::id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Status check - must be agent_confirmed
            if ($withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                Log::warning('Invalid Withdraw Status', [
                    'withdraw_id' => $id,
                    'current_status' => $withdraw->status,
                    'expected_status' => 'agent_confirmed'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Withdraw not confirmed by agent. Current status: ' . $withdraw->status
                ], 400);
            }

            // Prevent double-processing
            if ($withdraw->status === 'completed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This withdraw has already been completed'
                ], 400);
            }

            // Get and lock user record
            $user = User::lockForUpdate()->find($withdraw->user_id);

            if (!$user) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Check user balance again (double-check)
            if ($user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance. Your balance: ' . number_format($user->balance, 2) . ' USDT'
                ], 400);
            }

            // Get or create agent deposit record (with lock)
            $agentDeposit = AgentDeposite::lockForUpdate()
                ->firstOrCreate(
                    ['agent_id' => $withdraw->agent_id],
                    ['amount' => 0]
                );

            // Calculate commissions
            $agentCommission = 0;
            $adminCommission = 0;
            $totalCommission = 0;

            $commissionSetup = Agentcommissonsetup::where('status', 1)->first();
            if ($commissionSetup) {
                if ($commissionSetup->commission_type == 'percent') {
                    // Percentage-based commission
                    $totalCommission = ($withdraw->amount * $commissionSetup->withdraw_total_commission) / 100;
                } else {
                    // Fixed amount commission
                    $totalCommission = $commissionSetup->withdraw_total_commission;
                }

                // Split commission 50/50 between agent and admin
                $agentCommission = $totalCommission / 2;
                $adminCommission = $totalCommission / 2;
            }

            // Calculate net amount (amount after deducting total commission)
            $netAmount = $withdraw->amount - $totalCommission;

            // Update user balance (deduct full withdraw amount)
            $previousUserBalance = $user->balance;
            $user->balance -= $withdraw->amount;

            if (!$user->save()) {
                DB::rollBack();
                Log::error('Failed to Update User Balance', [
                    'user_id' => $user->id,
                    'withdraw_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user balance'
                ], 500);
            }

            // Update agent deposit (add net amount + agent's commission share)
            $previousAgentAmount = $agentDeposit->amount;
            $agentDeposit->amount += ($netAmount + $agentCommission);

            if (!$agentDeposit->save()) {
                DB::rollBack();
                Log::error('Failed to Update Agent Deposit', [
                    'agent_id' => $agentDeposit->agent_id,
                    'withdraw_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update agent deposit'
                ], 500);
            }

            // Update withdraw record
            $withdraw->status = 'completed';
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;

            if (!$withdraw->save()) {
                DB::rollBack();
                Log::error('Failed to Update Withdraw Status', ['withdraw_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update withdraw status'
                ], 500);
            }

            Log::info('Withdraw Completed Successfully', [
                'withdraw_id' => $withdraw->id,
                'amount' => $withdraw->amount,
                'user_previous_balance' => $previousUserBalance,
                'user_new_balance' => $user->balance,
                'user_deducted' => $withdraw->amount,
                'agent_previous_amount' => $previousAgentAmount,
                'agent_new_amount' => $agentDeposit->amount,
                'agent_received' => ($netAmount + $agentCommission),
                'agent_commission' => $agentCommission,
                'admin_commission' => $adminCommission,
                'total_commission' => $totalCommission,
                'net_amount' => $netAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdraw released successfully! Funds have been credited.',
                'new_balance' => number_format($user->balance, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw Release Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'withdraw_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to release withdraw: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agent Accepts Withdraw Request (Agent Side - Backend)
     * Route: POST agent/withdraw/accept/{id}
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();

        try {
            // Find and lock withdraw record
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw) {
                DB::rollBack();
                Log::error('Withdraw Not Found for Agent Accept', ['withdraw_id' => $id]);
                return back()->with('error', 'Withdraw request not found');
            }

            // Security check - verify agent ownership
            if ($withdraw->agent_id != Auth::id()) {
                DB::rollBack();
                Log::warning('Unauthorized Agent Accept Attempt', [
                    'withdraw_id' => $id,
                    'actual_agent' => $withdraw->agent_id,
                    'attempted_agent' => Auth::id()
                ]);
                return back()->with('error', 'Unauthorized access');
            }

            // Status check - must be pending
            if ($withdraw->status !== 'pending') {
                DB::rollBack();
                Log::warning('Invalid Status for Agent Accept', [
                    'withdraw_id' => $id,
                    'current_status' => $withdraw->status
                ]);
                return back()->with('error', 'This request has already been processed. Current status: ' . $withdraw->status);
            }

            // Update status to agent_confirmed
            $withdraw->status = 'agent_confirmed';

            if (!$withdraw->save()) {
                DB::rollBack();
                Log::error('Failed to Update Withdraw Status', ['withdraw_id' => $id]);
                return back()->with('error', 'Failed to update withdraw status');
            }

            Log::info('Withdraw Request Accepted by Agent', [
                'withdraw_id' => $id,
                'agent_id' => Auth::id(),
                'user_id' => $withdraw->user_id,
                'amount' => $withdraw->amount
            ]);

            DB::commit();

            return back()->with('success', 'Withdraw request accepted successfully. User can now release funds.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accept Withdraw Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'withdraw_id' => $id,
                'agent_id' => Auth::id()
            ]);
            return back()->with('error', 'Failed to accept request: ' . $e->getMessage());
        }
    }
}
