# Spesifikasi: Dua Penjahit dalam Satu Transaksi Jahit + Split Komisi

Status: Draft (menunggu implementasi)
Tanggal: 2026-07-08
Area: Tailorshop / Transaksi Jahit (Internal)

## 1. Ringkasan

Saat ini satu `TailorTransaction` hanya bisa ditugaskan ke **satu** penjahit (`tailor_transactions.tailor_id`). Pada praktiknya, satu orderan jahit kadang dikerjakan dalam dua tahapan oleh **dua penjahit berbeda**. Fitur ini memungkinkan satu transaksi Internal menetapkan **hingga dua penjahit** dan **membagi komisi** (porsi 2/3 penjahit) di antara keduanya berdasarkan **bobot persentase**.

### Keputusan hasil wawancara (menjadi acuan wajib)

| Aspek | Keputusan |
|---|---|
| Metode split komisi | Persentase per penjahit (bobot), mis. 60/40 |
| Jumlah penjahit maksimum | Tepat 2 (penjahit utama wajib, penjahit kedua **opsional**) |
| Rasio bagi hasil | Pool tetap: owner **1/3**, penjahit **2/3**. Hanya porsi 2/3 yang dibagi antar dua penjahit |
| Cakupan | **Hanya `work_type = 'Internal'`**. Eksternal tidak berubah (1 supplier, tanpa komisi) |
| Visibilitas role Tailor | Transaksi muncul untuk **kedua** penjahit (utama & kedua) |
| Input bobot | Per transaksi, muncul saat penjahit kedua dipilih, **default 50/50**, wajib total = 100% |
| Edit saat komisi sebagian terbayar | **Kunci seluruh transaksi** bila ada satu baris komisi ber-`payroll_id` |
| Pembulatan | **Round masing-masing** bagian (diterima meleset ±1 rupiah dari pool) |
| Tampilan penjahit ke-2 | Halaman detail (show) internal + rincian payroll |
| Automated test | Ya, tulis Pest feature test |

## 2. Perilaku saat ini (current flow)

Referensi: `app/Http/Controllers/Backend/TailorTransactionController.php`.

1. **Store/Update (Internal)** menghitung `total_profit = serviceTotal − cost_price`, lalu `owner_profit = total_profit × 1/3` dan `tailor_commission = total_profit × 2/3`. `tailor_id` diisi dari request, `supplier_id = null`.
2. **Komisi** ditulis ke `tailor_commissions` (satu baris per transaksi) berisi `tailor_transaction_id`, `user_id` (penjahit), `amount`, `payroll_id`. Dibuat hanya jika `status ∈ {Selesai, Diambil}`.
3. **Profit owner** (1/3) ditulis ke `profit_distributions` (`transaction_type = TailorTransaction::class`, `realized_at = picked_up_at`) hanya jika `status = 'Diambil'`.
4. **Proteksi pembayaran**: pada update/destroy, jika komisi punya `payroll_id`, perubahan/penghapusan yang membatalkan komisi ditolak (`throw \Exception`).
5. **Visibilitas** (`index`, `AdminController` dashboard): role `Tailor` difilter `where('tailor_id', $user->id)`.
6. **Payroll** (`PayrollController::calculate/store/show/destroy`) meng-agregasi komisi **per `user_id`** dari `tailor_commissions` (`whereNull('payroll_id')`, rentang `created_at`) — sudah mendukung banyak baris komisi per transaksi selama masing-masing baris punya `user_id` benar.

## 3. Data model

### 3.1 Tabel `tailor_transactions` — migration baru (additive, backward-compatible)

Tambah kolom (semua nullable agar transaksi lama tetap valid):

- `secondary_tailor_id` — `unsignedBigInteger` nullable, FK `users(id)` `onDelete('set null')`, `after('tailor_id')`.
- `primary_tailor_pct` — `decimal(5,2)` nullable (persen bagian penjahit utama; `100.00` saat penjahit tunggal, atau nilai split saat dua penjahit).
- `secondary_tailor_pct` — `decimal(5,2)` nullable (persen bagian penjahit kedua; `null`/`0` saat penjahit tunggal).

Alasan tidak memakai pivot many-to-many: keputusan **tepat 2 penjahit** (bukan N). Dua kolom lebih sederhana, dan `tailor_id` tetap menjadi "penjahit utama" sehingga seluruh report/filter lama yang memakai `tailor_id` tetap bekerja tanpa perubahan skema di sana.

