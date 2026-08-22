<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;

class SupportController extends Controller
{
   public function support()
    {
        try {
            // Fetch all support data from database
            $data = Support::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Support data loaded successfully',
                'data' => $data,
                'count' => $data->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to load support data',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
