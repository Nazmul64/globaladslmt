<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WidthrawInstruction;
use Illuminate\Http\Request;

class WidthrawInstructionController extends Controller
{

   public function widthrawinstructionss()
    {
        try {
            $instructions = WidthrawInstruction::orderBy('id', 'asc')->get();

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
