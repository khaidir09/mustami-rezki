<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Models\TailorCommission;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\TailorTransactionItem;
use App\Models\TailorTransactionProduct;

class TailorTransactionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Mulai query builder dengan eager loading
        $query = TailorTransaction::with('customer', 'tailor', 'commission', 'supplier')->latest();

        // 2. Cek jika pengguna HANYA memiliki peran 'Tailor'
        // (dan bukan 'Super Admin' atau 'Admin')
        if ($user->hasRole('Tailor') && !$user->hasRole(['Super Admin', 'Admin'])) {
            // Jika ya, tambahkan kondisi where untuk memfilter berdasarkan ID penjahit
            $query->where('tailor_id', $user->id);
        }

        // 3. Eksekusi query
        // Jika Admin/Super Admin, tidak ada 'where' tambahan, jadi semua data akan diambil.
        $transactions = $query->get();

        return view('admin.backend.tailor.index', compact('transactions'));
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        $customers = Customer::all();
        $tailors = User::role('Tailor')->get();
        $types = ServiceType::all();
        $services = Service::where('is_active', true)->get();
        $serviceSuppliers = Supplier::where('type', 'Jasa')->get();
        return view('admin.backend.tailor.create', compact('types', 'customers', 'services', 'tailors', 'serviceSuppliers'));
    }

    /**
     * Menyimpan transaksi baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'transaction_date' => 'required|date',
            'work_type' => 'required|in:Internal,Eksternal',
            'items' => 'required|array|min:1',
            'items.*.service_type_id' => 'required|exists:service_types,id', // Validasi untuk type_id
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            // ## LANGKAH 1: HITUNG TOTAL HARGA DARI ITEM TERLEBIH DAHULU ##
            $serviceTotal = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $serviceTotal += floatval($item['quantity']) * floatval($item['price']);
                }
            }

            $productTotal = 0;
            if ($request->has('products')) {
                foreach ($request->products as $productData) {
                    // Hitung subtotal di backend
                    $productTotal += floatval($productData['quantity']) * floatval($productData['price']);
                }
            }

            // Total tagihan untuk pelanggan adalah gabungan keduanya
            $grandTotalForCustomer = $serviceTotal + $productTotal;
            $due_amount = $grandTotalForCustomer - ($request->paid_amount ?? 0);

            // Variabel untuk menyimpan data yang akan disimpan
            $transactionData = [];
            $owner_profit = 0;
            $tailor_commission = 0;
            $profit_toko = 0;

            // ## LANGKAH 2: LAKUKAN PERCABANGAN DAN PERHITUNGAN ##
            if ($request->work_type == 'Internal') {
                // --- PROSES INTERNAL ---
                $total_profit = $serviceTotal - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];
            } else { // Jika pengerjaan Eksternal
                // --- PROSES EKSTERNAL ---
                $profit_toko = $serviceTotal - ($request->cost_price ?? 0);

                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'profit' => $profit_toko, // Profit toko adalah profit utama di kasus ini
                ];
            }

            // Gabungkan data umum dengan data spesifik dari percabangan
            $finalData = array_merge($transactionData, [
                'transaction_code' => 'JAHIT-' . Carbon::now()->format('dm') . mt_rand(00, 99),
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price ?? 0,
                'total_price' => $serviceTotal,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $due_amount,
                'status' => $request->status,
            ]);

            $transaction = TailorTransaction::create($finalData);


            // ## LANGKAH 4: SIMPAN DATA TURUNAN SETELAH TRANSAKSI UTAMA DIBUAT ##

            // Simpan item-item transaksi
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $serviceId = null;
                    $namaKomponen = '';

                    if (isset($item['manual_service_name']) && !empty($item['manual_service_name'])) {
                        // --- PROSES INPUT MANUAL ---
                        // **DISESUAIKAN**: Ambil nama langsung dari inputan. service_id dibiarkan null.
                        $namaKomponen = $item['manual_service_name'];
                        $serviceId = null;
                    } else if (isset($item['service_id'])) {
                        // --- PROSES DARI DROPDOWN ---
                        // **DISESUAIKAN**: Ambil service_id dan cari namanya di database.
                        $serviceId = $item['service_id'];
                        $service = Service::find($serviceId);
                        $namaKomponen = $service ? $service->name : 'Komponen Tidak Ditemukan';
                    }

                    // Simpan item jika nama komponen berhasil didapatkan
                    if (!empty($namaKomponen)) {
                        TailorTransactionItem::create([
                            'tailor_transaction_id' => $transaction->id,
                            'service_type_id'       => $item['service_type_id'],
                            'service_id'            => $serviceId,       // Akan bernilai NULL jika input manual
                            'nama_komponen'         => $namaKomponen,    // <-- NAMA KOMPONEN DISIMPAN DI SINI
                            'quantity'              => $item['quantity'],
                            'price'                 => $item['price'],
                            'subtotal'              => floatval($item['quantity']) * floatval($item['price']),
                        ]);
                    }
                }
            }

            // ## LOGIKA BARU: Simpan produk yang dijual & kurangi stok ##
            if ($request->has('products')) {
                foreach ($request->products as $productId => $productData) {
                    $product = Product::find($productId);
                    if ($product) {
                        // Simpan ke tabel pivot baru
                        $transaction->soldProducts()->create([
                            'product_id' => $productId,
                            'product_name' => $product->name,
                            'quantity' => $productData['quantity'],
                            'price' => $productData['price'],
                            'subtotal' => $productData['quantity'] * $productData['price'],
                        ]);

                        // Kurangi stok produk
                        $product->decrement('product_qty', $productData['quantity']);
                    }

                    // ---- KALKULASI PROFIT PER ITEM ----
                    $totalProfit = ($productData['price'] - $product->modal) * $productData['quantity'];

                    if ($totalProfit > 0) {
                        // 4. Hitung jumlah untuk setiap bagian (1/3)
                        $amountPerShare = $totalProfit / 3;
                        $distributionTypes = ['pengembangan_modal', 'pribadi', 'sedekah'];

                        // 5. Buat data distribusi untuk setiap jenis
                        foreach ($distributionTypes as $type) {
                            ProfitDistribution::create([
                                'transaction_id'   => $transaction->id,
                                'transaction_type' => TailorTransactionProduct::class, // Menggunakan class constant lebih aman
                                'distribution_type' => $type,
                                'amount'           => $amountPerShare,
                            ]);
                        }
                    }
                }
            }

            $transaction->save();

            // Jalankan kembali logika penyimpanan turunan yang butuh ID transaksi
            if (
                $request->work_type == 'Internal' &&
                $tailor_commission > 0 &&
                in_array($request->status, ['Selesai', 'Diambil'])
            ) {
                TailorCommission::create([
                    'tailor_transaction_id' => $transaction->id,
                    'user_id' => $request->tailor_id,
                    'amount' => $tailor_commission,
                ]);
            }

            // Tentukan profit mana yang akan didistribusikan
            $profitToDistribute = 0;
            if ($request->work_type == 'Internal') {
                $profitToDistribute = $owner_profit;
            } else {
                $profitToDistribute = $profit_toko;
            }

            if ($profitToDistribute > 0) {
                $amountPerShare = $profitToDistribute / 3;
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
        $transaction = TailorTransaction::with(['customer', 'items.service', 'soldProducts.product'])->findOrFail($id);
        return view('admin.backend.tailor.show', compact('transaction'));
    }

    public function edit($id)
    {
        // Ambil data transaksi beserta item dan relasi servicenya
        $transaction = TailorTransaction::with('items.service')->findOrFail($id);

        // Ambil data master untuk dropdown
        $customers = Customer::all();
        $tailors = User::role('Tailor')->get();
        $services = Service::where('is_active', true)->get();
        $types = ServiceType::all();
        $serviceSuppliers = Supplier::where('type', 'Jasa')->get();

        return view('admin.backend.tailor.edit', compact('transaction', 'customers', 'services', 'types', 'tailors', 'serviceSuppliers'));
    }

    /**
     * Memperbarui data transaksi di database.
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'customer_id' => 'required',
            'transaction_date' => 'required|date',
            'work_type' => 'required|in:Internal,Eksternal',
            'items' => 'sometimes|array', // 'sometimes' berarti tidak wajib ada jika user hanya ganti status
            'tailor_id' => 'required_if:work_type,Internal',
            'supplier_id' => 'required_if:work_type,Eksternal',
        ]);

        DB::beginTransaction();
        try {
            $transaction = TailorTransaction::findOrFail($id);

            // ## LANGKAH 1: KALKULASI ULANG SEMUA NILAI DARI REQUEST ##
            $total_price = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $total_price += floatval($item['quantity']) * floatval($item['price']);
                }
            }

            $transactionData = [];
            $owner_profit = 0;
            $tailor_commission = 0;
            $profit_toko = 0;

            if ($request->work_type == 'Internal') {
                $total_profit = $total_price - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];
            } else { // Eksternal
                $profit_toko = $total_price - ($request->cost_price ?? 0);
                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'profit' => $profit_toko,
                ];
            }

            // ## LANGKAH 2: UPDATE TRANSAKSI UTAMA ##
            $due_amount = $total_price - ($request->paid_amount ?? 0);
            $finalData = array_merge($transactionData, [
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price ?? 0,
                'total_price' => $total_price,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $due_amount,
                'status' => $request->status,
            ]);
            $transaction->update($finalData);

            // ## LANGKAH 3: SINKRONISASI ITEMS ##
            $submittedItemIds = [];
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $item_id = $itemData['id'] ?? null;
                    // ... (Logika updateOrCreate item Anda sudah benar)
                    $item = TailorTransactionItem::updateOrCreate(
                        ['id' => $item_id, 'tailor_transaction_id' => $transaction->id],
                        [
                            // ... detail item
                            'service_type_id' => $itemData['service_type_id'],
                            'service_id' => $itemData['service_id'] ?? null,
                            'nama_komponen' => $itemData['manual_service_name'] ?? Service::find($itemData['service_id'])->name,
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                            'subtotal' => floatval($itemData['quantity']) * floatval($itemData['price']),
                        ]
                    );
                    $submittedItemIds[] = $item->id;
                }
            }
            $transaction->items()->whereNotIn('id', $submittedItemIds)->delete();

            // ## LANGKAH 4: HAPUS DATA TURUNAN LAMA DAN BUAT YANG BARU JIKA PERLU ##
            TailorCommission::where('tailor_transaction_id', $transaction->id)->delete();
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->delete();

            // Cek apakah status 'Selesai' atau 'Diambil'
            $isCompleted = in_array($request->status, ['Selesai', 'Diambil']);

            // Buat ulang komisi HANYA JIKA pekerjaan internal & sudah selesai
            if ($request->work_type == 'Internal' && $tailor_commission > 0 && $isCompleted) {
                TailorCommission::create([
                    'tailor_transaction_id' => $transaction->id,
                    'user_id' => $request->tailor_id,
                    'amount' => $tailor_commission,
                ]);
            }

            // Tentukan profit yang akan didistribusikan
            $profitToDistribute = ($request->work_type == 'Internal') ? $owner_profit : $profit_toko;

            // Buat ulang distribusi profit HANYA JIKA ada profit & sudah selesai
            if ($profitToDistribute > 0 && $isCompleted) {
                $amountPerShare = $profitToDistribute / 3;
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
            return redirect()->back()->withInput();
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

            TailorCommission::where('tailor_transaction_id', $transaction->id)->delete();

            // Hapus produk yang dijual dan kembalikan stok
            foreach ($transaction->soldProducts as $soldProduct) {
                $product = Product::find($soldProduct->product_id);
                if ($product) {
                    // Kembalikan stok produk
                    $product->increment('product_qty', $soldProduct->quantity);
                }
            }
            $transaction->soldProducts()->delete();

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
