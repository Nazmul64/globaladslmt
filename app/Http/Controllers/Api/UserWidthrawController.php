<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Paymentmethod;
use App\Models\Widthrawlimit;
use App\Models\Userwidthraw;
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

            if (!$user) {
                Log::error('Unauthenticated user trying to access withdraw page');
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            Log::info('User accessing withdraw page', ['user_id' => $user->id]);

            // Active payment methods
            $paymentMethods = Paymentmethod::select('id', 'method_name', 'method_number', 'photo', 'status')
                ->where('status', 'active')
                ->get();

            Log::info('Payment methods fetched', ['count' => $paymentMethods->count()]);

            // Withdraw limits
            $withdrawLimit = Widthrawlimit::first();

            if (!$withdrawLimit) {
                Log::error('Withdraw limits not found in database');
                return $this->sendError('Withdraw limits not configured.', [], 500);
            }

            // Total balance of all users
            $totalUserBalance = DB::table('users')->sum('balance');

            $data = [
                'user' => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'balance' => (float) $user->balance,
                ],
                'total_user_balance' => (float) $totalUserBalance,
                'payment_methods' => $paymentMethods,
                'withdraw_limit'  => [
                    'min_withdraw_limit' => (float) $withdrawLimit->min_withdraw_limit,
                    'max_withdraw_limit' => (float) $withdrawLimit->max_withdraw_limit,
                ],
            ];

            Log::info('Withdraw data prepared successfully', [
                'user_id' => $user->id,
                'balance' => $user->balance,
                'payment_methods_count' => $paymentMethods->count(),
                'total_user_balance' => $totalUserBalance
            ]);

            return $this->sendResponse($data, 'User withdraw data fetched successfully.');

        } catch (\Exception $e) {
            Log::error('Error in userwidthrawshow', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Server error occurred.', [], 500);
        }
    }

    /**
     * Store withdraw request
     * 👉 এখানে IMMEDIATELY balance কেটে নেওয়া হবে
     */
    public function userwidthrawstore(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::error('Unauthenticated user trying to submit withdraw');
                return $this->sendError('Unauthenticated user.', [], 401);
            }

            Log::info('Withdraw request received', [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            // Validation
            $validator = Validator::make($request->all(), [
                'payment_method_id' => 'required|exists:paymentmethods,id',
                'account_number'    => 'required|string|max:255',
                'wallet_address'    => 'nullable|string|max:255',
                'amount'            => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                Log::warning('Withdraw validation failed', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()
                ]);
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $amount = (float) $request->amount;

            // Get withdraw limits
            $limit = Widthrawlimit::first();

            if (!$limit) {
                Log::error('Withdraw limits not configured');
                return $this->sendError('Withdraw limits not configured.', [], 500);
            }

            // Check amount limits
            if ($amount < $limit->min_withdraw_limit) {
                Log::warning('Amount below minimum limit', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'min_limit' => $limit->min_withdraw_limit
                ]);
                return $this->sendError(
                    "Minimum withdraw amount is {$limit->min_withdraw_limit}",
                    [],
                    422
                );
            }

            if ($amount > $limit->max_withdraw_limit) {
                Log::warning('Amount exceeds maximum limit', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'max_limit' => $limit->max_withdraw_limit
                ]);
                return $this->sendError(
                    "Maximum withdraw amount is {$limit->max_withdraw_limit}",
                    [],
                    422
                );
            }

            // 👉 Refresh user balance before checking
            $user->refresh();

            // Check user balance
            if ($amount > $user->balance) {
                Log::warning('Insufficient balance', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'balance' => $user->balance
                ]);
                return $this->sendError('Insufficient balance for this withdraw!', [], 422);
            }

            // Verify payment method
            $paymentMethod = Paymentmethod::where('id', $request->payment_method_id)
                ->where('status', 'active')
                ->first();

            if (!$paymentMethod) {
                Log::warning('Invalid payment method', [
                    'user_id' => $user->id,
                    'payment_method_id' => $request->payment_method_id
                ]);
                return $this->sendError('Invalid or inactive payment method.', [], 422);
            }

            // 👉 Database Transaction দিয়ে safely balance কেটে নিবো
            DB::beginTransaction();

            try {
                // Create withdraw request
                $withdraw = Userwidthraw::create([
                    'user_id'            => $user->id,
                    'payment_method_id'  => $request->payment_method_id,
                    'account_number'     => $request->account_number,
                    'wallet_address'     => $request->wallet_address,
                    'amount'             => $amount,
                    'status'             => 'pending',
                ]);

                // 👉 IMMEDIATELY balance থেকে amount কেটে নিবো
                $user->balance -= $amount;
                $user->save();

                DB::commit();

                Log::info('Withdraw request created and balance deducted successfully', [
                    'withdraw_id' => $withdraw->id,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'old_balance' => $user->balance + $amount,
                    'new_balance' => $user->balance,
                    'payment_method' => $paymentMethod->method_name
                ]);

                return $this->sendResponse(
                    [
                        'withdraw' => [
                            'id'     => $withdraw->id,
                            'amount' => $withdraw->amount,
                            'status' => $withdraw->status,
                            'payment_method' => $paymentMethod->method_name,
                        ],
                        'new_balance' => (float) $user->balance, // 👉 নতুন balance return করবো
                    ],
                    'Withdraw request submitted successfully. Amount deducted from your balance.'
                );

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error creating withdraw request', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);

                return $this->sendError('Failed to process withdraw request.', [], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error in userwidthrawstore', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Server error occurred while processing withdraw.', [], 500);
        }
    }



}
