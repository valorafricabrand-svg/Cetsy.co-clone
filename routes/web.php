<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage
Route::get('/', [HomeController::class, 'index'])
     ->name('home');

Route::get('/dashboard', fn() => view('dashboard'))
     ->middleware(['auth','verified'])
     ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile',   [ProfileController::class,'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class,'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class,'destroy'])->name('profile.destroy');

    // Shop
    Route::get('/shop/create',      [ShopController::class,'create'])->name('shops.create');
    Route::post('/shop',            [ShopController::class,'store'])->name('shops.store');
    Route::get('/shop/{shop:slug}', [ShopController::class,'show'])->name('shops.show');

    // Products
    Route::resource('products', ProductController::class);

    // Categories (admin/ui)
    Route::resource('categories', CategoryController::class);

    // Cart
    Route::get('/cart', [CartController::class, 'index'])
         ->name('cart.index');
});

require __DIR__.'/auth.php';
