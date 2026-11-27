<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['user']], function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    // categories
    Route::resource('categories', CategoryController::class);
    Route::prefix('categories')->group(function (){
        Route::patch('/delete-or-restore/{category}', [CategoryController::class, 'deleteOrRestore'])->name('categories.delete');
    });

    // products
    Route::resource('products', ProductController::class);
    Route::prefix('products')->group(function (){
        Route::patch('/delete-or-restore/{product}', [ProductController::class, 'deleteOrRestore'])->name('products.delete');
    });

    // purchases
    Route::resource('purchases', PurchaseController::class);
    Route::prefix('purchases')->group(function (){
        Route::patch('/delete-or-restore/{purchase}', [PurchaseController::class, 'deleteOrRestore'])->name('purchases.delete');
        Route::get('/product-form/{index?}', [PurchaseController::class, 'productForm'])->name('purchases.productForm');
    });

    // orders
    Route::resource('orders', OrderController::class);
    Route::prefix('orders')->group(function (){
        Route::patch('/delete-or-restore/{order}', [OrderController::class, 'deleteOrRestore'])->name('orders.delete');
    });
});


Route::view('user/login', 'user.login')->name('user.login');
Route::post('user/do-login', [UserController::class, 'doLogin'])->name('user.doLogin');