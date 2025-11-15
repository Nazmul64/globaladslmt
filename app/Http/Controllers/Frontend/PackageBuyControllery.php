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

        // Check balance
        $totalApprovedDeposit = Deposite::where('user_id', $user->id)
                                        ->where('status', 'approved')
                                        ->sum('amount');

        if ($totalApprovedDeposit < $package->price) {
            return redirect()->back()->with('error', 'Insufficient balance.');
        }

        DB::transaction(function () use ($user, $package) {

            // Check previous package
            $previousPackage = Packagebuy::where('user_id', $user->id)->first();

            if ($previousPackage) {
                // 🟡 Update Existing Package (Shift system)
                $previousPackage->package_id = $package->id;
                $previousPackage->amount = $package->price;
                $previousPackage->daily_income = $package->daily_income;
                $previousPackage->daily_limit = $package->daily_limit;
                $previousPackage->save();
            } else {
                // 🟢 New Package Buy (First time)
                $previousPackage = Packagebuy::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'amount' => $package->price,
                    'daily_income' => $package->daily_income,
                    'daily_limit' => $package->daily_limit,
                    'status' => 'approved',
                ]);

                // Referral only first time
                $this->giveReferralCommission($user, $package->price);
            }

            // Deduct Deposit
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
        });

        return redirect()->back()->with('success', 'Package updated/purchased successfully!');
    }


    private function giveReferralCommission(User $user, $packagePrice)
    {
        $referrer = $user->referrer;

        if (!$referrer) return;

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
