<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Models\Packagebuy;
use App\Models\Notice;
use App\Models\Wornotice;
use Illuminate\Support\Facades\Auth;

class WroknoticesController extends BaseController
{
    public function worknotices()
    {
        try {
            $auth_user_id = Auth::id();

            // user not logged in
            if (!$auth_user_id) {
                return $this->sendError('Unauthorized user', [], 401);
            }

            // check active package
            $has_active_package = Packagebuy::where('user_id', $auth_user_id)
                ->where('status', 'approved')
                ->exists();

            $work_notices = Wornotice::all();
            $notices = Notice::all();

            $responseData = [
                'has_active_package'  => $has_active_package,
                'work_notices'        => $work_notices,
                'notices'             => $notices,
            ];

            return $this->sendResponse($responseData, 'Work notices fetched successfully');

        } catch (\Throwable $th) {
            return $this->sendError('Something went wrong', [$th->getMessage()], 500);
        }
    }
}
