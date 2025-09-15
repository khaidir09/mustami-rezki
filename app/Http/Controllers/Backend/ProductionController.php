<?php

namespace App\Http\Controllers\Backend;

use App\Models\Production;
use Illuminate\Http\Request;
use App\Models\ProfitDistribution;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $production = Production::latest()->get();
        return view('admin.backend.production.all_production', compact('production'));
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
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
        ]);

        $total = $request->price * $request->quantity;
        $rate = $request->price  * (2 / 3);
        $commission = $rate * $request->quantity;
        $profit = $total - $commission;


        $production = Production::create([
            'user_id' => Auth::user()->id,
            'date' => $request->date,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'rate' => $rate,
            'total_commission' => $commission,
        ]);

        $amountPerShare = $profit / 3;
        $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

        foreach ($distributionTypes as $type) {
            ProfitDistribution::create([
                'transaction_id'   => $production->id,
                'transaction_type' => Production::class,
                'distribution_type' => $type,
                'amount'           => $amountPerShare,
            ]);
        }

        $notification = array(
            'message' => 'Produksi Baru Berhasil Ditambahkan',
            'alert-type' => 'success'
        );

        return redirect()->route('all.production')->with($notification); //
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
        $production = Production::findOrFail($id);
        return view('admin.backend.production.edit_production', compact('production'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $production = Production::findOrFail($id);

        $total = $request->price * $request->quantity;
        $rate = $request->price  * (2 / 3);
        $commission = $rate * $request->quantity;
        $profit = $total - $commission;

        $production->update([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'rate' => $rate,
            'total_commission' => $commission,
        ]);

        ProfitDistribution::where('transaction_id', $production->id)
            ->where('transaction_type', Production::class)
            ->delete();

        $amountPerShare = $profit / 3;
        $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

        foreach ($distributionTypes as $type) {
            ProfitDistribution::create([
                'transaction_id'   => $production->id,
                'transaction_type' => Production::class,
                'distribution_type' => $type,
                'amount'           => $amountPerShare,
            ]);
        }

        $notification = array(
            'message' => 'Produksi Berhasil Diperbarui',
            'alert-type' => 'success'
        );
        return redirect()->route('all.production')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $production = Production::findOrFail($id);

        ProfitDistribution::where('transaction_id', $production->id)
            ->where('transaction_type', Production::class)
            ->delete();

        $production->delete();

        $notification = array(
            'message' => 'Produksi Berhasil Dihapus',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
