<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('user/login', [AuthController::class, 'showLogin'])->name('user.login');
    Route::post('user/do-login', [AuthController::class, 'doLogin'])->name('user.doLogin');

    Route::get('user/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('user/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('user/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('user/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('user')->group(function (): void {
    Route::post('user/logout', [AuthController::class, 'logout'])->name('user.logout');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::patch('categories/delete-or-restore/{category}', [CategoryController::class, 'deleteOrRestore'])->name('categories.delete');

    // Products
    Route::resource('products', ProductController::class);
    Route::patch('products/delete-or-restore/{product}', [ProductController::class, 'deleteOrRestore'])->name('products.delete');

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::prefix('purchases')->group(function (): void {
        Route::patch('/delete-or-restore/{purchase}', [PurchaseController::class, 'deleteOrRestore'])->name('purchases.delete');
        Route::get('/product-form/{index?}', [PurchaseController::class, 'productForm'])->name('purchases.productForm');
    });

    // Suppliers & Customers
    Route::resource('suppliers', SupplierController::class);
    Route::patch('suppliers/delete-or-restore/{supplier}', [SupplierController::class, 'deleteOrRestore'])->name('suppliers.delete');

    Route::resource('customers', CustomerController::class);
    Route::patch('customers/delete-or-restore/{customer}', [CustomerController::class, 'deleteOrRestore'])->name('customers.delete');

    // Orders & POS
    Route::get('orders/pos/products', [OrderController::class, 'products'])->name('orders.products');
    Route::post('orders/pos/printer/test', [OrderController::class, 'testThermalPrinter'])->name('orders.printer.test');
    Route::resource('orders', OrderController::class)->except(['edit', 'update']);
    Route::prefix('orders')->group(function (): void {
        Route::get('/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
        Route::post('/{order}/print-thermal', [OrderController::class, 'printThermal'])->name('orders.printThermal');
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/delete-or-restore/{order}', [OrderController::class, 'deleteOrRestore'])->name('orders.delete');
    });

    // Expenses & Sales Returns
    Route::resource('expenses', ExpenseController::class);
    Route::patch('expenses/delete-or-restore/{expense}', [ExpenseController::class, 'deleteOrRestore'])->name('expenses.delete');

    Route::get('returns/order-items/{order}', [SaleReturnController::class, 'orderItems'])->name('returns.orderItems');
    Route::resource('returns', SaleReturnController::class);

    // Users, Roles & Permissions
    Route::resource('users', UserController::class);
    Route::patch('users/delete-or-restore/{user}', [UserController::class, 'deleteOrRestore'])->name('users.delete');
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
});
