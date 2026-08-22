<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Depositelimite;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentmethodController extends Controller
{
   public function paymentmethod()
{
    try {
        // Get Active Payment Methods
        $paymentMethods = Paymentmethod::all()
            ->map(function ($method) {

                $photoPath = null;

                if ($method->photo) {
                    $fullPath = public_path('uploads/paymentmethod/' . $method->photo);
                    if (file_exists($fullPath)) {
                        $photoPath = url('uploads/paymentmethod/' . $method->photo);
                    }
                }

                if (!$photoPath) {
                    $defaultPath = public_path('uploads/paymentmethod/default.png');
                    $photoPath = file_exists($defaultPath)
                        ? url('uploads/paymentmethod/default.png')
                        : 'https://via.placeholder.com/120x60/FF6B6B/FFFFFF?text=' . urlencode($method->method_name);
                }

                // Parse numeric USD rate if present e.g. "128", "120BDT=1$", "120"
                $rawUsdRate = $method->usd_rate;
                $numericRate = null;
                if (!empty($rawUsdRate)) {
                    preg_match('/([\d\.]+)/', $rawUsdRate, $matches);
                    if (!empty($matches[1])) {
                        $numericRate = (float)$matches[1];
                    }
                }

                $isExchangeRateActive = (bool) ($method->is_exchange_rate_active ?? false);

                return [
                    'id'                      => $method->id,
                    'method_name'             => $method->method_name,
                    'method_number'           => $method->method_number,
                    'number_type'             => $method->number_type ?? 'Account Number',
                    'field_label'             => $method->number_type ?? 'Account Number',
                    'label_type'              => $method->number_type ?? 'Account Number',
                    'usd_rate'                => $isExchangeRateActive ? $method->usd_rate : null,
                    'usd_rate_bdt'            => $isExchangeRateActive ? $numericRate : null,
                    'is_exchange_rate_active' => $isExchangeRateActive,
                    'photo'                   => $photoPath,
                    'status'                  => $method->status ?? 'active',
                ];
            });

        // Get Deposit Limit (single row)
        $limit = Depositelimite::first(['min_deposit', 'max_deposit']);

        return response()->json([
            'success' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => $paymentMethods,
            'limits' => [
                'min_deposit' => $limit->min_deposit ?? 0,
                'max_deposit' => $limit->max_deposit ?? 0,
            ]
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
