<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositeInstruction;
use Illuminate\Http\Request;

class DepositeInstructionsController extends Controller
{
    public function depositeinstructions()
    {
        try {
            $instructions =DepositeInstruction::orderBy('id', 'asc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Withdraw instructions fetched successfully',
                'data' => $instructions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch withdraw instructions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
