<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stepguide;
use App\Models\Whychooseu;
use Illuminate\Http\Request;

class HowtoworkController extends Controller
{
    /**
     * Fetch steps and reasons for "How to Work" section
     */
    public function howtowork()
    {
        try {
            // Fetch stepguides ordered by serial_number
            $stepguides = Stepguide::orderBy('serial_number', 'asc')->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'icon' => $item->icon,
                    'serial_number' => (int) $item->serial_number, // Convert to integer
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            // Fetch all whychooseus records
            $whychooseus = Whychooseu::all()->map(function($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'icon' => $item->icon,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'stepguides' => $stepguides,
                'whychooseus' => $whychooseus,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
