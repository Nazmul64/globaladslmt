<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agentbuysellpost;
use App\Models\Agentcommissonsetup;
use App\Models\AgentDeposite;
use App\Models\Category;
use App\Models\User;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserDepositewidthrawrequestController extends Controller
{
    /**
     * Get all approved buy/sell posts with categories
     */


    /**
     * Create deposit or withdraw request
     */
    public function userwidhraw_request(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation rules
            $rules = [
                'type' => 'required|in:deposit,withdraw',
                'agent_id' => 'required|exists:users,id',
                'post_id' => 'required|exists:agentbuysellposts,id',
                'amount' => 'required|numeric|min:0.01'
            ];

            // Add conditional rules for withdraw
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

            // Lock user record
            $user = User::lockForUpdate()->find(Auth::id());

            // Check balance for withdraw
            if ($request->type === 'withdraw' && $user->balance < $request->amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance: ' . number_format($user->balance, 2) . ' USDT'
                ], 400);
            }

            // Validate trade limits
            $post = Agentbuysellpost::find($request->post_id);

            if ($request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Amount must be between {$post->trade_limit} and {$post->trade_limit_two} USDT"
                ], 400);
            }

            // Check for existing pending requests
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

            // Prepare common data
            $data = [
                'user_id' => $user->id,
                'agent_id' => $request->agent_id,
                'amount' => $request->amount,
                'status' => 'pending',
                'agent_commission' => 0,
                'admin_commission' => 0
            ];

            // Create request based on type
            if ($request->type === 'withdraw') {
                $data['sender_account'] = $request->sender_account;
                $data['transaction_id'] = $request->transaction_id ?? null;
                $record = UserWidhrawrequest::create($data);
            } else {
                $data['type'] = 'deposit';
                $data['photo'] = null;
                $record = Userdepositerequest::create($data);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . ' request sent successfully',
                'request_id' => $record->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create request'
            ], 500);
        }
    }

    /**
     * Check deposit status for polling
     */
    public function checkDepositStatus()
    {
        try {
            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->whereIn('status', ['agent_confirmed', 'user_submitted'])
                ->latest()
                ->first();

            if ($deposit && $deposit->status === 'agent_confirmed') {
                return response()->json([
                    'status' => 'agent_confirmed',
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount
                ], 200);
            }

            return response()->json([
                'status' => 'pending'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Deposit Status Check Error', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Check withdraw status for polling
     */
    public function checkWithdrawStatus()
    {
        try {
            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($withdraw) {
                return response()->json([
                    'status' => 'agent_confirmed',
                    'withdraw_id' => $withdraw->id,
                    'amount' => $withdraw->amount
                ], 200);
            }

            return response()->json([
                'status' => 'pending'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Withdraw Status Check Error', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * User submits deposit details with payment proof
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'transaction_id' => 'required|string|max:255',
                'sender_account' => 'required|string|max:255',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $deposit = Userdepositerequest::lockForUpdate()->find($id);

            if (!$deposit || $deposit->user_id != Auth::id() || $deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid deposit request'
                ], 400);
            }

            // Upload photo
            $path = 'uploads/deposit/';
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path), 0755, true);
            }

            $fileName = "deposit_" . time() . "_" . uniqid() . "." . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move(public_path($path), $fileName);

            // Update deposit
            $deposit->transaction_id = trim($request->transaction_id);
            $deposit->sender_account = trim($request->sender_account);
            $deposit->photo = $path . $fileName;
            $deposit->status = 'user_submitted';
            $deposit->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit details submitted successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Deposit Submit Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit deposit'
            ], 500);
        }
    }

    /**
     * User releases withdraw funds
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw || $withdraw->user_id != Auth::id() || $withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid withdraw request'
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

            // Deduct from user balance
            $user->balance -= $withdraw->amount;
            $user->save();

            // Add to agent deposit
            $agentDepo = AgentDeposite::lockForUpdate()
                ->firstOrCreate(['agent_id' => $withdraw->agent_id], ['amount' => 0]);

            $agentDepo->amount += ($netAmount + $agentCommission);
            $agentDepo->save();

            // Update withdraw record
            $withdraw->agent_commission = $agentCommission;
            $withdraw->admin_commission = $adminCommission;
            $withdraw->status = 'completed';
            $withdraw->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdraw completed successfully',
                'new_balance' => number_format($user->balance, 2)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw Release Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete withdraw'
            ], 500);
        }
    }

    /**
     * Agent accepts withdraw request
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();

        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw || $withdraw->agent_id != Auth::id() || $withdraw->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unauthorized request'
                ], 400);
            }

            $withdraw->status = 'agent_confirmed';
            $withdraw->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdraw request accepted'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Accept Withdraw Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept request'
            ], 500);
        }
    }
}
