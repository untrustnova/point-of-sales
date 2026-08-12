<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductExcelController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'active_store'])->group(function () {
    // Dashboard POS Nova
    Route::get('/dashboard', DashboardController::class)->name('dashboard.main');

    // POS (Point of Sale Kasir)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    // Transaksi Kasir dengan Proteksi Shift Aktif & Rate Limiter
    Route::post('/pos/transactions', [PosController::class, 'storeTransaction'])
        ->middleware(['active_shift', 'throttle:pos-transactions'])
        ->name('pos.transactions.store');

    Route::post('/pos/transactions/{transaction}/void', [PosController::class, 'void'])
        ->middleware(['permission:void-transaction'])
        ->name('pos.transactions.void');

    // Cetak Struk Thermal & PDF
    Route::get('/transactions/{transaction}/receipt', [ReceiptController::class, 'show'])->name('receipt.show');
    Route::get('/transactions/{transaction}/receipt/pdf', [ReceiptController::class, 'downloadPdf'])->name('receipt.pdf');

    // Shift Kasir
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Manajemen Staf & User (Khusus Owner / Manager)
    Route::middleware(['role:Owner|Manager'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::get('/store-settings', [StoreSettingController::class, 'show'])->name('store-settings.show');
        Route::put('/store-settings', [StoreSettingController::class, 'update'])->name('store-settings.update');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Excel Product Import/Export
    Route::get('/products/export', [ProductExcelController::class, 'export'])->name('products.export');
    Route::post('/products/import', [ProductExcelController::class, 'import'])->name('products.import');

    // Manajemen Produk & Katalog
    Route::resource('products', ProductController::class)->except(['create', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('units', UnitController::class)->except(['create', 'edit', 'show']);

    // Stok & Opname
    Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    Route::post('/stock/opname', [StockMovementController::class, 'store'])->name('stock.opname');

    // Purchase Orders & Supplier
    Route::resource('suppliers', SupplierController::class)->except(['create', 'edit', 'show']);
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    // Pelanggan
    Route::resource('customers', CustomerController::class)->except(['create', 'edit', 'show']);

    // Pengeluaran / Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy']);

    // Laporan & Analytics
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/pdf', [ReportController::class, 'downloadPdf'])->name('reports.sales.pdf');
    Route::get('/reports/sales/excel', [ReportController::class, 'downloadExcel'])->name('reports.sales.excel');

    // Team invitations
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

require __DIR__.'/settings.php';
