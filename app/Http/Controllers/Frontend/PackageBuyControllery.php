<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Packagebuy;
use App\Models\Reffercommissionsetup;
use App\Models\User;
use App\Models\Deposite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PackageBuyControllery extends Controller
{
    public function frontend_packages_buy(Request $request, $package_id)
    {
        $user = Auth::user();
        $package = Package::findOrFail($package_id);

        // Check if package has price
        if (!$package->price) {
            return redirect()->back()->with('error', 'Package price not set!');
        }

        // Total approved deposit of user
        $totalApprovedDeposit = Deposite::where('user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->sum('amount');

        if ($totalApprovedDeposit < $package->price) {
            return redirect()->back()->with('error', 'Insufficient balance.');
        }

        DB::transaction(function () use ($user, $package) {

            // 1. Insert into packagebuys
            $packageBuy = Packagebuy::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'amount' => $package->price,
                'status' => 'approved',
            ]);

            // 2. Deduct from user's approved deposits
            $amountToDeduct = $package->price;
            $userDeposits = Deposite::where('user_id', $user->id)
                                     ->where('status', 'approved')
                                     ->orderBy('id', 'asc')
                                     ->get();

            foreach ($userDeposits as $deposit) {
                if ($amountToDeduct <= 0) break;

                if ($deposit->amount >= $amountToDeduct) {
                    $deposit->amount -= $amountToDeduct;
                    $deposit->save();
                    $amountToDeduct = 0;
                } else {
                    $amountToDeduct -= $deposit->amount;
                    $deposit->amount = 0;
                    $deposit->save();
                }
            }

            // 3. Give referral commission if this is the first purchase
            $firstPurchase = Packagebuy::where('user_id', $user->id)->count() === 1;
            if ($firstPurchase) {
                $this->giveReferralCommission($user, $package->price);
            }
        });

        return redirect()->back()->with('success', 'Package purchased successfully!');
    }

    private function giveReferralCommission(User $user, $packagePrice)
    {
        $referrer = $user->referrer;
        if (!$referrer) return;

        // Get commission levels
        $commissionLevels = Reffercommissionsetup::orderBy('reffer_level', 'asc')->get();

        foreach ($commissionLevels as $commission) {
            if (!$referrer) break;

            $commissionAmount = ($commission->commission_percentage / 100) * $packagePrice;

            $referrer->balance += $commissionAmount;
            $referrer->refer_income += $commissionAmount;
            $referrer->save();

            $referrer = $referrer->referrer;
        }
    }
}
