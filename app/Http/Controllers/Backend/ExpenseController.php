<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

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
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
        ]);

        Expense::create([
            'date' => $request->date,
            'amount' => $request->amount,
            'description' => $request->description,
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

        $expense->date = $request->date;
        $expense->description = $request->description;
        $expense->amount = $request->amount;
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
        Expense::find($id)->delete();

        $notification = array(
            'message' => 'Pengeluaran Berhasil Dihapus',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
