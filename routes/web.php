<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\AcceptanceController;
use App\Http\Controllers\Backend\AttendanceController;
use App\Http\Controllers\Backend\ExpenseController;
use App\Http\Controllers\Backend\FinancialReportController;
use App\Http\Controllers\Backend\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SaleController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\ProductionController;
use App\Http\Controllers\Backend\PurchaseController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\TransferController;
use App\Http\Controllers\Backend\SaleReturnController;
use App\Http\Controllers\Backend\ServiceController;
use App\Http\Controllers\Backend\TailorTransactionController;
use App\Http\Controllers\ProfitDistributionController;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [AdminController::class, 'AdminDashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/admin/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');

Route::middleware('auth')->group(function () {

    Route::get('/admin/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/profile/store', [AdminController::class, 'ProfileStore'])->name('profile.store');
    Route::post('/admin/password/update', [AdminController::class, 'AdminPasswordUpdate'])->name('admin.password.update');
});


Route::middleware('auth')->group(function () {
    Route::controller(SupplierController::class)->group(function () {
        Route::get('/all/supplier', 'AllSupplier')->name('all.supplier');
        Route::get('/add/supplier', 'AddSupplier')->name('add.supplier');
        Route::post('/store/supplier', 'StoreSupplier')->name('store.supplier');
        Route::get('/edit/supplier/{id}', 'EditSupplier')->name('edit.supplier');
        Route::post('/update/supplier', 'UpdateSupplier')->name('update.supplier');
        Route::get('/delete/supplier/{id}', 'DeleteSupplier')->name('delete.supplier');
    });


    Route::controller(SupplierController::class)->group(function () {
        Route::get('/all/customer', 'AllCustomer')->name('all.customer');
        Route::get('/add/customer', 'AddCustomer')->name('add.customer');
        Route::post('/store/customer', 'StoreCustomer')->name('store.customer');
        Route::get('/edit/customer/{id}', 'EditCustomer')->name('edit.customer');
        Route::post('/update/customer', 'UpdateCustomer')->name('update.customer');
        Route::get('/delete/customer/{id}', 'DeleteCustomer')->name('delete.customer');
    });


    Route::controller(ProductController::class)->group(function () {
        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::post('/store/category', 'StoreCategory')->name('store.category');
        Route::get('/edit/category/{id}', 'EditCategory');
        Route::post('/update/category', 'UpdateCategory')->name('update.category');
        Route::get('/delete/category/{id}', 'DeleteCategory')->name('delete.category');
    });

    Route::controller(ProductController::class)->group(function () {
        Route::get('/all/product', 'AllProduct')->name('all.product');
        Route::get('/add/product', 'AddProduct')->name('add.product');
        Route::post('/store/product', 'StoreProduct')->name('store.product');
        Route::get('/edit/product/{id}', 'EditProduct')->name('edit.product');
        Route::post('/update/product', 'UpdateProduct')->name('update.product');
        Route::get('/delete/product/{id}', 'DeleteProduct')->name('delete.product');
        Route::get('/details/product/{id}', 'DetailsProduct')->name('details.product');
    });

    Route::controller(PurchaseController::class)->group(function () {
        Route::get('/all/purchase', 'AllPurchase')->name('all.purchase');
        Route::get('/add/purchase', 'AddPurchase')->name('add.purchase');
        Route::get('/purchase/product/search', 'PurchaseProductSearch')->name('purchase.product.search');
        Route::get('/purchase/product-search-for-purchase', 'productSearchForPurchase')->name('purchase.product.search.modal');

        Route::post('/store/purchase', 'StorePurchase')->name('store.purchase');
        Route::get('/edit/purchase/{id}', 'EditPurchase')->name('edit.purchase');
        Route::post('/update/purchase/{id}', 'UpdatePurchase')->name('update.purchase');

        Route::get('/details/purchase/{id}', 'DetailsPurchase')->name('details.purchase');
        Route::get('/invoice/purchase/{id}', 'InvoicePurchase')->name('invoice.purchase');
        Route::get('/delete/purchase/{id}', 'DeletePurchase')->name('delete.purchase');
    });


    Route::controller(SaleController::class)->group(function () {
        Route::get('/all/sale', 'AllSales')->name('all.sale');
        Route::get('/add/sale', 'AddSales')->name('add.sale');
        Route::post('/store/sale', 'StoreSales')->name('store.sale');
        Route::get('/edit/sale/{id}', 'EditSales')->name('edit.sale');
        Route::post('/update/sale/{id}', 'UpdateSales')->name('update.sale');
        Route::get('/delete/sale/{id}', 'DeleteSales')->name('delete.sale');
        Route::get('/details/sale/{id}', 'DetailsSales')->name('details.sale');
        Route::get('/invoice/sale/{id}', 'InvoiceSales')->name('invoice.sale');
    });

    Route::controller(SaleReturnController::class)->group(function () {
        Route::get('/all/sale/return', 'AllSalesReturn')->name('all.sale.return');
        Route::get('/add/sale/return', 'AddSalesReturn')->name('add.sale.return');
        Route::post('/store/sale/return', 'StoreSalesReturn')->name('store.sale.return');
        Route::get('/edit/sale/return/{id}', 'EditSalesReturn')->name('edit.sale.return');
        Route::post('/update/sale/return/{id}', 'UpdateSalesReturn')->name('update.sale.return');

        Route::get('/details/sale/return/{id}', 'DetailsSalesReturn')->name('details.sale.return');
        Route::get('/delete/sale/return/{id}', 'DeleteSalesReturn')->name('delete.sale.return');
    });


    Route::controller(SaleReturnController::class)->group(function () {
        Route::get('/due/sale', 'DueSale')->name('due.sale');
        Route::get('/due/sale/return', 'DueSaleReturn')->name('due.sale.return');
    });


    Route::controller(TransferController::class)->group(function () {
        Route::get('/all/transfer', 'AllTransfer')->name('all.transfer');
        Route::get('/add/transfer', 'AddTransfer')->name('add.transfer');
        Route::post('/store/transfer', 'StoreTransfer')->name('store.transfer');
        Route::get('/edit/transfer/{id}', 'EditTransfer')->name('edit.transfer');
        Route::post('/update/transfer/{id}', 'UpdateTransfer')->name('update.transfer');
        Route::get('/delete/transfer/{id}', 'DeleteTransfer')->name('delete.transfer');
        Route::get('/details/transfer/{id}', 'DetailsTransfer')->name('details.transfer');
    });


    Route::controller(ReportController::class)->group(function () {
        Route::get('/all/report', 'AllReport')->name('all.report');
        Route::get('/purchase/return/report', 'PurchaseReturnReport')->name('purchase.return.report');

        Route::get('/sale/report', 'SaleReport')->name('sale.report');
        Route::get('/sale/return/report', 'SaleReturnReport')->name('sale.return.report');
        Route::get('/product/stock/report', 'ProductStockReport')->name('product.stock.report');

        Route::get('/filter-purchases', 'FilterPurchases')->name('filter-purchases');
        Route::get('/filter-sales', 'FilterSales')->name('filter-sales');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('/laporan/distribusi-profit', [ProfitDistributionController::class, 'index'])->name('profit.distribution.report');
    });

    Route::controller(ServiceController::class)->group(function () {
        Route::get('/all/service', 'AllService')->name('all.service');
        Route::get('/add/service', 'AddService')->name('add.service');
        Route::post('/store/service', 'StoreService')->name('store.service');
        Route::get('/edit/service/{id}', 'EditService')->name('edit.service');
        Route::post('/update/service', 'UpdateService')->name('update.service');
        Route::get('/delete/service/{id}', 'DeleteService')->name('delete.service');
    });

    Route::controller(TailorTransactionController::class)->group(function () {
        Route::get('/all/tailor', 'index')->name('all.tailor');
        Route::get('/add/tailor', 'create')->name('add.tailor');
        Route::post('/store/tailor', 'store')->name('store.tailor');
        Route::get('/edit/tailor/{id}', 'edit')->name('edit.tailor');
        Route::post('/update/tailor/{id}', 'update')->name('update.tailor');
        Route::get('/delete/tailor/{id}', 'destroy')->name('delete.tailor');
        Route::get('/details/tailor/{id}', 'show')->name('details.tailor');
        Route::get('/invoice/tailor/{id}', 'cetak')->name('invoice.tailor');
        // Route::get('/invoice/tailor/{id}', 'InvoiceSales')->name('invoice.tailor');
    });

    Route::controller(ExpenseController::class)->group(function () {
        Route::get('/all/expense', 'index')->name('all.expense');
        Route::get('/add/expense', 'create')->name('add.expense');
        Route::post('/store/expense', 'store')->name('store.expense');
        Route::get('/edit/expense/{id}', 'edit')->name('edit.expense');
        Route::post('/update/expense', 'update')->name('update.expense');
        Route::get('/delete/expense/{id}', 'destroy')->name('delete.expense');
    });

    Route::controller(ProductionController::class)->group(function () {
        Route::get('/all/production', 'index')->name('all.production');
        Route::get('/add/production', 'create')->name('add.production');
        Route::post('/store/production', 'store')->name('store.production');
        Route::get('/edit/production/{id}', 'edit')->name('edit.production');
        Route::post('/update/production/{id}', 'update')->name('update.production');
        Route::get('/delete/production/{id}', 'destroy')->name('delete.production');
    });

    Route::controller(AttendanceController::class)->group(function () {
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
        Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::get('/delete/attendances/{id}', 'destroy')->name('attendances.delete');
    });


    Route::controller(RoleController::class)->group(function () {
        Route::get('/all/permission', 'AllPermission')->name('all.permission');
        Route::get('/add/permission', 'AddPermission')->name('add.permission');
        Route::post('/store/permission', 'StorePermission')->name('store.permission');
        Route::get('/edit/permission/{id}', 'EditPermission')->name('edit.permission');
        Route::post('/update/permission', 'UpdatePermission')->name('update.permission');
        Route::get('/delete/permission/{id}', 'DeletePermission')->name('delete.permission');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/all/roles', 'AllRoles')->name('all.roles');
        Route::get('/add/roles', 'AddRoles')->name('add.roles');
        Route::post('/store/roles', 'StoreRoles')->name('store.roles');
        Route::get('/edit/roles/{id}', 'EditRoles')->name('edit.roles');
        Route::post('/update/roles', 'UpdateRoles')->name('update.roles');
        Route::get('/delete/roles/{id}', 'DeleteRoles')->name('delete.roles');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('/add/roles/permission', 'AddRolesPermission')->name('add.roles.permission');
        Route::post('/role/permission/store', 'RolePermissionStore')->name('role.permission.store');
        Route::get('/all/roles/permission', 'AllRolesPermission')->name('all.roles.permission');

        Route::get('/admin/edit/roles/{id}', 'AdminEditRoles')->name('admin.edit.roles');
        Route::post('/admin/roles/update/{id}', 'AdminRolesUpdate')->name('admin.roles.update');
        Route::get('/admin/delete/roles/{id}', 'AdminDeleteRoles')->name('admin.delete.roles');
    });


    Route::controller(RoleController::class)->group(function () {
        Route::get('/all/admin', 'AllAdmin')->name('all.admin');
        Route::get('/add/admin', 'AddAdmin')->name('add.admin');
        Route::post('/store/admin', 'StoreAdmin')->name('store.admin');
        Route::get('/edit/admin/{id}', 'EditAdmin')->name('edit.admin');
        Route::post('/update/admin/{id}', 'UpdateAdmin')->name('update.admin');
        Route::get('/delete/admin/{id}', 'DeleteAdmin')->name('delete.admin');
    });

    Route::controller(PayrollController::class)->group(function () {
        Route::get('/payroll/generate', 'showGenerateForm')->name('payroll.generate.form');
        Route::post('/payroll/calculate', 'calculate')->name('payroll.calculate');
        Route::post('/payroll/store', 'store')->name('payroll.store');
        Route::get('/payroll/history', 'index')->name('payroll.history');
        Route::get('/payroll/{payroll}', 'show')->name('payroll.show');
        Route::get('/delete/payroll/{payroll}', 'destroy')->name('payroll.destroy');
    });

    Route::controller(FinancialReportController::class)->group(function () {
        Route::get('/arus-kas', [FinancialReportController::class, 'index'])->name('financial.index');
        Route::get('/arus-kas/create', [FinancialReportController::class, 'create'])->name('financial.create');
        Route::post('/arus-kas', [FinancialReportController::class, 'store'])->name('financial.store');
        Route::get('/laporan-arus-kas/{year}/{month}', [FinancialReportController::class, 'cetak'])->name('financial.report.show');
    });

    Route::controller(AcceptanceController::class)->group(function () {
        Route::get('/all/acceptance', 'index')->name('all.acceptance');
        Route::get('/add/acceptance', 'create')->name('add.acceptance');
        Route::post('/store/acceptance', 'store')->name('store.acceptance');
        Route::get('/edit/acceptance/{id}', 'edit')->name('edit.acceptance');
        Route::post('/update/acceptance', 'update')->name('update.acceptance');
        Route::get('/delete/acceptance/{id}', 'destroy')->name('delete.acceptance');
    });
});
