<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\UserWidthraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWidthrawController extends Controller
{
public function user_deposite_manual(Request $request)
{
    $user = Auth::user();

    // Get withdraw limit (assuming only one row in widthrawlimits table)
    $limit = \App\Models\Widthrawlimit::first();

    // Validation
    $request->validate([
        'payment_method_id' => 'required|exists:paymentmethods,id',
        'account_number'    => 'required|string|max:255',
        'wallet_address'    => 'nullable|string|max:255',
        'amount'            => 'required|numeric|min:1',
    ]);

    // Check user balance
    if ($request->amount > $user->balance) {
        return redirect()->back()->with('error', 'Insufficient balance for this withdraw!');
    }

    // Check withdraw limits
    if ($limit) {
        if ($request->amount < $limit->min_withdraw_limit || $request->amount > $limit->max_withdraw_limit) {
            return redirect()->back()->with('error', "Withdraw amount must be between {$limit->min_withdraw_limit} and {$limit->max_withdraw_limit}!");
        }
    }

    // Store Withdraw Request
    $withdraw = new \App\Models\UserWidthraw();
    $withdraw->payment_method_id = $request->payment_method_id;
    $withdraw->account_number    = $request->account_number;
    $withdraw->wallet_address    = $request->wallet_address;
    $withdraw->amount            = $request->amount;
    $withdraw->user_id           = $user->id;
    $withdraw->status            = 'pending';
    $withdraw->save();

    return redirect()->back()->with('success', 'Withdraw request submitted successfully!');
}



}