### 3.2 Tabel `tailor_commissions` — tidak diubah

Sudah mendukung banyak baris per transaksi dan sudah dipakai payroll berbasis `user_id`. Fitur ini menulis **1 baris** (penjahit tunggal) atau **2 baris** (dua penjahit) per transaksi.

Catatan pre-existing (di luar scope, jangan diperbaiki di task ini kecuali menghalangi): relasi `TailorCommission::user()` menunjuk kolom `tailor_id` padahal kolom sebenarnya `user_id`. Karena view payroll & controller mengakses lewat `user_id`/query eksplisit, ini tidak dipakai di jalur fitur ini. Jangan gunakan relasi `user()` tanpa memperbaikinya.

### 3.3 Model

`app/Models/TailorTransaction.php`:

- Tambah `secondaryTailor()` → `belongsTo(User::class, 'secondary_tailor_id')`.
- Tambah `commissions()` → `hasMany(TailorCommission::class, 'tailor_transaction_id')`.
- Pertahankan `commission()` (`hasOne`) untuk kompatibilitas view yang belum diubah **atau** ubah seluruh pemakainya. Wajib: setiap tempat yang memutuskan "boleh edit/hapus?" harus memeriksa **semua** baris komisi (lihat §5.3), bukan hanya `hasOne`.

## 4. UI

### 4.1 Form create & edit (`resources/views/admin/backend/tailor/{create,edit}.blade.php`)

Di blok `#internal_tailor_div` (saat ini berisi `select[name=tailor_id]`):

1. Ubah label penjahit utama menjadi "Penjahit Utama".
2. Tambah select opsional `secondary_tailor_id` ("Penjahit Kedua (opsional)") berisi daftar `$tailors` yang sama, dengan opsi kosong "— Tidak ada —".
3. Tambah dua input persen: `primary_tailor_pct` & `secondary_tailor_pct`, **tersembunyi** selama penjahit kedua belum dipilih. Saat penjahit kedua dipilih → tampilkan, prefill `50` / `50`.
4. Alpine.js/JS: menjaga `primary_tailor_pct = 100 − secondary_tailor_pct` (dua arah) sehingga selalu berjumlah 100 dan mengurangi salah input. Validasi final tetap di backend.
5. Semua field penjahit kedua & persen hanya relevan saat `work_type = Internal` (ikut logika show/hide `#internal_tailor_div` yang sudah ada). Saat Eksternal, field ini tidak dikirim/diabaikan.
6. Pada `edit.blade.php`, prefill nilai dari transaksi (`secondary_tailor_id`, `primary_tailor_pct`, `secondary_tailor_pct`).

### 4.2 Halaman detail (`resources/views/admin/backend/tailor/show.blade.php`)

Baris "Penjahit" (saat ini `show.blade.php:32`) menjadi:

- Penjahit tunggal: tampil seperti sekarang.
- Dua penjahit: tampilkan keduanya beserta persen dan nominal komisi masing-masing (mis. "Penjahit: A (60% · Rp X), B (40% · Rp Y)"). Nominal diambil dari relasi `commissions` bila sudah terbentuk; jika komisi belum terbentuk (status belum Selesai/Diambil), tampilkan estimasi dari persen × pool atau cukup persen saja.

Controller `show()` perlu meng-eager-load `secondaryTailor` dan `commissions.` (Sesuaikan `with([...])`.)

### 4.3 Daftar (`index.blade.php`) dan Nota PDF/WhatsApp

**Di luar scope tampilan** (tidak dipilih saat wawancara): kolom penjahit di index boleh tetap menampilkan penjahit utama saja; nota PDF & pesan WhatsApp tidak menampilkan penjahit (memang tidak menampilkannya sekarang). Tidak ada perubahan wajib di sini selain yang diperlukan agar tidak error.

## 5. Logika backend (controller)

Berkas: `app/Http/Controllers/Backend/TailorTransactionController.php`.

### 5.1 Validasi (store & update), hanya berlaku saat `work_type = Internal`

