<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agentpaymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class AgentPaymentmethodController extends Controller
{
    /**
     * Show all payment methods of logged-in agent
     */
    public function index()
    {
        $methods = Agentpaymentmethod::where('agent_id', Auth::id())
            ->latest()
            ->get();

        return view('agent.paymentmethodsetup.index', compact('methods'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('agent.paymentmethodsetup.create');
    }

    /**
     * Store payment method
     */
    public function store(Request $request)
    {
        $request->validate([
            'method_name'   => 'required|string|max:255',
            'method_number' => 'nullable|string|max:255',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'        => 'required|in:active,inactive',
        ]);

        $data = [
            'agent_id'      => Auth::id(), // ✅ FIX
            'method_name'   => $request->method_name,
            'method_number' => $request->method_number,
            'status'        => $request->status,
        ];

        if ($request->hasFile('photo')) {
            $filename = uniqid().'_'.$request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(
                public_path('uploads/agentpaymentsetup'),
                $filename
            );
            $data['photo'] = $filename;
        }

        Agentpaymentmethod::create($data);

        return redirect()
            ->route('paymentsetup.index')
            ->with('success', 'Payment method added successfully!');
    }

    /**
     * Edit payment method
     */
    public function edit(Agentpaymentmethod $paymentsetup)
    {
        // ✅ Security check
        if ($paymentsetup->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        return view('agent.paymentmethodsetup.edit', compact('paymentsetup'));
    }

    /**
     * Update payment method
     */
    public function update(Request $request, Agentpaymentmethod $paymentsetup)
    {
        // ✅ Security check
        if ($paymentsetup->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        $request->validate([
            'method_name'   => 'required|string|max:255',
            'method_number' => 'nullable|string|max:255',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'        => 'required|in:active,inactive',
        ]);

        $data = [
            'method_name'   => $request->method_name,
            'method_number' => $request->method_number,
            'status'        => $request->status,
        ];

        if ($request->hasFile('photo')) {

            if (
                $paymentsetup->photo &&
                File::exists(public_path('uploads/agentpaymentsetup/'.$paymentsetup->photo))
            ) {
                File::delete(public_path('uploads/agentpaymentsetup/'.$paymentsetup->photo));
            }

            $filename = uniqid().'_'.$request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(
                public_path('uploads/agentpaymentsetup'),
                $filename
            );
            $data['photo'] = $filename;
        }

        $paymentsetup->update($data);

        return redirect()
            ->route('paymentsetup.index')
            ->with('success', 'Payment method updated successfully!');
    }

    /**
     * Delete payment method
     */
    public function destroy(Agentpaymentmethod $paymentsetup)
    {
        // ✅ Security check
        if ($paymentsetup->agent_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        if (
            $paymentsetup->photo &&
            File::exists(public_path('uploads/agentpaymentsetup/'.$paymentsetup->photo))
        ) {
            File::delete(public_path('uploads/agentpaymentsetup/'.$paymentsetup->photo));
        }

        $paymentsetup->delete();

        return redirect()
            ->route('paymentsetup.index')
            ->with('success', 'Payment method deleted successfully!');
    }
}
