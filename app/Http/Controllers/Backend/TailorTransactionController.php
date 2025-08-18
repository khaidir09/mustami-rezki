<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TailorTransactionItem;

class TailorTransactionController extends Controller
{
    public function index()
    {
        $transactions = TailorTransaction::with('customer')->latest()->get();
        return view('admin.backend.tailor.index', compact('transactions'));
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        $customers = Customer::all();
        $services = Service::where('is_active', true)->get();
        return view('admin.backend.tailor.create', compact('customers', 'services'));
    }

    /**
     * Menyimpan transaksi baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi bisa ditambahkan di sini sesuai kebutuhan

        try {
            DB::beginTransaction();

            // Hitung total harga dari item
            $total_price = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $total_price += $item['subtotal'];
                }
            }

            // Hitung profit dan sisa bayar
            $profit = $total_price - $request->cost_price;
            $due_amount = $total_price - $request->paid_amount;

            // Buat transaksi utama
            $transaction = TailorTransaction::create([
                'transaction_code' => 'JAHIT-' . Carbon::now()->format('dm') . mt_rand(00, 99),
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price,
                'total_price' => $total_price,
                'profit' => $profit,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $due_amount,
                'status' => $request->status,
            ]);

            // Simpan item-item transaksi
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    TailorTransactionItem::create([
                        'tailor_transaction_id' => $transaction->id,
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }

            // Simpan distribusi profit jika ada profit
            if ($profit > 0) {
                $amountPerShare = $profit / 3;
                $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

                foreach ($distributionTypes as $type) {
                    ProfitDistribution::create([
                        'transaction_id'   => $transaction->id,
                        'transaction_type' => TailorTransaction::class,
                        'distribution_type' => $type,
                        'amount'           => $amountPerShare,
                    ]);
                }
            }

            DB::commit();

            $notification = ['message' => 'Transaksi Jasa Jahit Berhasil Disimpan', 'alert-type' => 'success'];
            return redirect()->route('all.tailor')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = ['message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }
    }

    /**
     * Menampilkan detail transaksi.
     */
    public function show($id)
    {
        $transaction = TailorTransaction::with(['customer', 'items.service'])->findOrFail($id);
        return view('admin.backend.tailor.show', compact('transaction'));
    }

    public function edit($id)
    {
        // Ambil data transaksi beserta item dan relasi servicenya
        $transaction = TailorTransaction::with('items.service')->findOrFail($id);

        // Ambil data master untuk dropdown
        $customers = Customer::all();
        $services = Service::where('is_active', true)->get();

        return view('admin.backend.tailor.edit', compact('transaction', 'customers', 'services'));
    }

    /**
     * Memperbarui data transaksi di database.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = TailorTransaction::findOrFail($id);

            // 1. Hapus item dan distribusi profit yang lama
            $transaction->items()->delete();
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->delete();

            // 2. Hitung ulang total harga dari item baru yang dikirim
            $total_price = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $total_price += $item['subtotal'];
                }
            }

            // 3. Hitung ulang profit dan sisa bayar
            $profit = $total_price - $request->cost_price;
            $due_amount = $total_price - $request->paid_amount;

            // 4. Update data transaksi utama
            $transaction->update([
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price,
                'total_price' => $total_price,
                'profit' => $profit,
                'paid_amount' => $request->paid_amount,
                'due_amount' => $due_amount,
                'status' => $request->status,
            ]);

            // 5. Buat ulang item-item transaksi
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    TailorTransactionItem::create([
                        'tailor_transaction_id' => $transaction->id,
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            }

            // 6. Buat ulang distribusi profit jika ada profit
            if ($profit > 0) {
                $amountPerShare = $profit / 3;
                $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

                foreach ($distributionTypes as $type) {
                    ProfitDistribution::create([
                        'transaction_id'   => $transaction->id,
                        'transaction_type' => TailorTransaction::class,
                        'distribution_type' => $type,
                        'amount'           => $amountPerShare,
                    ]);
                }
            }

            DB::commit();

            $notification = ['message' => 'Transaksi Jasa Jahit Berhasil Diperbarui', 'alert-type' => 'success'];
            return redirect()->route('all.tailor')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = ['message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }
    }

    /**
     * Menghapus transaksi jahit.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $transaction = TailorTransaction::findOrFail($id);

            // Hapus distribusi profit terkait
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->delete();

            // Hapus transaksi utama (item akan terhapus otomatis karena onDelete('cascade'))
            $transaction->delete();

            DB::commit();

            $notification = ['message' => 'Transaksi Jasa Jahit Berhasil Dihapus', 'alert-type' => 'success'];
            return redirect()->route('tailor.index')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = ['message' => 'Gagal menghapus data!', 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }
    }
}
