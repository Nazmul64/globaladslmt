<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\UserWidthraw;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymenthistoryiController extends Controller
{
    public function paymenthistory(Request $request)
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'history' => []
                ], 401);
            }

            Log::info('Fetching payment history for user: ' . $userId);

            // Deposit history
            $deposit_history = Deposite::where('user_id', $userId)
                ->select('id', 'amount', 'transaction_id', 'status', 'created_at', 'photo')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($item) => [
                    'type' => 'deposit',
                    'id' => $item->transaction_id ?? '#DEP-' . $item->id,
                    'amount' => (string) $item->amount,
                    'status' => strtolower($item->status ?? 'pending'),
                    'date' => $item->created_at->toDateString(),
                    'formatted_date' => $item->created_at->format('M d, Y'),
                    'time' => $item->created_at->format('h:i A'),
                    'photo' => $item->photo ?? null,
                ]);

            // Deposit requests
            $deposit_request_history = Userdepositerequest::where('user_id', $userId)
                ->select('id', 'amount', 'transaction_id', 'status', 'created_at', 'photo')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($item) => [
                    'type' => 'deposit_request',
                    'id' => $item->transaction_id ?? '#DEPREQ-' . $item->id,
                    'amount' => (string) $item->amount,
                    'status' => strtolower($item->status ?? 'pending'),
                    'date' => $item->created_at->toDateString(),
                    'formatted_date' => $item->created_at->format('M d, Y'),
                    'time' => $item->created_at->format('h:i A'),
                    'photo' => $item->photo ?? null,
                ]);

            // Withdraw history
            $withdraw_history = UserWidthraw::where('user_id', $userId)
                ->with('paymentMethod') // remove id,name selection
                ->select('id', 'amount', 'status', 'created_at', 'payment_method_id', 'account_number', 'wallet_address')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($item) => [
                    'type' => 'withdraw',
                    'id' => '#WD-' . $item->id,
                    'amount' => (string) $item->amount,
                    'status' => strtolower($item->status ?? 'pending'),
                    'date' => $item->created_at->toDateString(),
                    'formatted_date' => $item->created_at->format('M d, Y'),
                    'time' => $item->created_at->format('h:i A'),
                    'payment_method' => $item->paymentMethod->method_name ?? null, // adjust to your DB column
                    'account_number' => $item->account_number ?? null,
                    'wallet_address' => $item->wallet_address ?? null,
                    'photo' => null,
                ]);

            // Withdraw requests
            $withdraw_request_history = UserWidhrawrequest::where('user_id', $userId)
                ->select('id', 'amount', 'status', 'created_at', 'photo', 'transaction_id')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($item) => [
                    'type' => 'withdraw_request',
                    'id' => $item->transaction_id ?? '#WDREQ-' . $item->id,
                    'amount' => (string) $item->amount,
                    'status' => strtolower($item->status ?? 'pending'),
                    'date' => $item->created_at->toDateString(),
                    'formatted_date' => $item->created_at->format('M d, Y'),
                    'time' => $item->created_at->format('h:i A'),
                    'photo' => $item->photo ?? null,
                ]);

            $merged = $deposit_history
                ->merge($deposit_request_history)
                ->merge($withdraw_history)
                ->merge($withdraw_request_history)
                ->sortByDesc('date')
                ->values()
                ->toArray();

            Log::info('Payment history count: ' . count($merged));

            return response()->json([
                'success' => true,
                'message' => 'Payment history fetched successfully',
                'history' => $merged,
                'total' => count($merged)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Payment history error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history',
                'error' => $e->getMessage(),
                'history' => []
            ], 500);
        }
    }
}
