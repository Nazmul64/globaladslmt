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
            'number_type' => 'nullable|string|max:255',
            'usd_rate' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,avif,jfif|max:10240',
            'status' => 'required|in:active,inactive',
        ], [
            'photo.mimes' => 'Supported formats are JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.',
            'photo.max' => 'Image size cannot exceed 10MB.',
        ]);

        $data = $request->only(['method_name', 'method_number', 'number_type', 'usd_rate', 'status']);
        $data['number_type'] = $request->number_type ?? 'Account Number';
        $data['is_exchange_rate_active'] = $request->boolean('is_exchange_rate_active') || in_array($request->is_exchange_rate_active, [1, '1', 'on', 'active', true], true);
        $data['is_account_number_active'] = $request->has('is_account_number_active')
            ? ($request->boolean('is_account_number_active') || in_array($request->is_account_number_active, [1, '1', 'on', 'active', true], true))
            : true;

        if ($request->hasFile('photo')) {
            $path = public_path('uploads/paymentmethod');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);
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
            'number_type' => 'nullable|string|max:255',
            'usd_rate' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,bmp,avif,jfif|max:10240',
            'status' => 'required|in:active,inactive',
        ], [
            'photo.mimes' => 'Supported formats are JPG, JPEG, PNG, GIF, SVG, WEBP, BMP, AVIF, JFIF.',
            'photo.max' => 'Image size cannot exceed 10MB.',
        ]);

        $data = $request->only(['method_name', 'method_number', 'number_type', 'usd_rate', 'status']);
        $data['number_type'] = $request->number_type ?? 'Account Number';
        $data['is_exchange_rate_active'] = $request->boolean('is_exchange_rate_active') || in_array($request->is_exchange_rate_active, [1, '1', 'on', 'active', true], true);
        $data['is_account_number_active'] = $request->has('is_account_number_active')
            ? ($request->boolean('is_account_number_active') || in_array($request->is_account_number_active, [1, '1', 'on', 'active', true], true))
            : false;

        if ($request->hasFile('photo')) {
            $path = public_path('uploads/paymentmethod');
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
            if ($paymentmethod->photo && File::exists($path . '/' . $paymentmethod->photo)) {
                @File::delete($path . '/' . $paymentmethod->photo);
            }

            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);
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