- `tailor_id` — `required_if:work_type,Internal|exists:users,id`.
- `secondary_tailor_id` — `nullable|exists:users,id|different:tailor_id`.
- `primary_tailor_pct`, `secondary_tailor_pct` — `required_with:secondary_tailor_id|numeric|min:0|max:100`.
- Aturan tambahan (custom): jika `secondary_tailor_id` terisi, maka `primary_tailor_pct + secondary_tailor_pct` harus == `100`. Jika tidak persis 100, tolak dengan pesan Bahasa Indonesia.
- Jika `secondary_tailor_id` kosong → paksa `primary_tailor_pct = 100`, `secondary_tailor_pct = null`.

### 5.2 Perhitungan komisi

```
total_profit   = serviceTotal - cost_price          // tidak berubah
owner_profit   = total_profit * (1/3)               // tidak berubah (ProfitDistribution owner)
tailor_pool    = total_profit * (2/3)

if (secondary_tailor_id kosong):
    commission_primary   = tailor_pool
    // tidak ada baris kedua
else:
    commission_primary   = round(tailor_pool * primary_tailor_pct   / 100)
    commission_secondary = round(tailor_pool * secondary_tailor_pct / 100)
```

Round masing-masing (diterima selisih ±1 rupiah terhadap `tailor_pool`; lihat §8 Risiko).

Simpan `primary_tailor_pct` & `secondary_tailor_pct` (dan `secondary_tailor_id`) ke `tailor_transactions` bersama field lain.

### 5.3 Sinkronisasi baris `tailor_commissions`

Komisi dibuat/di-update hanya bila `status ∈ {Selesai, Diambil}` dan `tailor_commission > 0` (mengikuti aturan sekarang).

- **Store**: buat 1 baris (`user_id = tailor_id`, `amount = commission_primary`). Jika ada penjahit kedua & `commission_secondary > 0`: buat baris kedua (`user_id = secondary_tailor_id`, `amount = commission_secondary`).
- **Update — proteksi terbayar (kunci seluruh transaksi)**: sebelum menyentuh komisi, cek apakah **ada** baris komisi transaksi ini dengan `payroll_id != null`. Jika ada dan perubahan akan mengubah nilai/penjahit/split/menurunkan status keluar dari {Selesai, Diambil} → `throw \Exception('Tidak dapat mengubah transaksi karena komisi sudah dibayarkan.')` (rollback). (Konsisten & memperluas cek `payroll_id` yang ada di `update()`/`destroy()`.)
- **Update — belum terbayar**: rekonsiliasi baris menjadi tepat sesuai state baru (0/1/2 baris). Pola aman: hapus semua baris komisi transaksi yang `payroll_id == null` lalu buat ulang sesuai perhitungan. (Baris terbayar tidak akan pernah tersentuh karena sudah diblok di atas.)
- **Destroy**: tolak jika **ada** baris komisi ber-`payroll_id` (ganti cek `commission->payroll_id` menjadi cek `commissions()->whereNotNull('payroll_id')->exists()`). Hapus seluruh `tailor_commissions` transaksi (sudah ada `TailorCommission::where('tailor_transaction_id', ...)->delete()`).

### 5.4 ProfitDistribution owner (1/3) — tidak berubah

Tetap satu baris `transaction_type = TailorTransaction::class`, `amount = owner_profit`, `realized_at = picked_up_at`, hanya saat `status = 'Diambil'`. Split penjahit **tidak** memengaruhi porsi owner.

### 5.5 Visibilitas role Tailor (kedua penjahit melihat transaksi)

- `TailorTransactionController::index` — ganti `where('tailor_id', $user->id)` menjadi:
  `where(fn($q) => $q->where('tailor_id',$user->id)->orWhere('secondary_tailor_id',$user->id))`.
- `AdminController` dashboard `assignedJobs` (`:170`) & `completedJobsThisMonth` (`:174`) — terapkan filter OR yang sama agar penjahit kedua ikut terhitung. (Perubahan basis metrik yang disengaja, sesuai keputusan wawancara.)

## 6. Bagian yang TIDAK termasuk scope

- Lebih dari 2 penjahit (N penjahit) / tabel pivot many-to-many.
- Split komisi untuk `work_type = Eksternal` / outsourcing.
- Menampilkan penjahit di nota PDF pelanggan & pesan WhatsApp.
- Kolom penjahit ganda di tabel index (boleh tetap penjahit utama).
- Mengubah rumus owner 1/3 : penjahit 2/3.
- Split per komponen/item atau per tahapan berbasis nominal manual (ditolak saat wawancara; metode final = persentase).
- Memperbaiki relasi `TailorCommission::user()` yang salah kolom (pre-existing; catat saja).
- Memindahkan komisi lintas periode payroll / mengubah agregasi payroll (sudah kompatibel).

