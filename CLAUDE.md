# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Inventory + tailorshop (penjahit) management system for **Mustami Rezki Tailorshop**, built on Laravel 12 / PHP 8.2. The UI and domain language are **Indonesian** — route names, view text, WhatsApp templates, and many comments are in Bahasa Indonesia (e.g. _jahitan_ = tailoring order, _arus kas_ = cash flow, _distribusi profit_ = profit distribution). Database name is `inventory`.

## Commands

This is a Laragon/Windows environment. PHP, Composer, and Node are on PATH.

```bash
composer dev          # Runs serve + queue:listen + vite concurrently (main dev command)
php artisan serve     # App server only
npm run dev           # Vite dev server (HMR for CSS/JS)
npm run build         # Production asset build

php artisan migrate                 # Apply migrations (MySQL `inventory` db)
php artisan migrate:fresh --seed    # Rebuild schema + seed test user

php artisan test                    # Run full Pest/PHPUnit suite (sqlite :memory:)
php artisan test --filter=ProfileTest          # Single test class/file
php artisan test tests/Feature/Auth/AuthenticationTest.php   # Single file by path
./vendor/bin/pest                   # Run via Pest directly

./vendor/bin/pint     # Format code (Laravel Pint)
php artisan tinker    # REPL
php artisan pail      # Tail application logs
```

## Architecture

**Controller layout.** Business/inventory logic lives under `app/Http/Controllers/Backend/` (one controller per domain area: Product, Purchase, Sale, SaleReturn, Transfer, TailorTransaction, Expense, Production, Payroll, Attendance, Acceptance, FinancialReport, DailyFinancialReport, Report, Role, Supplier, Service, Brand, WareHouse). Account/auth-adjacent controllers (`AdminController`, `ProfileController`, `ProfitDistributionController`) sit at the root `Controllers/` namespace. Auth scaffolding is Laravel Breeze in `Controllers/Auth/`.

**Routing convention is non-RESTful.** Most routes are registered with `Route::controller(X::class)->group(...)` using custom verb-prefixed method names and custom URL/name pairs, e.g. `AllSupplier`/`all.supplier`, `StoreSupplier`/`store.supplier`, `EditSupplier`/`edit.supplier`. Newer controllers (TailorTransaction, Expense, Production, Attendance, Acceptance, Payroll) use conventional `index`/`create`/`store`/`edit`/`update`/`destroy` method names but still keep the custom route names. When adding a feature, follow the pattern of the controller you are extending rather than `Route::resource`. All app routes live in a single `auth` middleware group in `routes/web.php` (~277 lines); there is no separate route-level permission gating despite roles existing.

**Authorization.** Uses `spatie/laravel-permission` (roles + permissions managed through `RoleController`, seeded/edited in the admin UI). Access control is enforced in views/controllers, not via route middleware.

**Domain model relationships.** `protected $guarded = []` is used throughout (mass-assignment is open). Key flows:

- **Inventory movement**: `Purchase`/`PurchaseItem` (incoming), `Sale`/`SaleItem` (outgoing), `ReturnPurchase`/`SaleReturn` (reversals), `Transfer`/`TransferItem` (between `WareHouse`s). Product stock is adjusted inside these controllers.
- **Tailoring orders**: `TailorTransaction` is the central order, with `items()` (`TailorTransactionItem`, the service line items / _komponen_), `soldProducts()` (`TailorTransactionProduct`, store goods sold alongside the job), a `tailor()` (a `User`), and a `commission()` (`TailorCommission`). Grand total = sum of item subtotals + sold-product subtotals. Outsourcing fields tie a transaction to a `Supplier`.
- **HR / finance**: `Attendance` → `Payroll`/`Salary`, `Expense` (categorized), `Production`, `Acceptance`, plus reporting aggregates `FinancialSummary`, `DailyFinancialSummary`, and `ProfitDistribution`.

**PDF & images.** Invoices/reports render via `barryvdh/laravel-dompdf`. Product images are processed with `intervention/image` and stored under `public/upload/` (e.g. `public/upload/productimg/`, `public/upload/expense/`).

**WhatsApp integration.** `app/Helpers/WhatsAppHelper.php` is globally autoloaded (registered in `composer.json` `autoload.files`). `generateTailorInvoiceLink()` builds a `https://wa.me/` deep link with a formatted Indonesian invoice message for a `TailorTransaction` — it normalizes phone numbers to the `62` country code and recomputes the combined service + product total.

**Views.** Blade + Tailwind CSS v3 + Alpine.js, bundled by Vite. Admin theme templates live in `resources/views/admin/`, layouts in `resources/views/layouts/`. A custom Blade directive **`@rupiah($amount)`** (defined in `AppServiceProvider::boot()`) formats numbers as `Rp 1.000.000` — use it for currency display instead of inline `number_format`.

## Conventions

