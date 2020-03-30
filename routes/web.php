<?php

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
    Route::get('/', 'HomeController@index')->name('dashboard');

    // categories
    Route::resource('categories', 'CategoryController');
    Route::prefix('categories')->group(function (){
        Route::get('/delete-or-restore/{category}', 'CategoryController@deleteOrRestore')->name('categories.delete');
    });

    // products
    Route::resource('products', 'ProductController');
    Route::prefix('products')->group(function (){
        Route::get('/delete-or-restore/{product}', 'ProductController@deleteOrRestore')->name('products.delete');
    });

    // purchases
    Route::resource('purchases', 'PurchaseController');
    Route::prefix('purchases')->group(function (){
        Route::get('/delete-or-restore/{purchase}', 'PurchaseController@deleteOrRestore')->name('purchases.delete');
        Route::get('/product-form/{index?}', 'PurchaseController@productForm')->name('purchases.productForm');
    });
});


Route::view('user/login', 'user.login')->name('user.login');
Route::post('user/do-login', 'UserController@doLogin')->name('user.doLogin');