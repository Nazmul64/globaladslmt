<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TakaandDollarsigend;
use Illuminate\Http\Request;

class TakaandDollarsigendController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tollar_signed = TakaandDollarsigend::latest()->get();
        return view('admin.dollarsigned.index', compact('tollar_signed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.dollarsigned.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dollarsigned' => 'required|string',
        ]);

        TakaandDollarsigend::create($request->all());

        return redirect()->route('dollarsiged.index')
                         ->with('success', 'Record added successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $takaandDollarsigend = TakaandDollarsigend::findOrFail($id);
        return view('admin.dollarsigned.edit', compact('takaandDollarsigend'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, $id)
{
    $request->validate([
        'dollarsigned' => 'required|string',
    ]);

    $record = TakaandDollarsigend::findOrFail($id);
    $record->update($request->all());

    return redirect()->route('dollarsiged.index')
                     ->with('success', 'Record updated successfully');
}



    /**
     * Remove the specified resource from storage.
     */
   public function destroy(TakaandDollarsigend $dollarsiged)
{
    $dollarsiged->delete();
    return redirect()->route('dollarsiged.index')
                     ->with('success', 'Record deleted successfully');
}

}
