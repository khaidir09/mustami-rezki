<?php

namespace App\Http\Controllers\Backend;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TailorTransactionProduct;

class SaleController extends Controller
{
    public function AllSales()
    {
        $allData = Sale::orderBy('id', 'desc')->get();
        return view('admin.backend.sales.all_sales', compact('allData'));
    }
    // End Method 

    public function AddSales()
    {
        return view('admin.backend.sales.add_sales');
    }
    // End Method

    public function StoreSales(Request $request)
    {

        $request->validate([
            'date' => 'required|date',
        ]);

        try {

            DB::beginTransaction();

            $grandTotal = 0;
            $totalProfit = 0;

            $editdatas = Sale::create([
                'date' => $request->date,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => 0,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $request->due_amount,
            ]);

            /// Store Sales Items & Update Stock 
            foreach ($request->products as $productData) {
                $product = Product::findOrFail($productData['id']);
                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                if ($netUnitCost === null) {
                    throw new \Exception("Net Unit cost is missing ofr the product id" . $productData['id']);
                }

                // ---- KALKULASI PROFIT PER ITEM ----
                $itemProfit = ($netUnitCost - $product->modal) * $productData['quantity'];
                $totalProfit += $itemProfit; // 2. Akumulasikan ke total profit

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                SaleItem::create([
                    'sale_id' => $editdatas->id,
                    'product_id' => $productData['id'],
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $product->product_qty + $productData['quantity'],
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('product_qty', $productData['quantity']);
            }

            $editdatas->update(['grand_total' => $grandTotal + $request->shipping - $request->discount]);

            if ($totalProfit > 0) {
                // 4. Hitung jumlah untuk setiap bagian (1/3)
                $amountPerShare = $totalProfit / 3;
                $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

                // 5. Buat data distribusi untuk setiap jenis
                foreach ($distributionTypes as $type) {
                    ProfitDistribution::create([
                        'transaction_id'   => $editdatas->id,
                        'transaction_type' => Sale::class, // Menggunakan class constant lebih aman
                        'distribution_type' => $type,
                        'amount'           => $amountPerShare,
                    ]);
                }
            }

            DB::commit();

            $notification = array(
                'message' => 'Data Penjualan Berhasil Disimpan',
                'alert-type' => 'success'
            );
            return redirect()->route('all.sale')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // End Method 

    public function EditSales($id)
    {
        $sale = Sale::with('saleItems.product')->findOrFail($id);
        return view('admin.backend.sales.edit_sales', compact('sale'));
    }
    // End Method 

    public function UpdateSales(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required',
            'products' => 'required|array|min:1' // Pastikan ada minimal 1 produk
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($id);

            $oldSaleItems = $sale->saleItems;
            foreach ($oldSaleItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_qty', $item->quantity);
                }
            }

            $sale->saleItems()->delete();
            ProfitDistribution::where('transaction_id', $sale->id)
                ->where('transaction_type', Sale::class)
                ->delete();

            $grandTotal = 0;
            $totalProfit = 0;

            foreach ($request->products as $product_id => $productData) {
                $product = Product::findOrFail($product_id);

                $netUnitCost = $productData['net_unit_cost'] ?? $product->price;

                $itemProfit = ($netUnitCost - $product->cost) * $productData['quantity'];
                $totalProfit += $itemProfit;

                $subtotal = ($netUnitCost * $productData['quantity']) - ($productData['discount'] ?? 0);
                $grandTotal += $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product_id,
                    'net_unit_cost' => $netUnitCost,
                    'stock' => $product->product_qty, // Stok saat ini sebelum dikurangi
                    'quantity' => $productData['quantity'],
                    'discount' => $productData['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk dengan kuantitas baru
                $product->decrement('product_qty', $productData['quantity']);
            }

            // === 4. UPDATE DATA PENJUALAN UTAMA ===
            $finalGrandTotal = $grandTotal + ($request->shipping ?? 0) - ($request->discount ?? 0);
            $dueAmount = $finalGrandTotal - ($request->paid_amount ?? 0);

            $sale->update([
                'date' => $request->date,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'status' => $request->status,
                'note' => $request->note,
                'grand_total' => $finalGrandTotal,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $dueAmount,
            ]);

            // === 5. BUAT ULANG DISTRIBUSI PROFIT ===
            if ($totalProfit > 0) {
                $amountPerShare = $totalProfit / 3;
                $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

                foreach ($distributionTypes as $type) {
                    ProfitDistribution::create([
                        'transaction_id'   => $sale->id,
                        'transaction_type' => Sale::class,
                        'distribution_type' => $type,
                        'amount'           => $amountPerShare,
                    ]);
                }
            }

            DB::commit(); // Simpan semua perubahan jika tidak ada error

            $notification = [
                'message' => 'Data Penjualan Berhasil Diperbarui',
                'alert-type' => 'success'
            ];
            return redirect()->route('all.sale')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika terjadi error

            $notification = [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }
    }
    // End Method 

    public function DeleteSales($id)
    {
        try {
            DB::beginTransaction();
            $editdatas = Sale::findOrFail($id);
            $editDatasItems = SaleItem::where('sale_id', $id)->get();

            ProfitDistribution::where('transaction_id', $editdatas->id)
                ->where('transaction_type', Sale::class) // Penting untuk polymorphic
                ->delete();

            foreach ($editDatasItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('product_qty', $item->quantity);
                }
            }
            SaleItem::where('sale_id', $id)->delete();
            $editdatas->delete();
            DB::commit();

            $notification = array(
                'message' => 'Penjualan Berhasil Dihapus',
                'alert-type' => 'success'
            );
            return redirect()->route('all.sale')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // End Method 

    public function DetailsSales($id)
    {
        $sales = Sale::with('saleItems.product')->find($id);
        return view('admin.backend.sales.sales_details', compact('sales'));
    }
    // End Method 

    public function InvoiceSales($id)
    {
        $sales = Sale::with('saleItems.product')->find($id);
        $imagePath = public_path('backend/assets/images/lunas.png');
        $logo = public_path('backend/assets/images/logo.png');

        $pdf = Pdf::loadView('admin.backend.sales.invoice_pdf', compact('sales', 'imagePath', 'logo'));
        return $pdf->stream('sales_' . $id . '.pdf');
    }
    // End Method 

    public function IndirectSales()
    {
        $allData = TailorTransactionProduct::orderBy('id', 'desc')->get();
        return view('admin.backend.sales.indirect_sales', compact('allData'));
    }
}
