<?php

namespace App\Http\Controllers\Backend;

use App\Models\Acceptance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcceptanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $acceptance = Acceptance::latest()->get();
        return view('admin.backend.acceptance.all_acceptance', compact('acceptance'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
        ]);

        Acceptance::create([
            'date' => $request->date,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        $notification = array(
            'message' => 'Penerimaan Baru Berhasil Ditambahkan',
            'alert-type' => 'success'
        );

        return redirect()->route('all.acceptance')->with($notification); //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $editData = Acceptance::find($id);
        return view('admin.backend.acceptance.edit_acceptance', compact('editData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;

        $acceptance = Acceptance::findOrFail($id);

        $acceptance->date = $request->date;
        $acceptance->amount = $request->amount;
        $acceptance->description = $request->description;
        $acceptance->save();

        $notification = array(
            'message' => 'Penerimaan Berhasil Diperbarui',
            'alert-type' => 'success'
        );
        return redirect()->route('all.acceptance')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $acceptance = Acceptance::find($id);

        $acceptance->delete();

        $notification = array(
            'message' => 'Penerimaan Berhasil Dihapus',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
