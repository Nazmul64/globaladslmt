<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Agentcommissonsetup;
use App\Models\AgentDeposite;
use App\Models\User;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * =====================================================================
 * 🎯 SIMPLIFIED DEPOSIT SYSTEM (A to Z)
 * =====================================================================
 *
 * ✅ ONLY userdepositerequest TABLE USED (NOT deposite table!)
 * ✅ users.balance NEVER TOUCHED
 * ✅ Money stored ONLY in: userdepositerequest.amount
 *
 * =====================================================================
 * DEPOSIT FLOW:
 * =====================================================================
 *
 * STEP 1: User creates request
 *   - Function: userwidhraw_request()
 *   - userdepositerequest: amount = 100, status = 'pending'
 *   - users.balance: UNCHANGED ❌
 *
 * STEP 2: Agent accepts
 *   - Function: acceptDepositRequest()
 *   - userdepositerequest: status = 'agent_confirmed'
 *   - users.balance: UNCHANGED ❌
 *
 * STEP 3: User submits payment proof
 *   - Function: userSubmitDeposit()
 *   - userdepositerequest: photo saved, status = 'user_submitted'
 *   - users.balance: UNCHANGED ❌
 *
 * STEP 4: Admin approves (FINAL)
 *   - Function: adminApproveDeposit()
 *   - userdepositerequest: commissions calculated, status = 'completed'
 *   - agent_deposites: agent commission added
 *   - users.balance: STILL UNCHANGED ❌
 *
 * 💰 FINAL RESULT: Money in userdepositerequest.amount ONLY
 * =====================================================================
 */

class UserDepositewidthrawrequestController extends Controller
{
    /**
     * =====================================================================
     * STEP 1: USER CREATES DEPOSIT/WITHDRAW REQUEST
     * =====================================================================
     *
     * Deposit:
     * ✅ Creates userdepositerequest (status = 'pending', amount = X)
     * ❌ Does NOT touch users.balance
     *
     * Withdraw:
     * ✅ Creates userwidhrawrequest (status = 'pending')
     * ✅ Checks balance but does NOT deduct yet
     */
    public function userwidhraw_request(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation
            $rules = [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01'
            ];

            if ($request->type === 'withdraw') {
                $rules['sender_account'] = 'required|string|max:500';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user = User::lockForUpdate()->find(Auth::id());

            // Withdrawal block check (Direct & P2P USDT Sell Order)
            if ($request->type === 'withdraw' && ($user->is_blocked ?? false)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'status' => false,
                    'message' => 'Your withdrawal is currently blocked by administration. You cannot place P2P USDT sell orders.'
                ], 403);
            }

