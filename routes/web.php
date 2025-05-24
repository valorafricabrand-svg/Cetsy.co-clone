<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Seller\DashboardController as SellerDashboard;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboard;
use App\Http\Controllers\Seller\KycController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public homepage
Route::get('/', [HomeController::class, 'index'])
     ->name('home');

// Cart
Route::get   ('/cart',               [CartController::class, 'index'])  ->name('cart.index');
Route::post  ('/cart',               [CartController::class, 'store'])  ->name('cart.store');
Route::patch ('/cart/{productId}',   [CartController::class, 'update']) ->name('cart.update');
Route::delete('/cart/{productId}',   [CartController::class, 'destroy'])->name('cart.destroy');


// Public product detail page
Route::get('/listings', [ProductController::class, 'listings'])
     ->name('listings');
Route::get('/listing/{slug}', [ProductController::class, 'listing'])
     ->name('listing.show');
Route::get('/category/{slug}', [CategoryController::class, 'categoryShow'])
     ->name('category.show');
// Authenticated & verified generic dashboard (if you still use it)

Route::get('/dashboard',    [DashboardController::class, 'dashboard'])
         ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile management
    Route::get('/profile',    [ProfileController::class, 'edit'])
         ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])
         ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
         ->name('profile.destroy');

    // One-shop-per-user
    Route::get('/shop/index',       [ShopController::class, 'index'])
         ->name('shops.index');
    Route::get('/shop/create',      [ShopController::class, 'create'])
         ->name('shops.create');
    Route::post('/shop',            [ShopController::class, 'store'])
         ->name('shops.store');
    Route::get('/shop/{shop:slug}', [ShopController::class, 'show'])
         ->name('shops.show');

// Show the edit form (only for the owner)
Route::get('shops/{shop}/edit', [ShopController::class, 'edit'])
     ->name('shops.edit')
     ->middleware('auth');

// Handle the form submission
Route::patch('shops/{shop}', [ShopController::class, 'update'])
     ->name('shops.update')
     ->middleware('auth');


    // Products management
    Route::resource('products', ProductController::class);

    // Categories management
    Route::resource('categories', CategoryController::class);



    // Checkout & orders
    Route::get('/checkout',                      [CheckoutController::class, 'index'])
         ->name('checkout.index');
    Route::post('/checkout',                     [CheckoutController::class, 'store'])
         ->name('checkout.store');
    Route::get('/checkout/success/{order}',      [CheckoutController::class, 'success'])
         ->name('checkout.success');
    Route::get('/orders',                        [OrderController::class, 'index'])
         ->name('orders.index');
    Route::get('/orders/{order}',                [OrderController::class, 'show'])
         ->name('orders.show');
});

// Admin panel (only `user_type = admin`)
Route::middleware(['auth'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
         Route::get('dashboard', [AdminDashboard::class, 'index'])
              ->name('dashboard');
         Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::get('kyc', [KycController::class, 'index'])
         ->name('kyc.index');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])
         ->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])
         ->name('kyc.showDetails');

    // Settings page
    Route::get('settings', [SettingsController::class, 'index'])
         ->name('settings');

         Route::get('reports', [AdminReport::class, 'index'])
             ->name('reports');
     });

// KYC routes (do NOT use ensure.seller.kyc here)
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/kyc', [KycController::class, 'show'])->name('kyc');
    Route::post('/kyc', [KycController::class, 'submit'])->name('kyc.submit');
});

// All other seller routes (require KYC approval)
Route::middleware(['auth', 'seller', 'ensure.seller.kyc'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
    // ... other seller routes
});

// Buyer panel (only `user_type = buyer`)
Route::middleware(['auth'])
     ->prefix('buyer')
     ->name('buyer.')
     ->group(function () {
         Route::get('dashboard', [BuyerDashboard::class, 'index'])
              ->name('dashboard');
         // add buyer-specific routes if needed
     });




require __DIR__ . '/auth.php';
