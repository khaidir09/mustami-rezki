<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function AllService()
    {
        $service = Service::latest()->get();
        return view('admin.backend.service.all_service', compact('service'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function StoreService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
        ]);

        Service::create([
            'name' => $request->name,
            'base_price' => $request->base_price,
            'is_active' => $request->filled('is_active'),
        ]);

        $notification = array(
            'message' => 'Komponen Jasa Baru Berhasil Ditambahkan',
            'alert-type' => 'success'
        );

        return redirect()->route('all.service')->with($notification); //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function EditService($id)
    {
        $editData = Service::find($id);
        return view('admin.backend.service.edit_service', compact('editData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function UpdateService(Request $request)
    {
        $id = $request->id;

        $service = Service::findOrFail($id);

        $service->name = $request->name;
        $service->base_price = $request->base_price;
        $service->is_active = $request->is_active;
        $service->save();

        $notification = array(
            'message' => 'Komponen Jasa Jahit Berhasil Diperbarui',
            'alert-type' => 'success'
        );
        return redirect()->route('all.service')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function DeleteService($id)
    {
        Service::find($id)->delete();

        $notification = array(
            'message' => 'Jasa Jahit Berhasil Dihapus',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
