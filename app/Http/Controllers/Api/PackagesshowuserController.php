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
            $user = Auth::guard('sanctum')->user() ?? Auth::user();
            $activePackage = null;
            if ($user) {
                $activePackage = Packagebuy::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->first();
            }

            $formatted = $packages->map(function ($package) use ($activePackage) {
                $photoUrl = $package->photo
                    ? (filter_var($package->photo, FILTER_VALIDATE_URL) ? $package->photo : url('uploads/package/' . $package->photo))
                    : null;

                return [
                    'id' => $package->id,
                    'package_name' => $package->package_name,
                    'validity' => $package->validity,
                    'price' => (float) $package->price,
                    'daily_income' => (float) $package->daily_income,
                    'daily_limit' => (int) $package->daily_limit,
                    'photo' => $photoUrl,
                    'photo_url' => $photoUrl,
                    'is_current_active' => $activePackage ? ($activePackage->package_id == $package->id) : false,
                ];
            });

            return response()->json([
                'success' => true,
                'has_active_package' => $activePackage !== null,
                'active_package_id' => $activePackage?->package_id,
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
