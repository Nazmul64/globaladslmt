<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposite;
use App\Models\Package;
use App\Models\Packagebuy;
use App\Models\Userdepositerequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class PackagesshowuserController extends Controller
{
    /**
     * Show all packages (sorted by price ASC)
     */


    /**
     * Show all packages (sorted by price ASC)
     *
     * @return JsonResponse
     */
    public function packageshow(): JsonResponse
    {
        try {
            $packages = Package::orderBy('price', 'asc')->get();

            $formatted = $packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'package_name' => $package->package_name,
                    'price' => (float) $package->price,
                    'daily_income' => (float) $package->daily_income,
                    'daily_limit' => (int) $package->daily_limit,
                    'photo' => $package->photo
                        ? url('uploads/package/' . $package->photo)
                        : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted
            ], 200);

        } catch (Throwable $e) {
            Log::error('Package Fetch Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch packages'
            ], 500);
        }
    }
}
