<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\TailorCommission;
use App\Models\TailorTransaction;
use App\Models\ProfitDistribution;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use App\Models\TailorTransactionItem;

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

        try {
            DB::beginTransaction();

            // ## LANGKAH 1: HITUNG TOTAL HARGA DARI ITEM TERLEBIH DAHULU ##
            $total_price = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $total_price += floatval($item['quantity']) * floatval($item['price']);
                }
            }

            // Variabel untuk menyimpan data yang akan disimpan
            $transactionData = [];

            // ## LANGKAH 2: LAKUKAN PERCABANGAN DAN PERHITUNGAN ##
            if ($request->work_type == 'Internal') {
                // --- PROSES INTERNAL ---
                $total_profit = $total_price - ($request->cost_price ?? 0);
                $owner_profit = $total_profit * (1 / 3);
                $tailor_commission = $total_profit * (2 / 3);

                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'supplier_id' => null,
                    'profit' => $total_profit,
                ];

                // Simpan komisi jika ada
                if ($tailor_commission > 0) {
                    // Kita simpan setelah transaksi dibuat untuk mendapatkan ID
                }

                // Distribusikan profit owner jika ada
                if ($owner_profit > 0) {
                    // Kita distribusikan setelah transaksi dibuat
                }
            } else { // Jika pengerjaan Eksternal
                // --- PROSES EKSTERNAL ---
                $profit_toko = $total_price - ($request->cost_price ?? 0);

                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'profit' => $profit_toko, // Profit toko adalah profit utama di kasus ini
                ];

                // Distribusikan profit toko jika ada
                if ($profit_toko > 0) {
                    // Kita distribusikan setelah transaksi dibuat
                }
            }

            // ## LANGKAH 3: GABUNGKAN DATA & BUAT TRANSAKSI UTAMA ##
            // Hitung sisa bayar dengan total_price yang sudah benar
            $due_amount = $total_price - ($request->paid_amount ?? 0);

            // Gabungkan data umum dengan data spesifik dari percabangan
            $finalData = array_merge($transactionData, [
                'transaction_code' => 'JAHIT-' . Carbon::now()->format('dm') . mt_rand(00, 99),
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'cost_price' => $request->cost_price ?? 0,
                'total_price' => $total_price, // <- Nilai sudah benar
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => $due_amount,   // <- Nilai sudah benar
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

            // Jalankan kembali logika penyimpanan turunan yang butuh ID transaksi
            if ($request->work_type == 'Internal' && isset($tailor_commission) && $tailor_commission > 0) {
                TailorCommission::create([
                    'tailor_transaction_id' => $transaction->id,
                    'user_id' => $request->tailor_id,
                    'amount' => $tailor_commission,
                ]);
            }

            if ($transaction->profit > 0) {
                $profitToDistribute = ($request->work_type == 'Internal') ? ($transaction->profit * (1 / 3)) : $transaction->profit;
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
        $transaction = TailorTransaction::with(['customer', 'items.service'])->findOrFail($id);
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
        try {
            DB::beginTransaction();

            $transaction = TailorTransaction::findOrFail($id);

            $total_price = 0;
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    $total_price += floatval($item['quantity']) * floatval($item['price']);
                }
            }

            // Variabel untuk menyimpan data spesifik berdasarkan tipe pengerjaan
            $transactionData = [];

            // 3. Lakukan percabangan logika berdasarkan tipe pengerjaan
            if ($request->work_type == 'Internal') {
                // ... (Logika profit internal Anda)
                $total_profit = $total_price;
                $transactionData = [
                    'work_type' => 'Internal',
                    'tailor_id' => $request->tailor_id,
                    'supplier_id' => null,
                    'cost_price' => 0,
                    'profit' => $total_profit,
                ];
            } else { // Eksternal
                // ... (Logika profit eksternal Anda)
                $cost_price = $request->cost_price ?? 0;
                $profit_toko = $total_price - $cost_price;
                $transactionData = [
                    'work_type' => 'Eksternal',
                    'tailor_id' => null,
                    'supplier_id' => $request->supplier_id,
                    'cost_price' => $cost_price,
                    'profit' => $profit_toko,
                ];
            }

            // 4. Update data transaksi utama
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

            // ## LANGKAH 4: SINKRONISASI ITEM (LOGIKA BARU) ##
            $submittedItemIds = []; // Untuk menampung ID item yang dikirim
            if ($request->has('items')) {
                foreach ($request->items as $key => $itemData) {
                    $item_id = $itemData['id'] ?? null; // Ambil ID item jika ada

                    $namaKomponen = '';
                    $serviceId = null;

                    if (isset($itemData['manual_service_name']) && !empty($itemData['manual_service_name'])) {
                        $namaKomponen = $itemData['manual_service_name'];
                        $serviceId = null;
                    } else if (isset($itemData['service_id'])) {
                        $serviceId = $itemData['service_id'];
                        $service = Service::find($serviceId);
                        $namaKomponen = $service ? $service->name : 'Komponen Dihapus';
                    }

                    $itemDetails = [
                        'service_type_id' => $itemData['service_type_id'],
                        'service_id' => $serviceId,
                        'nama_komponen' => $namaKomponen,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'subtotal' => floatval($itemData['quantity']) * floatval($itemData['price']),
                    ];

                    // Update jika ada ID, atau buat baru jika tidak ada ID
                    $item = TailorTransactionItem::updateOrCreate(
                        ['id' => $item_id, 'tailor_transaction_id' => $transaction->id],
                        $itemDetails
                    );

                    $submittedItemIds[] = $item->id; // Kumpulkan ID yang sudah diproses
                }
            }

            // Hapus item dari database yang tidak ada dalam daftar kiriman
            $transaction->items()->whereNotIn('id', $submittedItemIds)->delete();

            // ## LANGKAH 5: HAPUS DAN BUAT ULANG DATA TURUNAN (KOMISI & DISTRIBUSI PROFIT) ##
            TailorCommission::where('tailor_transaction_id', $transaction->id)->delete();
            ProfitDistribution::where('transaction_id', $transaction->id)
                ->where('transaction_type', TailorTransaction::class)
                ->delete();

            if ($request->work_type == 'Internal' && isset($tailor_commission) && $tailor_commission > 0) {
                TailorCommission::create([
                    'tailor_transaction_id' => $transaction->id,
                    'user_id' => $request->tailor_id,
                    'amount' => $tailor_commission,
                ]);
            }

            if ($transaction->profit > 0) {
                $profitToDistribute = ($request->work_type == 'Internal') ? ($transaction->profit * (1 / 3)) : $transaction->profit;
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
            $errorMessage = 'Gagal memperbarui: ' . $e->getMessage();
            $notification = ['message' => $errorMessage, 'alert-type' => 'error'];
            return redirect()->back()->with($notification)->withInput();
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
