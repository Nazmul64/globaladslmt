<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ServerSetting;
use Illuminate\Http\Request;

class ServerSettingController extends Controller
{
    /**
     * Display server mode settings view in admin panel.
     */
    public function index()
    {
        $setting = ServerSetting::getSetting();
        return view('admin.serversetting.index', compact('setting'));
    }

    /**
     * Update server settings (mode, local_url, live_url).
     */
    public function update(Request $request)
    {
        $request->validate([
            'server_mode' => 'required|in:local,live',
            'local_url'   => 'required|url',
            'live_url'    => 'required|url',
        ]);

        $setting = ServerSetting::getSetting();
        $setting->update([
            'server_mode' => $request->server_mode,
            'local_url'   => $request->local_url,
            'live_url'    => $request->live_url,
        ]);

        return redirect()
            ->route('admin.server.setting')
            ->with('success', 'Server settings updated successfully! Active mode: ' . strtoupper($setting->server_mode));
    }

    /**
     * Quick toggle server mode between 'local' and 'live'.
     */
    public function toggleMode(Request $request)
    {
        $mode = $request->input('mode');
        if (!in_array($mode, ['local', 'live'])) {
            return redirect()->back()->with('error', 'Invalid server mode selection.');
        }

        $setting = ServerSetting::getSetting();
        $setting->update(['server_mode' => $mode]);

        $message = $mode === 'local' 
            ? 'Switched to Local Server Mode (http://10.0.2.2:8000)' 
            : 'Switched to Live Production Server Mode (https://globalmoney.ltd)';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Public API endpoint to fetch current server mode configuration for Flutter App.
     */
    public function getServerMode()
    {
        try {
            $setting = ServerSetting::getSetting();

            return response()->json([
                'status'  => true,
                'message' => 'Server mode retrieved successfully',
                'data'    => [
                    'server_mode'   => $setting->server_mode,
                    'is_local_mode' => $setting->server_mode === 'local',
                    'local_url'     => $setting->local_url,
                    'live_url'      => $setting->live_url,
                    'active_url'    => $setting->active_url,
                    'updated_at'    => $setting->updated_at ? $setting->updated_at->format('Y-m-d H:i:s') : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error retrieving server mode: ' . $e->getMessage(),
                'data'    => [
                    'server_mode'   => 'local',
                    'is_local_mode' => true,
                    'local_url'     => 'http://10.0.2.2:8000',
                    'live_url'      => 'https://globalmoney.ltd',
                    'active_url'    => 'http://10.0.2.2:8000',
                ]
            ], 500);
        }
    }
}
