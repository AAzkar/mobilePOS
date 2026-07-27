<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/scan');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Cashier + Admin
    Route::get('/scan', [ScanController::class, 'index'])->name('scan');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::get('/api/products/lookup/{barcode}', [ProductController::class, 'lookup'])->name('api.products.lookup');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/receipts/{transaction}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{transaction}/pdf', [ReceiptController::class, 'pdf'])->name('receipts.pdf');

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('/settings/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/settings/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/settings/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/settings/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/settings/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
