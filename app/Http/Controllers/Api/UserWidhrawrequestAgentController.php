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

class UserWidhrawrequestAgentController extends Controller
{
    /**
     * Create deposit or withdraw request
     */
    public function userwidhraw_request(Request $request)
    {
        $userId = Auth::id();

        $rules = [
            'type'      => 'required|in:deposit,withdraw',
            'agent_id'  => 'required|exists:users,id',
            'post_id'   => 'required|exists:agentbuysellposts,id',
            'amount'    => 'required|numeric|min:0.01',
        ];

        if ($request->type === 'withdraw') {
            $rules['sender_account'] = 'required|string|max:500';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find($userId);

            if (!$user) {
                DB::rollBack();
                return $this->error("User not found.", 404);
            }

            // Withdraw: Check User Balance
            if ($request->type === 'withdraw' && $user->balance < $request->amount) {
                DB::rollBack();
                return $this->error("Insufficient balance.", 400);
            }

            // Validate Trade Limit
            $post = Agentbuysellpost::find($request->post_id);
            if (!$post || $request->amount < $post->trade_limit || $request->amount > $post->trade_limit_two) {
                DB::rollBack();
                return $this->error("Amount must be between {$post->trade_limit} - {$post->trade_limit_two} USDT", 400);
            }

            // Check Duplicate Pending Requests
            $pending = ($request->type === 'deposit')
                ? Userdepositerequest::where('user_id', $userId)->whereIn('status', ['pending', 'agent_confirmed', 'user_submitted'])->exists()
                : UserWidhrawrequest::where('user_id', $userId)->whereIn('status', ['pending', 'agent_confirmed'])->exists();

            if ($pending) {
                DB::rollBack();
                return $this->error("Existing pending {$request->type} request found.", 400);
            }

            // Prepare Insert Data
            $data = [
                'user_id'          => $userId,
                'agent_id'         => $request->agent_id,
                'amount'           => $request->amount,
                'status'           => 'pending',
                'agent_commission' => 0,
                'admin_commission' => 0,
            ];

            if ($request->type === 'deposit') {
                $record = Userdepositerequest::create(array_merge($data, ['type' => 'deposit', 'photo' => null]));
            } else {
                $record = UserWidhrawrequest::create(array_merge($data, [
                    'sender_account' => trim($request->sender_account),
                    'transaction_id' => $request->transaction_id ?? null,
                ]));
            }

            DB::commit();
            Log::info(strtoupper($request->type) . " Request Created", ['id' => $record->id]);

            return $this->success(ucfirst($request->type) . " request created successfully.", ['request_id' => $record->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Create Request Error", ['error' => $e->getMessage()]);
            return $this->error("Something went wrong.", 500);
        }
    }


    /**
     * Deposit Polling Status
     */
    public function checkDepositStatus()
    {
        try {
            $deposit = Userdepositerequest::where('user_id', Auth::id())
                ->whereIn('status', ['agent_confirmed', 'user_submitted'])
                ->latest()
                ->first();

            if ($deposit) {
                return $this->success('update', [
                    'status'     => $deposit->status,
                    'deposit_id' => $deposit->id,
                    'amount'     => $deposit->amount
                ]);
            }

            return $this->success('no_update');
        } catch (\Exception $e) {
            Log::error("Deposit Status Error", ['error' => $e->getMessage()]);
            return $this->error("Failed to check status.", 500);
        }
    }


    /**
     * Withdraw Polling Status
     */
    public function checkWithdrawStatus()
    {
        try {
            $withdraw = UserWidhrawrequest::where('user_id', Auth::id())
                ->where('status', 'agent_confirmed')
                ->latest()
                ->first();

            if ($withdraw) {
                return $this->success('update', [
                    'status'      => 'agent_confirmed',
                    'withdraw_id' => $withdraw->id,
                    'amount'      => $withdraw->amount
                ]);
            }

            return $this->success('no_update');
        } catch (\Exception $e) {
            Log::error("Withdraw Status Error", ['error' => $e->getMessage()]);
            return $this->error("Failed to check status.", 500);
        }
    }


    /**
     * Submit Deposit Proof
     */
    public function userSubmitDeposit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string|max:255',
            'sender_account' => 'required|string|max:255',
            'photo'          => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try {

            $deposit = Userdepositerequest::lockForUpdate()->find($id);

            if (!$deposit || $deposit->user_id != Auth::id() || $deposit->status !== 'agent_confirmed') {
                DB::rollBack();
                return $this->error("Invalid deposit request.", 400);
            }

            // Upload Image
            $path = 'uploads/deposits/';
            if (!is_dir(public_path($path))) mkdir(public_path($path), 0775, true);

            $fileName = uniqid('deposit_') . '.' . $request->photo->extension();
            $request->photo->move(public_path($path), $fileName);

            $deposit->update([
                'photo'           => $path . $fileName,
                'transaction_id'  => trim($request->transaction_id),
                'sender_account'  => trim($request->sender_account),
                'status'          => 'user_submitted'
            ]);

            DB::commit();
            return $this->success("Deposit details submitted.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Deposit Submit Error", ['error' => $e->getMessage()]);
            return $this->error("Submission failed.", 500);
        }
    }


    /**
     * Complete Withdraw
     */
    public function userSubmitWithdraw(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw || $withdraw->user_id != Auth::id() || $withdraw->status !== 'agent_confirmed') {
                DB::rollBack();
                return $this->error("Invalid withdraw request.", 400);
            }

            $user = User::lockForUpdate()->find($withdraw->user_id);

            if ($user->balance < $withdraw->amount) {
                DB::rollBack();
                return $this->error("Insufficient balance.", 400);
            }

            // Commission
            [$agentCommission, $adminCommission, $netAmount] = $this->calculateWithdrawCommission($withdraw->amount);

            $user->decrement('balance', $withdraw->amount);

            $agentWallet = AgentDeposite::lockForUpdate()->firstOrCreate(
                ['agent_id' => $withdraw->agent_id],
                ['amount' => 0]
            );

            $agentWallet->increment('amount', $netAmount + $agentCommission);

            $withdraw->update([
                'agent_commission' => $agentCommission,
                'admin_commission' => $adminCommission,
                'status'           => 'completed'
            ]);

            DB::commit();
            return $this->success("Withdraw completed successfully!", [
                'new_balance' => number_format($user->balance, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Withdraw Submit Error", ['error' => $e->getMessage()]);
            return $this->error("Failed to complete withdraw.", 500);
        }
    }


    /**
     * Agent Accept Withdraw
     */
    public function acceptWithdrawRequest($id)
    {
        DB::beginTransaction();
        try {
            $withdraw = UserWidhrawrequest::lockForUpdate()->find($id);

            if (!$withdraw || $withdraw->agent_id != Auth::id() || $withdraw->status !== 'pending') {
                DB::rollBack();
                return $this->error("Invalid or unauthorized request.", 400);
            }

            $withdraw->update(['status' => 'agent_confirmed']);

            DB::commit();
            return $this->success("Withdraw request accepted.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Withdraw Accept Error", ['error' => $e->getMessage()]);
            return $this->error("Failed to accept request.", 500);
        }
    }


    /* ================= Helper Methods ================= */

    private function calculateWithdrawCommission($amount)
    {
        $config = Agentcommissonsetup::where('status', 1)->first();
        if (!$config) return [0, 0, $amount];

        $total = ($config->commission_type === 'percent')
            ? ($amount * $config->withdraw_total_commission) / 100
            : $config->withdraw_total_commission;

        return [($total / 2), ($total / 2), ($amount - $total)];
    }

    private function success($message, $data = [], $code = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function error($message, $code = 400)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
