<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widthrawlimit;
use Illuminate\Http\Request;

class WidthrawlimitController extends Controller
{
    public function index()
    {
        $limits = Widthrawlimit::all();
        return view('admin.widthrawlimit.index', compact('limits'));
    }

    public function create()
    {
        return view('admin.widthrawlimit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'max_withdraw_limit' => 'required|numeric|min:0',
            'min_withdraw_limit' => 'required|numeric|min:0',
        ]);

        Widthrawlimit::create([
            'max_withdraw_limit' => $request->max_withdraw_limit,
            'min_withdraw_limit' => $request->min_withdraw_limit,
        ]);

        return redirect()->route('widthrawlimit.index')->with('success', 'Limit added successfully.');
    }

    public function edit($id)
    {
        $widthrawlimit = Widthrawlimit::findOrFail($id);
        return view('admin.widthrawlimit.edit', compact('widthrawlimit'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'max_withdraw_limit' => 'required|numeric|min:0',
            'min_withdraw_limit' => 'required|numeric|min:0',
        ]);

        $widthrawlimit = Widthrawlimit::findOrFail($id);
        $widthrawlimit->update([
            'max_withdraw_limit' => $request->max_withdraw_limit,
            'min_withdraw_limit' => $request->min_withdraw_limit,
        ]);

        return redirect()->route('widthrawlimit.index')->with('success', 'Limit updated successfully.');
    }

    public function destroy($id)
    {
        $widthrawlimit = Widthrawlimit::findOrFail($id);
        $widthrawlimit->delete();

        return redirect()->route('widthrawlimit.index')->with('success', 'Limit deleted successfully.');
    }
}
