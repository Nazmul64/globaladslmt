<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PaymentmethodController extends Controller
{
    /**
     * Display a listing of the payment methods.
     */
    public function index()
    {
        $methods = Paymentmethod::latest()->get();
        return view('admin.paymentmethod.index', compact('methods'));
    }

    /**
     * Show the form for creating a new payment method.
     */
    public function create()
    {
        return view('admin.paymentmethod.create');
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'method_number' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only(['method_name', 'method_number', 'status']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/paymentmethod'), $filename);
            $data['photo'] = $filename;
        }

        Paymentmethod::create($data);

        return redirect()->route('paymentmethod.index')
            ->with('success', 'Payment method added successfully!');
    }

    /**
     * Show the form for editing the specified payment method.
     */
    public function edit(Paymentmethod $paymentmethod)
    {
        return view('admin.paymentmethod.edit', compact('paymentmethod'));
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, Paymentmethod $paymentmethod)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'method_number' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only(['method_name', 'method_number', 'status']);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($paymentmethod->photo && File::exists(public_path('uploads/paymentmethod/'.$paymentmethod->photo))) {
                File::delete(public_path('uploads/paymentmethod/'.$paymentmethod->photo));
            }

            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/paymentmethod'), $filename);
            $data['photo'] = $filename;
        }

        $paymentmethod->update($data);

        return redirect()->route('paymentmethod.index')
            ->with('success', 'Payment method updated successfully!');
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy(Paymentmethod $paymentmethod)
    {
        // Delete photo if exists
        if ($paymentmethod->photo && File::exists(public_path('uploads/paymentmethod/'.$paymentmethod->photo))) {
            File::delete(public_path('uploads/paymentmethod/'.$paymentmethod->photo));
        }

        $paymentmethod->delete();

        return redirect()->route('paymentmethod.index')
            ->with('success', 'Payment method deleted successfully!');
    }
}
