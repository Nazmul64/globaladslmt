<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposite;
use App\Models\Agentnotice;
use App\Models\Agentwidthraw;
use App\Models\Userdepositerequest;
use App\Models\UserWidhrawrequest;
use App\Models\User;
use App\Models\LockedSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function agent_dashboard()
    {
        $auth_user_id = Auth::id();
        $user = User::findOrFail($auth_user_id);

        // ✅ Get ACTIVE global lock (status = 1)
        $activeLock = LockedSetting::where('status', 1)->first();

        // ✅ Calculate EFFECTIVE locked amount (NEVER update database here)
        $locked_amount = 0;
        $lock_status = 'No Lock';

        if ($user->is_locked_override) {
            // Agent has CUSTOM lock - use their database value
            $locked_amount = $user->locked_amount;
            $lock_status = $locked_amount > 0 ? 'Custom Locked' : 'Custom Unlocked';

            Log::info('Agent Lock Calculation', [
                'agent_id' => $user->id,
                'logic' => 'CUSTOM LOCK',
                'is_locked_override' => true,
                'locked_amount' => $locked_amount
            ]);
        } else {
            // Agent FOLLOWS global lock
            if ($activeLock && $activeLock->status == 1) {
                // Global lock is ACTIVE - use global amount
                $locked_amount = $activeLock->locked_amount;
                $lock_status = 'Global Lock Active';

                Log::info('Agent Lock Calculation', [
                    'agent_id' => $user->id,
                    'logic' => 'GLOBAL LOCK ACTIVE',
                    'is_locked_override' => false,
                    'global_status' => 1,
                    'locked_amount' => $locked_amount
                ]);
            } else {
                // Global lock is INACTIVE or doesn't exist - NO LOCK
                $locked_amount = 0;
                $lock_status = 'No Lock';

                Log::info('Agent Lock Calculation', [
                    'agent_id' => $user->id,
                    'logic' => 'GLOBAL LOCK INACTIVE',
                    'is_locked_override' => false,
                    'global_exists' => $activeLock ? 'yes' : 'no',
                    'global_status' => $activeLock ? $activeLock->status : 'N/A',
                    'locked_amount' => 0
                ]);
            }
        }

        // ⚠️ CRITICAL: DO NOT call $user->save() here!

        // ✅ Calculate total approved deposits
        $total_deposite = AgentDeposite::where('agent_id', $auth_user_id)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;

        // ✅ Calculate commissions
        $deposit_income = Userdepositerequest::where('agent_id', $auth_user_id)
            ->sum('agent_commission') ?? 0;

        $withdraw_income = UserWidhrawrequest::where('agent_id', $auth_user_id)
            ->sum('agent_commission') ?? 0;

        $total_come = $deposit_income + $withdraw_income;

        // ✅ Calculate total approved withdrawals
        $total_widthraw = Agentwidthraw::where('status', 'approved')
            ->where('user_id', $auth_user_id)
            ->sum('amount') ?? 0;

        // ✅ Calculate available balance
        $available_balance = max(0, $total_deposite - $locked_amount);

        Log::info('Agent Dashboard Loaded', [
            'agent_id' => $user->id,
            'total_deposit' => $total_deposite,
            'locked_amount' => $locked_amount,
            'available_balance' => $available_balance,
            'lock_status' => $lock_status
        ]);


        $notices = Agentnotice::latest()->get();

        return view('agent.index', compact(
            'total_deposite',
            'available_balance',
            'locked_amount',
            'total_come',
            'deposit_income',
            'withdraw_income',
            'total_widthraw',
            'lock_status',
            'notices',
        ));
    }
}