## 7. Acceptance criteria

1. Membuat transaksi Internal dengan **1 penjahit** berperilaku identik dengan sekarang: satu baris komisi = `total_profit × 2/3`, owner 1/3 saat Diambil.
2. Membuat transaksi Internal dengan **2 penjahit** & default 50/50 menghasilkan dua baris `tailor_commissions` dengan `user_id` masing-masing dan `amount` ≈ 50% × pool (round masing-masing).
3. Split **custom** (mis. 70/30) menghasilkan pembagian sesuai persen; validasi menolak submit bila total persen ≠ 100.
4. Menyimpan penjahit kedua = penjahit utama ditolak (`different:tailor_id`).
5. Persen tersimpan di transaksi (`primary_tailor_pct`, `secondary_tailor_pct`) dan ter-prefill benar saat edit.
6. Payroll mingguan tiap penjahit hanya menarik `amount` bagiannya sendiri (agregasi per `user_id` tetap benar untuk kedua penjahit).
7. Jika salah satu komisi sudah `payroll_id` (terbayar), setiap edit yang mengubah nilai/penjahit/split atau menurunkan status ditolak dengan pesan yang jelas; tidak ada baris terbayar yang berubah/terhapus.
8. Menurunkan status ke non-{Selesai,Diambil} pada transaksi yang belum terbayar menghapus **kedua** baris komisi (rekonsiliasi ke 0 baris).
9. User role `Tailor` melihat transaksi di daftarnya baik sebagai penjahit utama **maupun** kedua; dashboard `assignedJobs`/`completedJobsThisMonth` ikut menghitung keduanya.
10. Halaman detail (show) menampilkan kedua penjahit beserta persen & nominal komisinya.
11. Migration `up()` dan `down()` berjalan bersih di MySQL maupun SQLite (test suite). Transaksi lama tetap valid (kolom baru nullable).
12. Pest feature test untuk skenario di §9 lulus; `php artisan test` hijau; `./vendor/bin/pint` bersih.

## 8. Risiko & trade-off

- **Selisih pembulatan ±1 rupiah**: keputusan "round masing-masing" berarti `commission_primary + commission_secondary` bisa berbeda 1 rupiah dari `tailor_pool`. Diterima secara eksplisit; owner tetap 1/3 penuh sehingga tidak memengaruhi porsi owner. Bila di masa depan diinginkan penjumlahan persis, ganti ke pola "penjahit utama serap sisa".
- **Perubahan basis metrik dashboard**: `assignedJobs`/`completedJobsThisMonth` kini menghitung penjahit kedua. Angka historis per-penjahit bisa naik dibanding sebelumnya — ini disengaja.
- **Kunci seluruh transaksi saat sebagian terbayar** dapat terasa kaku bila admin ingin sekadar mengubah data non-komisi setelah salah satu penjahit dibayar. Trade-off keamanan yang dipilih; alternatif granular tidak diambil.
- **Konsistensi periode payroll**: kedua baris komisi memakai `created_at` yang sama, sehingga masuk periode payroll yang sama — perilaku wajar.
- **Interaksi dengan konvensi `realized_at`/`picked_up_at`**: fitur tidak mengubah tanggal realisasi; owner profit & realisasi omzet tetap pada `picked_up_at`. Tidak ada write baru ke `profit_distributions` selain yang sudah ada.

## 9. Automated test (Pest, `tests/Feature`)

Buat `tests/Feature/TailorTransactionCommissionSplitTest.php`. Gunakan seeding role `Tailor` + user penjahit. Skenario minimal:

1. `store` Internal 1 penjahit, status Diambil → 1 baris komisi = round(pool), owner profit 1/3 ada.
2. `store` Internal 2 penjahit 50/50 → 2 baris komisi, jumlah ≈ pool, `user_id` masing-masing benar.
3. `store` Internal 2 penjahit 70/30 → nominal sesuai persen.
4. Validasi: total persen ≠ 100 ditolak; `secondary_tailor_id == tailor_id` ditolak.
5. `update` menaikkan/menurunkan status: komisi terbentuk saat masuk {Selesai,Diambil}, terhapus saat keluar (belum terbayar).
6. Proteksi terbayar: set `payroll_id` pada satu baris, lalu `update` yang mengubah split/nilai → gagal (exception/redirect back), baris terbayar tidak berubah.
7. Visibilitas: user penjahit kedua `index` melihat transaksi tersebut.

