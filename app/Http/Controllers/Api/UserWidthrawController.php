<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Paymentmethod;
use App\Models\Widthrawlimit;
use App\Models\UserWidthraw;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserWidthrawController extends BaseController
{
    /**
     * Show withdraw page data
     */

    public function userwidthrawshow(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return $this->sendError('Unauthenticated user.', [], 401);

            $paymentMethods = Paymentmethod::select('id','method_name','method_number','photo','status')
                ->where('status','active')
                ->get();

            $withdrawLimit = Widthrawlimit::first();
            if (!$withdrawLimit) return $this->sendError('Withdraw limits not configured.', [], 500);

            $totalUserBalance = DB::table('users')->sum('balance');

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'balance' => (float) $user->balance,
                    'is_blocked' => (bool) $user->is_blocked,
                    'is_withdraw_blocked' => (bool) $user->is_blocked,
                ],
                'is_withdraw_blocked' => (bool) $user->is_blocked,
                'total_user_balance' => (float) $totalUserBalance,
                'payment_methods' => $paymentMethods,
                'withdraw_limit' => [
                    'min_withdraw_limit' => (float)$withdrawLimit->min_withdraw_limit,
                    'max_withdraw_limit' => (float)$withdrawLimit->max_withdraw_limit,
                ],
            ];

            return $this->sendResponse($data, 'User withdraw data fetched successfully.');
        } catch (\Exception $e) {
            Log::error('Error in userwidthrawshow', ['error'=>$e->getMessage()]);
            return $this->sendError('Server error occurred.', [], 500);
        }
    }

    /**
     * Store withdraw request
     * 👉 Immediately deducts balance
     */
    public function userwidthrawstore(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return $this->sendError('Unauthenticated user.', [], 401);

            if ($user->is_blocked) {
                return $this->sendError('Your withdrawal has been blocked by administration. Please contact support.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'payment_method_id' => 'required|exists:paymentmethods,id',
                'account_number'    => 'required|string|max:255',
                'wallet_address'    => 'nullable|string|max:255',
                'amount'            => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $amount = (float) $request->amount;
            $limit = Widthrawlimit::first();
            if (!$limit) return $this->sendError('Withdraw limits not configured.', [], 500);

            if ($amount < $limit->min_withdraw_limit) return $this->sendError("Minimum withdraw amount is {$limit->min_withdraw_limit}", [], 422);
            if ($amount > $limit->max_withdraw_limit) return $this->sendError("Maximum withdraw amount is {$limit->max_withdraw_limit}", [], 422);

            $user->refresh();
            if ($amount > $user->balance) return $this->sendError('Insufficient balance for this withdraw!', [], 422);

            $paymentMethod = Paymentmethod::where('id', $request->payment_method_id)
                ->where('status','active')
                ->first();

            if (!$paymentMethod) return $this->sendError('Invalid or inactive payment method.', [], 422);

            DB::beginTransaction();
            try {
                $withdraw = UserWidthraw::create([
                    'user_id' => $user->id,
                    'payment_method_id' => $request->payment_method_id,
                    'account_number' => $request->account_number,
                    'wallet_address' => $request->wallet_address,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                // Immediately deduct balance
                $user->balance -= $amount;
                $user->save();

                DB::commit();

                PushNotificationService::send(
                    $user->id,
                    "উইথড্র রিকোয়েস্ট জমা হয়েছে",
                    "আপনার {$amount} টাকার উইথড্র রিকোয়েস্ট সফলভাবে জমা হয়েছে।",
                    "withdraw",
                    ['withdraw_id' => $withdraw->id, 'amount' => $amount]
                );

                return $this->sendResponse([
                    'withdraw' => [
                        'id' => $withdraw->id,
                        'amount' => $withdraw->amount,
                        'status' => $withdraw->status,
                        'payment_method' => $paymentMethod->method_name,
                    ],
                    'new_balance' => (float)$user->balance,
                ], 'Withdraw request submitted successfully. Amount deducted from your balance.');

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->sendError('Failed to process withdraw request.', [], 500);
            }
        } catch (\Exception $e) {
            return $this->sendError('Server error occurred while processing withdraw.', [], 500);
        }
    }

    /**
     * User withdraw history
     */
    public function withdrawHistory(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return $this->sendError('Unauthenticated user.', [], 401);

            $withdraws = UserWidthraw::with('payment_name')
                ->where('user_id',$user->id)
                ->orderBy('created_at','desc')
                ->get()
                ->map(fn($w) => [
                    'id'=>$w->id,
                    'amount'=>(float)$w->amount,
                    'commission'=>(float)($w->commission ?? 0),
                    'status'=>$w->status,
                    'payment_method'=>$w->payment_name->method_name ?? null,
                    'account_number'=>$w->account_number,
                    'wallet_address'=>$w->wallet_address,
                    'requested_at'=>$w->created_at->toDateTimeString(),
                    'updated_at'=>$w->updated_at->toDateTimeString(),
                ]);

            return $this->sendResponse(['withdraw_history'=>$withdraws],'Withdraw history fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Server error occurred while fetching withdraw history.', [], 500);
        }
    }
}
