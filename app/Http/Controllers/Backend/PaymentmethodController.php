<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PaymentmethodController extends Controller
{
    public function index()
    {
        $methods = Paymentmethod::latest()->get();
        return view('admin.paymentmethod.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.paymentmethod.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only(['method_name', 'status']);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/paymentmethod'), $filename);
            $data['photo'] = $filename;
        }

        Paymentmethod::create($data);

        return redirect()->route('paymentmethod.index')->with('success', 'Payment method added successfully!');
    }

    public function edit(Paymentmethod $paymentmethod)
    {
        return view('admin.paymentmethod.edit', compact('paymentmethod'));
    }

    public function update(Request $request, Paymentmethod $paymentmethod)
    {
        $request->validate([
            'method_name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only(['method_name', 'status']);

        if ($request->hasFile('photo')) {
            // Delete old photo
            $oldPhoto = public_path('uploads/paymentmethod/'.$paymentmethod->photo);
            if ($paymentmethod->photo && File::exists($oldPhoto)) {
                File::delete($oldPhoto);
            }

            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/paymentmethod'), $filename);
            $data['photo'] = $filename;
        }

        $paymentmethod->update($data);

        return redirect()->route('paymentmethod.index')->with('success', 'Payment method updated successfully!');
    }

    public function destroy(Paymentmethod $paymentmethod)
    {
        $oldPhoto = public_path('uploads/paymentmethod/'.$paymentmethod->photo);
        if ($paymentmethod->photo && File::exists($oldPhoto)) {
            File::delete($oldPhoto);
        }

        $paymentmethod->delete();

        return redirect()->route('paymentmethod.index')->with('success', 'Payment method deleted successfully!');
    }
}
