<?php

namespace App\Http\Controllers\Backend;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expense = Expense::latest()->get();
        return view('admin.backend.expense.all_expense', compact('expense'));
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
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->category == 'Keluarga' && !auth()->user()->hasRole('Super Admin')) {
            $notification = array(
                'message' => 'Anda tidak memiliki akses untuk memilih kategori Keluarga',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }

        $image = $request->file('photo');
        $save_url = null;
        if ($image) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            $img = $manager->read($image);
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save('upload/expense/' . $name_gen);
            $save_url = 'upload/expense/' . $name_gen;
        }

        Expense::create([
            'date' => $request->date,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'photo' => $save_url,
        ]);

        $notification = array(
            'message' => 'Pengeluaran Baru Berhasil Ditambahkan',
            'alert-type' => 'success'
        );

        return redirect()->route('all.expense')->with($notification); //
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
        $editData = Expense::find($id);
        return view('admin.backend.expense.edit_expense', compact('editData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->id;

        $expense = Expense::findOrFail($id);

        $image = $request->file('photo');
        $save_url = null;
        if ($image) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            $img = $manager->read($image);
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save('upload/expense/' . $name_gen);
            $save_url = 'upload/expense/' . $name_gen;
        }

        if ($request->category == 'Keluarga' && !auth()->user()->hasRole('Super Admin')) {
            $notification = array(
                'message' => 'Anda tidak memiliki akses untuk memilih kategori Keluarga',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }

        if ($image && $expense->photo) {
            // Hapus file lama jika ada
            unlink($expense->photo);
        }

        $expense->date = $request->date;
        $expense->category = $request->category;
        $expense->amount = $request->amount;
        $expense->description = $request->description;
        $expense->photo = $save_url;
        $expense->save();

        $notification = array(
            'message' => 'Pengeluaran Berhasil Diperbarui',
            'alert-type' => 'success'
        );
        return redirect()->route('all.expense')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::find($id);
        if ($expense->photo) {
            unlink($expense->photo);
        }

        $expense->delete();

        $notification = array(
            'message' => 'Pengeluaran Berhasil Dihapus',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
