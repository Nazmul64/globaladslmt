<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentcommissonsetup;
use App\Models\AgentDeposite;
use App\Models\Userdepositerequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AgentracceptuserandDeposite extends Controller
{
    // Show deposit requests - শুধু নিজের রিকোয়েস্ট দেখবে
    public function agentDepositRequests(Request $request)
    {
        $agentId = Auth::id();

        // Show rejected only if requested
        if ($request->has('rejected') && $request->rejected == 1) {
            $requests = Userdepositerequest::with('user:id,name,email,mobile')
                ->where('agent_id', $agentId)
                ->where('status', 'rejected')
                ->orderBy('id', 'desc')
                ->paginate(5)
                ->withQueryString();
        } else {
            // শুধু নিজের রিকোয়েস্ট - Pending/User Submitted first
            $requests = Userdepositerequest::with('user:id,name,email,mobile')
                ->where('agent_id', $agentId)
                ->orderByRaw("CASE WHEN status IN ('pending','user_submitted') THEN 0 ELSE 1 END ASC, id DESC")
                ->paginate(5)
                ->withQueryString();
        }

        return view('agent.userdepositewidhrawaccept.index', compact('requests'));
    }

    // Accept deposit
    public function acceptDepositRequest($id)
    {
        $depositRequest = Userdepositerequest::findOrFail($id);
        $agentId = Auth::id();

        // চেক করুন এটা এই এজেন্টের রিকোয়েস্ট কিনা
        if ($depositRequest->agent_id != $agentId) {
            return back()->with('error', 'This request does not belong to you!');
        }

        // চেক করুন স্ট্যাটাস পেন্ডিং কিনা
        if ($depositRequest->status !== 'pending') {
            return back()->with('error', 'Request is not pending.');
        }

        // এজেন্টের ওয়ালেট চেক করুন
        $agentWallet = AgentDeposite::firstOrCreate(['agent_id' => $agentId]);

        if ($agentWallet->amount < $depositRequest->amount) {
            return back()->with('error', 'আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই!');
        }

        // স্ট্যাটাস আপডেট করুন
        $depositRequest->update(['status' => 'agent_confirmed']);

        return back()->with('success', 'Request accepted. Waiting for user payment proof.');
    }

    // Reject deposit
    public function agentRejected($id)
    {
        $depositRequest = Userdepositerequest::findOrFail($id);
        $agentId = Auth::id();

        // চেক করুন এটা এই এজেন্টের রিকোয়েস্ট কিনা
        if ($depositRequest->agent_id != $agentId) {
            return back()->with('error', 'This request does not belong to you!');
        }

        // চেক করুন স্ট্যাটাস completed বা rejected কিনা
        if (in_array($depositRequest->status, ['completed', 'rejected'])) {
            return back()->with('info', 'This request cannot be changed.');
        }

        // রিজেক্ট করুন
        $depositRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Deposit request rejected successfully.');
    }

    // Final confirm
    public function finalDepositConfirm($id)
    {
        $deposit = Userdepositerequest::findOrFail($id);
        $agentId = Auth::id();

        // চেক করুন এটা এই এজেন্টের রিকোয়েস্ট কিনা
        if ($deposit->agent_id != $agentId) {
            return back()->with('error', 'This request does not belong to you!');
        }

        // চেক করুন ইউজার পেমেন্ট ইনফো সাবমিট করেছে কিনা
        if ($deposit->status !== 'user_submitted') {
            return back()->with('error', 'User has not submitted payment info yet.');
        }

        DB::beginTransaction();
        try {
            // ইউজার খুঁজুন
            $user = User::findOrFail($deposit->user_id);

            // এজেন্ট ওয়ালেট
            $agentWallet = AgentDeposite::firstOrCreate(['agent_id' => $deposit->agent_id]);

            // এজেন্টের ব্যালেন্স চেক
            if ($agentWallet->amount < $deposit->amount) {
                DB::rollBack();
                return back()->with('error', 'আপনার ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই!');
            }

            // কমিশন ক্যালকুলেশন
            $commissionSetup = Agentcommissonsetup::where('status', 1)->latest()->first();
            $agentCommission = 0;

            if ($commissionSetup && $deposit->type === 'deposit') {
                if ($commissionSetup->commission_type === 'percent') {
                    $agentCommission = ($deposit->amount * $commissionSetup->deposit_agent_commission) / 100;
                } else {
                    $agentCommission = $commissionSetup->deposit_agent_commission;
                }
            }

            // এজেন্ট ওয়ালেট আপডেট (ডিপোজিট পরিমাণ বিয়োগ + কমিশন যোগ)
            $agentWallet->amount = ($agentWallet->amount - $deposit->amount) + $agentCommission;
            $agentWallet->save();

            // ইউজার ব্যালেন্স আপডেট
            $user->balance += $deposit->amount;
            $user->save();

            // ডিপোজিট রিকোয়েস্ট কমপ্লিট করুন
            $deposit->update([
                'status' => 'completed',
                'agent_commission' => $agentCommission,
                'admin_commission' => 0
            ]);

            DB::commit();

            return back()->with('success', "Deposit completed successfully! Agent earned ৳" . number_format($agentCommission, 2) . " commission.");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }
}
