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
use Barryvdh\DomPDF\Facade\Pdf;

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

            $pickedUpAt = null;
            if ($request->status === 'Diambil') {
                $pickedUpAt = now();
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
                'picked_up_at' => $pickedUpAt,
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

            // ## LANGKAH 5: SIMPAN PRODUK TAMBAHAN & HITUNG PROFIT PRODUK ##
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

                    if ($totalProfit > 0 && $request->status === 'Diambil') {
                        ProfitDistribution::create([
                            'transaction_id'   => $transaction->id,
                            'transaction_type' => TailorTransactionProduct::class, // Menggunakan class constant lebih aman
                            'amount'           => $totalProfit,
                            'created_at'       => now(),
                        ]);
                    }
                }
            }

            $transaction->save();

            $isCommissionable = in_array($request->status, ['Selesai', 'Diambil']);
            $isProfitDistributable = ($request->status == 'Diambil');


            if (
                $request->work_type == 'Internal' &&
                $tailor_commission > 0 &&
                $isCommissionable
            ) {
                TailorCommission::create([
                    'tailor_transaction_id' => $transaction->id,
                    'user_id' => $request->tailor_id,
                    'amount' => $tailor_commission,
                ]);
            }

            $profitToDistribute = ($request->work_type == 'Internal') ? $owner_profit : $profit_toko;

            // 4. Distribusikan profit HANYA JIKA ada profit & statusnya 'Diambil'
            if ($profitToDistribute > 0 && $isProfitDistributable) {
                ProfitDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'transaction_type' => TailorTransaction::class,
                    'amount'           => $profitToDistribute,
                ]);
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

    public function cetak($id)
    {
        $transaction = TailorTransaction::with(['customer', 'items.service', 'soldProducts.product'])->findOrFail($id);
        $imagePath = public_path('backend/assets/images/lunas.png');
        $logo = public_path('backend/assets/images/logo.png');

        $pdf = Pdf::loadView('admin.backend.tailor.print', compact(
            'transaction',
            'imagePath',
            'logo'
        ));
        return $pdf->stream('Nota Jahit ' . $transaction->transaction_code . $transaction->customer->name . '.pdf');
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
            'products' => 'sometimes|array',
            'tailor_id' => 'required_if:work_type,Internal',
            'supplier_id' => 'required_if:work_type,Eksternal',
        ]);

        DB::beginTransaction();
        try {
            $transaction = TailorTransaction::findOrFail($id);

            // ## LANGKAH 1: KALKULASI ULANG SEMUA NILAI DARI REQUEST ##
            $serviceTotal = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $serviceTotal  += floatval($item['quantity']) * floatval($item['price']);
                }
            }

            $productTotal = 0;
            if ($request->has('products')) {
                foreach ($request->products as $productData) {
                    $productTotal += floatval($productData['quantity']) * floatval($productData['price']);
                }
            }

            $grandTotalForCustomer = $serviceTotal + $productTotal;
            $due_amount = $grandTotalForCustomer - ($request->paid_amount ?? 0);

            $transactionData = [];
            $owner_profit = 0;
            $tailor_commission = 0;
            $profit_toko = 0;

            if ($request->work_type == 'Internal') {
                $total_profit = $serviceTotal - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];
            } else { // Eksternal
                $profit_toko = $serviceTotal - ($request->cost_price ?? 0);
                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'profit' => $profit_toko,
                ];
            }

            // Cek logika: Jika status berubah menjadi 'Diambil' DAN sebelumnya belum ada tanggal ambilnya
            if ($request->status == 'Diambil' && is_null($transaction->picked_up_at)) {
                $pickedUpAt = now(); // Isi dengan waktu sekarang
            }
            // Opsional: Jika status dikembalikan dari 'Diambil' ke 'Dikerjakan' (karena salah klik), kita kosongkan lagi
            elseif ($request->status != 'Diambil') {
                $pickedUpAt = null;
            }

            // ## LANGKAH 2: UPDATE TRANSAKSI UTAMA ##
            $finalData = array_merge($transactionData, [
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price ?? 0,
                'total_price' => $serviceTotal,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $due_amount,
                'status' => $request->status,
                'picked_up_at' => $pickedUpAt,
            ]);

            $transaction->update($finalData);

            // ## LANGKAH 3: SINKRONISASI ITEMS ##
            $submittedItemIds = [];
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    $item_id = $itemData['id'] ?? null;
                    $namaKomponen = $itemData['manual_service_name'] ?? (isset($itemData['service_id']) ? Service::find($itemData['service_id'])->name : 'Error');
                    $item = TailorTransactionItem::updateOrCreate(
                        ['id' => $item_id, 'tailor_transaction_id' => $transaction->id],
                        [
                            'service_type_id' => $itemData['service_type_id'],
                            'service_id' => $itemData['service_id'] ?? null,
                            'nama_komponen' => $namaKomponen,
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                            'subtotal' => floatval($itemData['quantity']) * floatval($itemData['price']),
                        ]
                    );
                    $submittedItemIds[] = $item->id;
                }
            }
            $transaction->items()->whereNotIn('id', $submittedItemIds)->delete();


            // ## LANGKAH 5: SINKRONISASI PRODUK, STOK & PROFIT PRODUK ##
            $oldSoldProducts = $transaction->soldProducts->keyBy('product_id');
            $submittedProductIds = [];
            $newProductsData = $request->input('products', []);

            // 5.1. Proses produk yang disubmit
            foreach ($newProductsData as $productId => $productData) {
                $submittedProductIds[] = $productId;
                $product = Product::find($productId);
                if (!$product) continue;

                $oldSoldProduct = $oldSoldProducts->get($productId);
                $oldQuantity = $oldSoldProduct ? $oldSoldProduct->quantity : 0;
                $newQuantity = $productData['quantity'];
                $quantityDifference = $oldQuantity - $newQuantity; // Stok dikembalikan jika positif, dikurangi jika negatif

                // Sesuaikan stok
                $product->increment('product_qty', $quantityDifference);

                // Update atau buat data produk terjual
                $transaction->soldProducts()->updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'product_name' => $product->name,
                        'quantity' => $newQuantity,
                        'price' => $productData['price'],
                        'subtotal' => $newQuantity * $productData['price'],
                    ]
                );
            }

            // 5.2. Proses produk yang dihapus
            $productIdsToDelete = $oldSoldProducts->keys()->diff($submittedProductIds);
            foreach ($productIdsToDelete as $productId) {
                $soldProductToDelete = $oldSoldProducts->get($productId);
                $product = Product::find($productId);
                if ($product) {
                    // Kembalikan stok
                    $product->increment('product_qty', $soldProductToDelete->quantity);
                }
                // Hapus record
                $soldProductToDelete->delete();
            }

            // 5.3 Sinkronisasi profit produk
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransactionProduct::class)
                ->delete();

            if ($request->status == 'Diambil' && !empty($newProductsData)) {
                foreach ($newProductsData as $productId => $productData) {
                    $product = Product::find($productId);
                    if ($product) {
                        $totalProfit = ($productData['price'] - $product->modal) * $productData['quantity'];
                        if ($totalProfit > 0) {
                            ProfitDistribution::create([
                                'transaction_id'   => $transaction->id,
                                'transaction_type' => TailorTransactionProduct::class,
                                'amount'           => $totalProfit,
                            ]);
                        }
                    }
                }
            }


            // ## LANGKAH 6: SINKRONISASI KOMISI & PROFIT JASA ##
            $shouldHaveCommission = $request->work_type == 'Internal' && $tailor_commission > 0 && in_array($request->status, ['Selesai', 'Diambil']);
            $existingCommission = TailorCommission::where('tailor_transaction_id', $transaction->id)->first();

            if ($shouldHaveCommission) {
                if ($existingCommission) {
                    // Jika komisi seharusnya ada & sudah ada -> UPDATE
                    $existingCommission->update(['amount' => $tailor_commission, 'user_id' => $request->tailor_id]);
                } else {
                    // Jika komisi seharusnya ada & belum ada -> CREATE
                    TailorCommission::create([
                        'tailor_transaction_id' => $transaction->id,
                        'user_id' => $request->tailor_id,
                        'amount' => $tailor_commission,
                    ]);
                }
            } else {
                if ($existingCommission) {
                    // Jika komisi seharusnya TIDAK ada tapi di DB ada
                    if ($existingCommission->payroll_id) {
                        // PENTING: Jangan hapus komisi yang sudah dibayar!
                        throw new \Exception('Tidak dapat mengubah status karena komisi untuk transaksi ini sudah dibayarkan.');
                    }
                    // Jika belum dibayar, aman untuk dihapus
                    $existingCommission->delete();
                }
            }

            // --- SINKRONISASI DISTRIBUSI PROFIT ---
            $profitToDistribute = ($request->work_type == 'Internal') ? $owner_profit : $profit_toko;
            $shouldDistributeProfit = $profitToDistribute > 0 && ($request->status == 'Diambil');

            // Hapus distribusi profit lama, karena ini tidak memiliki status pembayaran tersendiri
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->delete();

            // Buat ulang HANYA jika kondisi terpenuhi
            if ($shouldDistributeProfit) {
                ProfitDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'transaction_type' => TailorTransaction::class,
                    'amount'           => $profitToDistribute,
                ]);
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
        DB::beginTransaction();
        try {

            $transaction = TailorTransaction::with('commission')->findOrFail($id);

            if ($transaction->commission && $transaction->commission->payroll_id) {
                throw new \Exception('Transaksi ini tidak dapat dihapus karena komisinya sudah dibayarkan.');
            }

            // Hapus distribusi profit terkait
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->orWhere('transaction_type', TailorTransactionProduct::class)
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
            return redirect()->route('all.tailor')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = ['message' => $e->getMessage(), 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }
    }
}
