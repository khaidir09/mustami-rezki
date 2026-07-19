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
use App\Support\TailorCommissionCalculator;
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
            // Penjahit melihat transaksi di mana ia menjadi penjahit utama maupun penjahit kedua.
            $query->where(function ($q) use ($user) {
                $q->where('tailor_id', $user->id)
                    ->orWhere('secondary_tailor_id', $user->id);
            });
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
            'tailor_id' => 'required_if:work_type,Internal|nullable|exists:users,id',
            'secondary_tailor_id' => 'nullable|exists:users,id|different:tailor_id',
            'primary_tailor_pct' => 'nullable|numeric|min:0|max:100|required_with:secondary_tailor_id',
            'secondary_tailor_pct' => 'nullable|numeric|min:0|max:100|required_with:secondary_tailor_id',
        ]);

        if ($pctError = $this->validateCommissionSplit($request)) {
            return redirect()->back()->withInput()->with(['message' => $pctError, 'alert-type' => 'error']);
        }

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
            $commissionRows = [];

            // ## LANGKAH 2: LAKUKAN PERCABANGAN DAN PERHITUNGAN ##
            if ($request->work_type == 'Internal') {
                // --- PROSES INTERNAL ---
                $total_profit = $serviceTotal - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $split = $this->resolveCommissionSplit($request, $tailor_commission);
                $commissionRows = $split['rows'];

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'secondary_tailor_id' => $split['secondary_tailor_id'],
                    'primary_tailor_pct' => $split['primary_pct'],
                    'secondary_tailor_pct' => $split['secondary_pct'],
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];
            } else { // Jika pengerjaan Eksternal
                // --- PROSES EKSTERNAL ---
                $profit_toko = $serviceTotal - ($request->cost_price ?? 0);

                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'secondary_tailor_id' => null,
                    'primary_tailor_pct' => null,
                    'secondary_tailor_pct' => null,
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
                    if (!$product) {
                        // Tolak seluruh transaksi jika produk tidak ditemukan (di-rollback oleh catch)
                        throw new \Exception('Produk dengan ID ' . $productId . ' tidak ditemukan.');
                    }

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

                    // ---- KALKULASI PROFIT PER ITEM ----
                    $totalProfit = ($productData['price'] - $product->modal) * $productData['quantity'];

                    if ($totalProfit > 0 && $request->status === 'Diambil') {
                        ProfitDistribution::create([
                            'transaction_id'   => $transaction->id,
                            'transaction_type' => TailorTransactionProduct::class, // Menggunakan class constant lebih aman
                            'amount'           => $totalProfit,
                            'realized_at'      => $pickedUpAt,
                        ]);
                    }
                }
            }

            $transaction->save();

            $isCommissionable = in_array($request->status, ['Selesai', 'Diambil']);
            $isProfitDistributable = ($request->status == 'Diambil');


            if ($request->work_type == 'Internal' && $isCommissionable) {
                foreach ($commissionRows as $row) {
                    TailorCommission::create([
                        'tailor_transaction_id' => $transaction->id,
                        'user_id' => $row['user_id'],
                        'amount' => $row['amount'],
                    ]);
                }
            }

            $profitToDistribute = ($request->work_type == 'Internal') ? $owner_profit : $profit_toko;

            // 4. Distribusikan profit HANYA JIKA ada profit & statusnya 'Diambil'
            if ($profitToDistribute > 0 && $isProfitDistributable) {
                ProfitDistribution::create([
                    'transaction_id'   => $transaction->id,
                    'transaction_type' => TailorTransaction::class,
                    'amount'           => $profitToDistribute,
                    'realized_at'      => $pickedUpAt,
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
        $transaction = TailorTransaction::with([
            'customer',
            'items.service',
            'soldProducts.product',
            'tailor',
            'secondaryTailor',
            'commissions',
        ])->findOrFail($id);
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
        $transaction = TailorTransaction::with('items.service', 'commissions')->findOrFail($id);

        // Komisi yang sudah masuk payroll mengunci field penentu nominal komisi di form
        // (lihat pemeriksaan yang sama di update()).
        $commissionLocked = $transaction->commissions->whereNotNull('payroll_id')->isNotEmpty();

        // Ambil data master untuk dropdown
        $customers = Customer::all();
        $tailors = User::role('Tailor')->get();
        $services = Service::where('is_active', true)->get();
        $types = ServiceType::all();
        $serviceSuppliers = Supplier::where('type', 'Jasa')->get();

        return view('admin.backend.tailor.edit', compact('transaction', 'customers', 'services', 'types', 'tailors', 'serviceSuppliers', 'commissionLocked'));
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
            'secondary_tailor_id' => 'nullable|exists:users,id|different:tailor_id',
            'primary_tailor_pct' => 'nullable|numeric|min:0|max:100|required_with:secondary_tailor_id',
            'secondary_tailor_pct' => 'nullable|numeric|min:0|max:100|required_with:secondary_tailor_id',
            'picked_up_at' => 'nullable|date',
        ]);

        if ($pctError = $this->validateCommissionSplit($request)) {
            return redirect()->back()->withInput()->with(['message' => $pctError, 'alert-type' => 'error']);
        }

        DB::beginTransaction();
        try {
            $transaction = TailorTransaction::with('commissions')->findOrFail($id);

            // Komisi yang sudah masuk payroll tidak boleh berubah nilainya, tetapi transaksinya
            // tetap boleh diedit — owner sering membayar komisi saat status 'Selesai', lalu status
            // baru diubah ke 'Diambil' ketika pelanggan mengambil barangnya.
            $hasPaidCommission = $transaction->commissions->whereNotNull('payroll_id')->isNotEmpty();

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
            $commissionRows = [];

            if ($request->work_type == 'Internal') {
                $total_profit = $serviceTotal - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $split = $this->resolveCommissionSplit($request, $tailor_commission);
                $commissionRows = $split['rows'];

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'secondary_tailor_id' => $split['secondary_tailor_id'],
                    'primary_tailor_pct' => $split['primary_pct'],
                    'secondary_tailor_pct' => $split['secondary_pct'],
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];
            } else { // Eksternal
                $profit_toko = $serviceTotal - ($request->cost_price ?? 0);
                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'secondary_tailor_id' => null,
                    'primary_tailor_pct' => null,
                    'secondary_tailor_pct' => null,
                    'supplier_id' => $request->supplier_id,
                    'profit' => $profit_toko,
                ];
            }

            $shouldHaveCommission = $request->work_type == 'Internal' && in_array($request->status, ['Selesai', 'Diambil']);

            if ($hasPaidCommission && !$this->commissionRowsUnchanged($transaction->commissions, $commissionRows, $shouldHaveCommission)) {
                throw new \Exception('Komisi transaksi ini sudah dibayarkan, sehingga penjahit, nominal jasa, dan modal tidak dapat diubah. Perubahan status, tanggal, pembayaran pelanggan, dan produk tetap diperbolehkan.');
            }

            // Cek logika: Jika status berubah menjadi 'Diambil'
            if ($request->status == 'Diambil') {
                // Gunakan input dari user jika ada, jika kosong gunakan data lama, jika tidak ada juga baru pakai now()
                $pickedUpAt = $request->picked_up_at ?: ($transaction->picked_up_at ?? now());
            } else {
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
                                'realized_at'      => $pickedUpAt,
                            ]);
                        }
                    }
                }
            }


            // ## LANGKAH 6: SINKRONISASI KOMISI & PROFIT JASA ##
            // Komisi terbayar dibiarkan apa adanya agar tautan payroll-nya tidak putus —
            // nilainya sudah dipastikan tidak berubah oleh pemeriksaan di LANGKAH 1.
            if (!$hasPaidCommission) {
                $transaction->commissions()->delete();

                if ($shouldHaveCommission) {
                    foreach ($commissionRows as $row) {
                        TailorCommission::create([
                            'tailor_transaction_id' => $transaction->id,
                            'user_id' => $row['user_id'],
                            'amount' => $row['amount'],
                        ]);
                    }
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
                    'realized_at'      => $pickedUpAt,
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

            $transaction = TailorTransaction::with('commissions')->findOrFail($id);

            if ($transaction->commissions()->whereNotNull('payroll_id')->exists()) {
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

    /**
     * Apakah hasil perhitungan ulang komisi identik dengan baris komisi yang tersimpan?
     * Dipakai untuk mengizinkan edit transaksi yang komisinya sudah dibayarkan, selama
     * penjahit dan nominalnya tidak berubah. Toleransi 1 rupiah untuk selisih pembulatan.
     *
     * @param  \Illuminate\Support\Collection<int, TailorCommission>  $existing
     * @param  array<int, array{user_id: int, amount: float}>  $newRows
     */
    private function commissionRowsUnchanged($existing, array $newRows, bool $shouldHaveCommission): bool
    {
        if (!$shouldHaveCommission || $existing->count() !== count($newRows)) {
            return false;
        }

        foreach ($newRows as $row) {
            $match = $existing->firstWhere('user_id', $row['user_id']);

            if (!$match || abs((float) $match->amount - (float) $row['amount']) > 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validasi total bobot komisi kedua penjahit harus 100% (hanya saat penjahit kedua diisi & Internal).
     * Mengembalikan pesan error (string) bila tidak valid, atau null bila valid.
     */
    private function validateCommissionSplit(Request $request): ?string
    {
        if ($request->work_type !== 'Internal' || !$request->filled('secondary_tailor_id')) {
            return null;
        }

        if (!TailorCommissionCalculator::percentagesSumTo100($request->primary_tailor_pct, $request->secondary_tailor_pct)) {
            return 'Total persentase komisi kedua penjahit harus 100%.';
        }

        return null;
    }

    /**
     * Ubah request menjadi rincian pembagian komisi (porsi 2/3 penjahit) per penjahit.
     *
     * @return array{secondary_tailor_id: ?int, primary_pct: float, secondary_pct: ?float, rows: array<int, array{user_id: int, amount: float}>}
     */
    private function resolveCommissionSplit(Request $request, float $tailorPool): array
    {
        $secondaryTailorId = $request->filled('secondary_tailor_id') ? (int) $request->secondary_tailor_id : null;

        $result = TailorCommissionCalculator::split(
            $tailorPool,
            (int) $request->tailor_id,
            $secondaryTailorId,
            $request->primary_tailor_pct,
            $request->secondary_tailor_pct
        );

        return [
            'secondary_tailor_id' => $secondaryTailorId,
            'primary_pct' => $result['primary_pct'],
            'secondary_pct' => $result['secondary_pct'],
            'rows' => $result['rows'],
        ];
    }
}
