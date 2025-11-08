<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentmethodController extends Controller
{
    public function paymentmethod()
    {
        try {
            $paymentMethods = Paymentmethod::select('method_name', 'method_number', 'photo')
                ->get()
                ->map(function ($method) {
                    // Check if photo exists
                    $photoPath = null;

                    if ($method->photo) {
                        // Full path check
                        $fullPath = public_path('uploads/paymentmethod/' . $method->photo);

                        if (file_exists($fullPath)) {
                            $photoPath = url('uploads/paymentmethod/' . $method->photo);
                        } else {
                            // Try without uploads folder
                            $altPath = public_path('paymentmethod/' . $method->photo);
                            if (file_exists($altPath)) {
                                $photoPath = url('paymentmethod/' . $method->photo);
                            }
                        }
                    }

                    // Fallback to default or placeholder
                    if (!$photoPath) {
                        $defaultPath = public_path('uploads/paymentmethod/default.png');
                        $photoPath = file_exists($defaultPath)
                            ? url('uploads/paymentmethod/default.png')
                            : 'https://via.placeholder.com/120x60/FF6B6B/FFFFFF?text=' . urlencode($method->method_name);
                    }

                    return [
                        'method_name'   => $method->method_name,
                        'method_number' => $method->method_number,
                        'photo'         => $photoPath,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentMethods,
                'message' => 'Payment methods retrieved successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

}