## 10. Verifikasi end-to-end (manual)

1. `php artisan migrate` (MySQL `inventory`) → kolom baru muncul, data lama utuh.
2. Buat transaksi Internal, pilih penjahit A saja, status Diambil → cek `tailor_commissions` 1 baris; detail menampilkan komisi A; owner profit muncul di distribusi profit.
3. Buat transaksi Internal, pilih A + B, biarkan 50/50, status Selesai → cek 2 baris komisi; ubah ke 70/30 lalu simpan → nominal berubah; total persen 60/50 → ditolak dengan pesan.
4. Login sebagai B (role Tailor) → transaksi tampil di daftarnya; dashboard menghitungnya.
5. Jalankan payroll untuk A dan untuk B pada periode terkait → masing-masing hanya menarik bagiannya; setelah dibayar, `payroll_id` terisi.
6. Coba edit transaksi tsb (ubah harga/split) → ditolak "komisi sudah dibayarkan"; hapus payroll → tautan komisi lepas (`payroll_id = null`), edit kembali diperbolehkan.
7. `php artisan test` hijau; `./vendor/bin/pint` bersih.

## 10a. Catatan implementasi (terisi saat implementasi 2026-07-08)

- **Logika inti split diekstrak** ke `app/Support/TailorCommissionCalculator.php` (murni, tanpa dependensi framework) agar dapat diuji tanpa DB. Controller (`resolveCommissionSplit`, `validateCommissionSplit`) mendelegasikan ke sana.
- **Test yang ditulis = Unit test** `tests/Unit/TailorCommissionCalculatorTest.php` (7 kasus: tunggal, 50/50, 70/30, round masing-masing, bagian nol, pool nol/negatif, validasi 100%) — **hijau**. Feature test full-HTTP (store/update) **tidak dapat dijalankan** di environment ini karena tiga hambatan pra-eksisting yang **di luar scope**:
  1. `pdo_sqlite`/`sqlite3` tidak diaktifkan di PHP CLI (bisa dipaksa via `php -d extension=...`).
  2. Migration lama `2026_01_13_161918_sync_picked_up_date_in_tailor_transactions` memakai sintaks `UPDATE ... alias` khas MySQL yang tidak portable ke sqlite → seluruh `RefreshDatabase` gagal.
  3. Schema drift: tidak ada migration `service_types`, dan `tailor_transaction_items` (migration) belum punya kolom `service_type_id`/`nama_komponen`/`service_id` nullable yang dipakai controller. Schema produksi sudah diubah manual di luar migration.
  Ketiganya harus diselesaikan lebih dulu (task terpisah) sebelum feature test HTTP untuk domain jahit bisa berjalan. Sampai saat itu, verifikasi integrasi memakai §10 (manual).
- Sebelum menjalankan artisan, `vendor/composer` sempat mereferensikan `app/Helpers/SettingHelper.php` (sisa branch lain) sehingga artisan mati total; diperbaiki dengan `composer dump-autoload --no-scripts`.

## 11. Berkas yang kemungkinan berubah

- Migration baru: `database/migrations/xxxx_add_secondary_tailor_to_tailor_transactions_table.php`.
- `app/Models/TailorTransaction.php` (relasi `secondaryTailor`, `commissions`).
- `app/Http/Controllers/Backend/TailorTransactionController.php` (`store`, `update`, `destroy`, `show`, `index`, validasi).
- `app/Http/Controllers/AdminController.php` (filter `assignedJobs`, `completedJobsThisMonth`).
- `resources/views/admin/backend/tailor/create.blade.php`, `edit.blade.php`, `show.blade.php` (field penjahit kedua + persen; tampilan detail).
- `tests/Feature/TailorTransactionCommissionSplitTest.php` (baru).
- (Opsional) `resources/views/admin/backend/payroll/show.blade.php` bila ingin menandai penjahit ke-1/ke-2 pada rincian — hanya jika tidak menambah query baru yang berat.