- Currency is always Indonesian Rupiah; format with `@rupiah` in Blade or `number_format($n, 0, ',', '.')` in PHP.
- New user-facing strings should be in Indonesian to match the existing UI.
- Tests run against sqlite in-memory (see `phpunit.xml`); the real app runs on MySQL. Keep migrations portable across both.

### Tailor report "realized revenue" convention

A `TailorTransaction` only counts as **realized** (omzet/pendapatan jahit recognized) once `status = 'Diambil'`, and the recognition date is **`picked_up_at`** — not `transaction_date`, `created_at`, or `updated_at`. Any new revenue/omzet report or dashboard metric over tailoring orders MUST filter `->where('status', 'Diambil')` and range its dates on `picked_up_at`. This is applied consistently across the existing reports:

- `ProfitDistributionController::index` omzet (`ProfitDistributionController.php:36-49`)
- Dashboard omzet jahit, daily & monthly (`AdminController.php:238-263`, `:291-293`, `:344`)
- `DailyFinancialReportController` (`:54-58`, `:189-196`, `:233-234`) and `FinancialReportController` (`:88-92`, `:158-159`)

**Profit aggregates: filter `ProfitDistribution` by `realized_at`, never `created_at`.** `profit_distributions.realized_at` is the dedicated business/realization date (added 2026-06; `created_at` stays as the row insert/audit timestamp). It is set at every `ProfitDistribution::create(...)` from the source transaction's realization date — tailor types from `picked_up_at`, `Sale`/`Production` from their `date`. This keeps profit totals aligned with omzet totals over the same range, and prevents an edit (which deletes & re-creates the profit rows) from shoving an old transaction's profit into the current month. Every read of `ProfitDistribution` ranges on `realized_at` (`ProfitDistributionController.php:25-26`, `AdminController.php:136-138/144-147/299-302`). When adding a new write site, you MUST populate `realized_at`; when adding a new report, filter on it.

Other caveats — do not assume a single date column everywhere:

- **"Completed jobs" counts are a different basis**: `status IN ('Selesai','Diambil')` filtered by `updated_at` (`AdminController.php:140-143`) — a job-throughput metric, not realized revenue.
- **Commission eligibility is broader** than realization: `status IN ('Selesai','Diambil')` (`TailorTransactionController.php:225`, `:480`), whereas profit distribution requires `status='Diambil'` only (`:226`).

## IMPORTANT

- Gunakan package manager yang sudah dipakai proyek.
- Jangan mengganti framework atau dependency utama tanpa persetujuan.
- Jangan melakukan upgrade dependency massal sebagai bagian dari tugas lain.

### Architecture Rules

Sebelum menambahkan implementasi baru:

1. Cari implementasi serupa dalam codebase.
2. Jelaskan pola yang ditemukan.
3. Gunakan kembali abstraction yang ada jika sesuai.
4. Jangan membuat duplicate utility atau duplicate service.

### Coding Conventions

- Ikuti style kode yang sudah digunakan pada file di area yang sedang dikerjakan
- Pertahankan public API yang sudah ada kecuali perubahan breaking memang diminta
- Hindari any, suppress error, empty catch, dan hardcoded configuration.
- Jangan menambahkan komentar yang hanya mengulang isi kode.
- Beri komentar hanya untuk keputusan atau perilaku yang tidak mudah dipahami.
- Jangan melakukan refactor di luar ruang lingkup tugas.

### Database Rules

- Jangan menghapus tabel, kolom, atau data.
- Jangan menjalankan destructive migration tanpa persetujuan.
- Migration harus backward-compatible jika memungkinkan.
- Jangan mengubah migration lama yang sudah pernah digunakan di environment lain.
- Tambahkan migration baru untuk perubahan schema.

### Workflow for Non-Trivial Tasks

Untuk tugas yang memengaruhi beberapa file atau mengubah perilaku aplikasi:

1. Pelajari implementasi yang relevan.
2. Jelaskan current flow.
3. Identifikasi file yang kemungkinan berubah.
4. Tulis implementation plan.
5. Tunggu sampai plan disetujui sebelum mengubah kode.
6. Implementasikan perubahan secara bertahap.
7. Jalankan test dan pemeriksaan terkait.
8. Tinjau git diff.
9. Laporkan: file yang berubah, keputusan teknis, test yang dijalankan, risiko atau pekerjaan lanjutan.

### Definition of Done

Sebuah tugas belum selesai sampai:

- kebutuhan dan acceptance criteria terpenuhi;
- implementasi mengikuti arsitektur yang ada;
- tidak ada perubahan di luar scope;
- test terkait lulus;
- lint dan type check lulus;
- build lulus jika relevan;
- tidak ada credential atau debug code tertinggal;
- git diff sudah diperiksa;
- dokumentasi diperbarui jika perilaku publik berubah.
