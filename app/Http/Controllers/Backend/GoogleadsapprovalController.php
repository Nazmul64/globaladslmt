<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Googleadsapproval;
use Illuminate\Http\Request;

class GoogleadsapprovalController extends Controller
{
    /**
     * Display Google Ads Approval configuration form.
     */
    public function index()
    {
        $approval = Googleadsapproval::first() ?? new Googleadsapproval();
        return view('admin.googleadsapproval.index', compact('approval'));
    }

    /**
     * Store/Update Google Ads Approval text.
     */
    public function update(Request $request)
    {
        $request->validate([
            'approval_text' => 'nullable|string',
        ]);

        $approval = Googleadsapproval::first();

        if ($approval) {
            $approval->update([
                'approval_text' => $request->approval_text
            ]);
        } else {
            Googleadsapproval::create([
                'approval_text' => $request->approval_text
            ]);
        }

        return redirect()
            ->route('googleadsapproval.index')
            ->with('success', 'Google Ads Approval text saved successfully!');
    }
}
