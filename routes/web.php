<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Homepage (public)
Route::get('/', [HomeController::class, 'index'])
     ->name('home');

// Add to cart (public; guests may post here)
Route::post('/cart', [CartController::class, 'store'])
     ->name('cart.store');

Route::get('/listing/{slug}', [ProductController::class, 'listing'])->name('listing.show');

// Dashboard (requires auth & email verification)
Route::get('/dashboard', fn() => view('dashboard'))
     ->middleware(['auth','verified'])
     ->name('dashboard');

// All routes below require authentication
Route::middleware('auth')->group(function () {
    // Profile management
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shop (one-per-user)
    Route::get('/shop/create',      [ShopController::class, 'create'])->name('shops.create');
    Route::post('/shop',            [ShopController::class, 'store'])->name('shops.store');

    Route::get('/shop/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');

    // Products CRUD
    Route::resource('products', ProductController::class);

    // Categories CRUD (admin/UI)
    Route::resource('categories', CategoryController::class);

    // View and manage cart (only for logged-in users)
    Route::get('/cart',                [CartController::class, 'index'])->name('cart.index');
    Route::patch('/cart/{id}',         [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}',        [CartController::class, 'destroy'])->name('cart.destroy');

    // (You can add checkout & orders here)
     Route::get('/checkout',          [CheckoutController::class,'index'])->name('checkout.index');
     Route::post('/checkout',         [CheckoutController::class,'store'])->name('checkout.store');
     Route::get('/orders',            [OrderController::class,'index'])->name('orders.index');
     Route::get('/orders/{order}',    [OrderController::class,'show'])->name('orders.show');
});

require __DIR__.'/auth.php';
