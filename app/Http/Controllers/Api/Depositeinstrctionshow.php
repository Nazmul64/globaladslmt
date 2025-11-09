<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositInstruction;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Depositeinstrctionshow extends Controller
{
     public function Depositeinstrctionshow()
    {
        try {
            // Fetch all deposit instructions from database
            $show_instruction = DepositInstruction::all();

            // Log the data for debugging
            Log::info('Deposit Instructions Retrieved', ['count' => $show_instruction->count()]);

            // Check if data exists
            if ($show_instruction->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No deposit instructions found',
                    'data' => [],
                ], 200);
            }

            // Return successful response with data
            return response()->json([
                'success' => true,
                'message' => 'Deposit instructions retrieved successfully',
                'data' => $show_instruction,
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error fetching deposit instructions: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deposit instructions',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
public function Depositeinstrctionshowvideos()
    {
        try {
            // Get Deposit Instructions with all required fields
            $depositInstructions = DepositInstruction::select(
                'id',
                'deposite_instructions_title',
                'deposite_instructions_description',
                'video_url'
            )->get();

            // Get Payment Methods with name and photo
            $paymentMethods = Paymentmethod::select('id', 'method_name', 'photo')
                ->get()
                ->map(function ($method) {
                    // Build full image URL if photo exists
                    $method->photo = $method->photo
                        ? url('uploads/paymentmethod/' . $method->photo)
                        : null;
                    return $method;
                });

            // Log for debugging
            Log::info('Deposit Instructions Count: ' . $depositInstructions->count());
            Log::info('Payment Methods Count: ' . $paymentMethods->count());

            return response()->json([
                'success' => true,
                'message' => 'Deposit instructions & payment methods retrieved successfully',
                'data' => [
                    'deposit_instructions' => $depositInstructions,
                    'payment_methods' => $paymentMethods,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching deposit instructions and payment methods: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage(),
                'data' => [
                    'deposit_instructions' => [],
                    'payment_methods' => [],
                ]
            ], 500);
        }
    }

}