            // Withdraw balance check
            if ($request->type === 'withdraw' && $user->balance < $request->amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance: ' . number_format($user->balance, 2) . ' USDT'
                ], 400);
            }

            // Trade limits check
            $post = Agentbuysellpost::find($request->post_id);

            if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"
                ], 400);
            }

            // Check pending requests
            if ($request->type === 'deposit') {
                $pending = Userdepositerequest::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])
                    ->first();
            } else {
                $pending = UserWidhrawrequest::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'agent_confirmed'])
                    ->first();
            }

            if ($pending) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "You already have a pending {$request->type} request"
                ], 400);
            }

            // Create request
            if ($request->type === 'withdraw') {
                $record = UserWidhrawrequest::create([
                    'user_id' => $user->id,
                    'agent_id' => $request->agent_id,
                    'amount' => $request->amount,
                    'status' => 'pending',
                    'sender_account' => $request->sender_account,
                    'transaction_id' => $request->transaction_id ?? null,
                    'agent_commission' => 0,
                    'admin_commission' => 0
                ]);
            } else {
                // DEPOSIT: Only in userdepositerequest table
                $record = Userdepositerequest::create([
                    'user_id' => $user->id,
                    'agent_id' => $request->agent_id,
                    'post_id' => $request->post_id,
                    'amount' => $request->amount, // 💰 Money stored HERE only!
                    'status' => 'pending',
                    'type' => 'deposit',
                    'agent_commission' => 0,
                    'admin_commission' => 0
                ]);
            }

            DB::commit();

            Log::info("✅ {$request->type} Request Created", [
                'request_id' => $record->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'table' => $request->type === 'deposit' ? 'userdepositerequest' : 'userwidhrawrequest',
                'user_balance' => $user->balance . ' (unchanged)'
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . ' request sent successfully',
                'request_id' => $record->id,
                'amount' => $request->amount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Request Creation Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================================
     * STEP 2: AGENT ACCEPTS DEPOSIT REQUEST
     * =====================================================================
     *
     * ✅ Updates userdepositerequest status to 'agent_confirmed'
     * ❌ Does NOT create deposite table record
     * ❌ Does NOT touch users.balance
     *
     * 💰 Money stays in: userdepositerequest.amount
     */
    public function acceptDepositRequest($id)
    {
        DB::beginTransaction();

        try {
            $depositRequest = Userdepositerequest::lockForUpdate()->find($id);

            if (!$depositRequest) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit request not found'
                ], 404);
            }

            if ($depositRequest->agent_id != Auth::id()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($depositRequest->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Request is not in pending status'
                ], 400);
            }

            // Simply update status (NO deposite table involved!)
            $depositRequest->status = 'agent_confirmed';
            $depositRequest->save();

            DB::commit();

            Log::info('✅ Agent Accepted Deposit', [
                'deposit_id' => $id,
                'amount_in_userdepositerequest' => $depositRequest->amount,
                'status' => 'agent_confirmed',
                'note' => 'Money stored ONLY in userdepositerequest table'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit request accepted successfully',
                'deposit_id' => $depositRequest->id,
                'amount' => $depositRequest->amount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Accept Deposit Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================================
     * STEP 3: USER SUBMITS PAYMENT PROOF
     * =====================================================================
     *
     * ✅ Uploads photo, saves transaction details
     * ✅ Updates userdepositerequest status to 'user_submitted'
     * ❌ Does NOT touch users.balance
     *
     * 💰 Money still in: userdepositerequest.amount
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()->find($id);

            if (!$deposit) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit request not found'
                ], 404);
            }

            if ($deposit->user_id != Auth::id()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit is not in agent_confirmed status'
                ], 400);
            }

            // Upload photo
            $path = 'uploads/deposit/';
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0755, true);
            }

            $fileName = "deposit_" . time() . "_" . uniqid() . "." . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path($path), $fileName);

            // Save payment proof
            $deposit->transaction_id = trim($request->transaction_id);
            $deposit->sender_account = trim($request->sender_account);
            $deposit->photo = $path . $fileName;
            $deposit->status = 'user_submitted';
            $deposit->save();

            DB::commit();

            Log::info('✅ Payment Proof Submitted', [
                'deposit_id' => $id,
                'transaction_id' => $deposit->transaction_id,
                'amount_in_userdepositerequest' => $deposit->amount,
                'note' => 'Money still ONLY in userdepositerequest table'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof submitted successfully',
                'deposit_id' => $deposit->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Payment Proof Submit Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit payment proof: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================================
     * STEP 4: ADMIN APPROVES DEPOSIT (FINAL STEP)
     * =====================================================================
     *
     * ✅ Calculates commissions
     * ✅ Updates userdepositerequest with commissions
     * ✅ Adds agent commission to agent_deposites
     * ✅ Updates status to 'completed'
     * ❌ Does NOT touch users.balance
     * ❌ Does NOT use deposite table
     *
     * 💰 FINAL: Money remains in userdepositerequest.amount ONLY!
     */
    public function adminApproveDeposit($id)
    {
        DB::beginTransaction();

        try {
            $depositRequest = Userdepositerequest::lockForUpdate()->find($id);

            if (!$depositRequest) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit request not found'
                ], 404);
            }

            if ($depositRequest->status !== 'user_submitted') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit is not in user_submitted status'
                ], 400);
            }

            $user = User::lockForUpdate()->find($depositRequest->user_id);
            $userBalanceBefore = $user->balance;

            // Calculate commissions
            $commission = Agentcommissonsetup::where('status', 1)->first();
            $agentCommission = $adminCommission = 0;

            if ($commission) {
                $totalCommission = $commission->commission_type === 'percent'
                    ? ($depositRequest->amount * $commission->deposit_total_commission) / 100
                    : $commission->deposit_total_commission;

                $agentCommission = $adminCommission = $totalCommission / 2;
            }

            // Calculate net amount (for information only)
            $netAmount = $depositRequest->amount - ($agentCommission + $adminCommission);

            // Add agent commission
            $agentDepo = AgentDeposite::lockForUpdate()
                ->firstOrCreate(['agent_id' => $depositRequest->agent_id], ['amount' => 0, 'status' => 'approved']);

            $agentDepo->amount += $agentCommission;
            $agentDepo->save();

            // Update request record with commissions and complete
            $depositRequest->agent_commission = $agentCommission;
            $depositRequest->admin_commission = $adminCommission;
            $depositRequest->status = 'completed';
            $depositRequest->save();

            // Verify user balance unchanged
            $user->refresh();
            $userBalanceAfter = $user->balance;

            if ($userBalanceBefore != $userBalanceAfter) {
                Log::error('🚨 CRITICAL: User balance changed during admin approval!', [
                    'deposit_id' => $id,
                    'user_id' => $user->id,
                    'balance_before' => $userBalanceBefore,
                    'balance_after' => $userBalanceAfter,
                    'BUG_ALERT' => 'Users balance should NEVER change!'
                ]);
            }

            DB::commit();

            Log::info('✅ Admin Approved Deposit - COMPLETED', [
                'deposit_id' => $id,
                'original_amount' => $depositRequest->amount,
                'net_amount' => $netAmount,
                'agent_commission' => $agentCommission,
                'admin_commission' => $adminCommission,
                'stored_in' => 'userdepositerequest.amount ONLY',
                'user_balance' => $user->balance . ' (unchanged)',
                'note' => 'NO deposite table used!'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit approved successfully',
                'deposit_id' => $depositRequest->id,
                'original_amount' => $depositRequest->amount,
                'net_amount' => $netAmount,
                'agent_commission' => $agentCommission,
                'admin_commission' => $adminCommission
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Admin Approve Deposit Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================================
     * AGENT ACCEPTS WITHDRAW REQUEST
     * =====================================================================
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();

        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Withdraw request not found'
                ], 404);
            }

            if ($withdraw->agent_id != Auth::id()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($withdraw->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Request is not in pending status'
                ], 400);
            }

            $withdraw->status = 'agent_confirmed';
            $withdraw->save();

            DB::commit();

            Log::info('✅ Agent Accepted Withdraw', [
                'withdraw_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdraw request accepted successfully',
                'withdraw_id' => $withdraw->id,
                'amount' => $withdraw->amount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Accept Withdraw Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================================
     * USER COMPLETES WITHDRAW
     * =====================================================================
     *
     * ✅ Deducts amount from users.balance (ONLY for withdraw!)
     * ✅ Adds net amount + commission to agent
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Withdraw request not found'
                ], 404);
            }

            if ($withdraw->user_id != Auth::id()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            if ($withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Withdraw is not confirmed by agent'
                ], 400);
            }

            $user = User::lockForUpdate()->find($withdraw->user_id);

            if ($user->balance < $withdraw->amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance: ' . number_format($user->balance, 2) . ' USDT'
                ], 400);
            }

            // Calculate commissions
            $commission = Agentcommissonsetup::where('status', 1)->first();
            $agentCommission = $adminCommission = 0;

            if ($commission) {
                $totalCommission = $commission->commission_type === 'percent'
                    ? ($withdraw->amount * $commission->withdraw_total_commission) / 100
                    : $commission->withdraw_total_commission;

                $agentCommission = $adminCommission = $totalCommission / 2;
            }

            $netAmount = $withdraw->amount - ($agentCommission + $adminCommission);
            $balanceBefore = $user->balance;

            // Deduct from user balance (ONLY for withdraw!)
            $user->balance -= $withdraw->amount;
            $user->save();

            // Add to agent
            $agentDepo = AgentDeposite::lockForUpdate()
                ->firstOrCreate(['agent_id' => $withdraw->agent_id], ['amount' => 0, 'status' => 'approved']);

            $agentDepo->amount += ($netAmount + $agentCommission);
            $agentDepo->save();

            // Update withdraw record
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->status = 'completed';
            $withdraw->save();

            DB::commit();

            Log::info('✅ Withdraw Completed', [
                'withdraw_id' => $id,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'amount_deducted' => $withdraw->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdraw completed successfully',
                'new_balance' => number_format($user->balance, 2),
                'amount' => $withdraw->amount,
                'net_amount' => $netAmount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Withdraw Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete withdraw: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHECK DEPOSIT STATUS
     */
    public function checkDepositStatus()
    {
        try {
            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])
                ->latest()
                ->first();

            if (!$deposit) {
                return response()->json([
                    'success' => true,
                    'status' => null,
                    'message' => 'No pending deposit found'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => $deposit->status,
                'deposit_id' => $deposit->id,
                'amount' => $deposit->amount
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Deposit Status Check Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status'
            ], 500);
        }
    }

    /**
     * CHECK WITHDRAW STATUS
     */
    public function checkWithdrawStatus()
    {
        try {
            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'agent_confirmed'])
                ->latest()
                ->first();

            if (!$withdraw) {
                return response()->json([
                    'success' => true,
                    'status' => null,
                    'message' => 'No pending withdraw found'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => $withdraw->status,
                'withdraw_id' => $withdraw->id,
                'amount' => $withdraw->amount
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Withdraw Status Check Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to check status'
            ], 500);
        }
    }

    /**
     * CANCEL DEPOSIT REQUEST
     */
    public function depositecancled(Request $request)
    {
        DB::beginTransaction();

        try {
            $depositId = $request->input('deposit_id');

            if (!$depositId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit ID is required'
                ], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()
                ->where('id', $depositId)
                ->where('user_id', Auth::id())
                ->first();

            if (!$deposit) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit request not found'
                ], 404);
            }

            if ($deposit->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel - deposit is already ' . $deposit->status
                ], 400);
            }

            $deposit->status = 'cancelled';
            $deposit->save();

            DB::commit();

            Log::info('✅ Deposit Cancelled', [
                'deposit_id' => $depositId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit cancelled successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Cancel Deposit Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET DEPOSIT STATUS (DETAILED)
     */
    public function depositStatus(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $deposit = Userdepositerequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'agent_confirmed'])
                ->latest()
                ->first();

            if (!$deposit) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending deposit found',
                    'status' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deposit status retrieved',
                'status' => $deposit->status,
                'deposit_id' => $deposit->id,
                'amount' => $deposit->amount,
                'can_cancel' => $deposit->status === 'pending'
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Deposit Status Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check status'
            ], 500);
        }
    }
}
