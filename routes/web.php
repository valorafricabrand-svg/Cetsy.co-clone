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
use App\Http\Controllers\Seller\SubscriptionController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminReportController as AdminReport;
use App\Http\Controllers\Admin\SettingsController as AdminSetting;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\OrderMessageController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Seller\ServiceController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ProductInfoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public homepage
Route::get('/', [HomeController::class, 'index'])
     ->name('home');

// Cart
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/buy', [CartController::class, 'addToBuy'])->name('cart.buy');
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');


Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');










// Categories and product listings
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/listings', [ProductController::class, 'listings'])->name('listings');
Route::get('/listing/{slug}', [ProductController::class, 'listing'])->name('listing.show');
Route::get('/category/{slug}', [CategoryController::class, 'categoryShow'])->name('category.show');

// Shop and dashboard routes
Route::get('shop/{id}', [ShopController::class, 'showPublic'])->name('shop.show');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

Route::get('/categories', [CategoryController::class, 'index'])
     ->name('categories.index');
Route::get('/search', [ProductController::class, 'search'])
     ->name('search');
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


// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile management

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shop management
    Route::get('/shop/index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('/shop/create', [ShopController::class, 'create'])->name('shops.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shops.store');
    Route::get('/shop/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');
    Route::get('shops/{shop}/edit', [ShopController::class, 'edit'])->name('shops.edit');
    Route::patch('shops/{shop}', [ShopController::class, 'update'])->name('shops.update');

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
    Route::get('/shops/{shop:slug}', [ShopController::class, 'show'])
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

    // Checkout & orders
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/order', [OrderController::class, 'storeOrder'])->name('store_order');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Order chat
    Route::get('orders/{order}/chat', [OrderMessageController::class, 'show'])->name('orders.chat.show');
    Route::get('orders/{order}/chat/messages', [OrderMessageController::class, 'fetch'])->name('orders.chat.fetch');
    Route::post('orders/{order}/chat', [OrderMessageController::class, 'send'])->name('orders.chat.send');

    // Order reviews
    Route::prefix('orders/{order}')->name('orders.')->group(function () {
        Route::post('items/{item}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('items.reviews.store');
    });
});

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('categories', CategoryController::class);

    // KYC management
    Route::get('kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])->name('kyc.showDetails');

    // Settings and reports
    Route::get('settings', [AdminSetting::class, 'index'])->name('settings');
    Route::get('reports', [AdminReport::class, 'index'])->name('reports');

    // Subscription management
    Route::post('subscriptions/deactivate-expired', [AdminSubscriptionController::class, 'deactivateExpired'])
        ->name('subscriptions.deactivate-expired');

    // Payout management
    Route::controller(\App\Http\Controllers\Admin\PayoutRequestController::class)
        ->prefix('payout-requests')
        ->name('payouts.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{payout}', 'show')->name('show');
            Route::post('/{payout}/approve', 'approve')->name('approve');
            Route::post('/{payout}/reject', 'reject')->name('reject');
            Route::post('/{payout}/paid', 'markPaid')->name('paid');
        });
});

// Seller routes
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    // Basic seller routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/order/payments', [OrderController::class, 'orderPayments'])->name('orders.payments');
    
    // Order status management
    Route::patch('orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // Subscription routes
    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription');
    Route::post('/subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // KYC routes
    Route::get('/kyc', [KycController::class, 'show'])->name('kyc');
    Route::post('/kyc', [KycController::class, 'submit'])->name('kyc.submit');

    // Dashboard and analytics
    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
    Route::get('analytics', [\App\Http\Controllers\Seller\AnalyticsController::class, 'index'])->name('analytics.index');

    // Payouts
    Route::get('payouts', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'store'])->name('payouts.store');

    // Services
    Route::resource('services', ServiceController::class);
});

// Buyer routes
Route::middleware(['auth'])->prefix('buyer')->name('buyer.')->group(function () {
    Route::get('dashboard', [BuyerDashboard::class, 'index'])->name('dashboard');
    Route::get('/orders/{order}', [AccountController::class, 'orderDetails'])->name('orders.show');
});

// Account routes
Route::get('/account/dashboard', [AccountController::class, 'dashboard'])->name('account.dashboard');
Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
Route::get('/account/payments', [AccountController::class, 'payments'])->name('account.payments');
Route::get('/account/details', [AccountController::class, 'details'])->name('account.details');
Route::post('/account/details/update', [AccountController::class, 'updateDetails'])->name('account.updateDetails');
Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');

// Wallet routes
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [WalletController::class, 'depositForm'])->name('wallet.deposit.form');
    Route::post('/wallet/deposit', [WalletController::class, 'storeDeposit'])->name('wallet.deposit.store');
    Route::post('/wallet/deposit/paypal', [WalletController::class, 'handlePayPalDeposit'])->name('wallet.deposit.paypal');
});

// Payment routes
Route::get('/pay-now/{total}', [OrderController::class, 'payNow'])->name('pay_now');
Route::get('/pay-now-invoice/{total}', [OrderController::class, 'payNowInvoice'])->name('pay_now_invoice');
Route::get('success-deposit/{id}', ['as' => 'success_deposit', 'uses' => 'App\Http\Controllers\OrderController@successDeposit']);
Route::get('success-deposit-invoice/{id}', ['as' => 'success_deposit_invoice', 'uses' => 'App\Http\Controllers\OrderController@successDepositInvoice']);

// MPesa routes
Route::get('/bmpesa', [MpesaController::class, 'initiate']);
Route::get('/bconfirm-payment/{id}', [MpesaController::class, 'checkStatus']);


// Wishlist
Route::get('wishlist', [ProductInfoController::class, 'wishlist'])->name('wishlist');

Route::middleware('auth')->group(function () {
    Route::prefix('orders/{order}')->name('orders.')->group(function () {
        Route::post('items/{item}/reviews',
            [\App\Http\Controllers\ReviewController::class, 'store']
        )->name('items.reviews.store');
    });
});


Route::middleware(['auth'])
      ->prefix('seller')
      ->name('seller.')
      ->group(function () {

    // list + create payouts
    Route::get ('payouts', [\App\Http\Controllers\Seller\PayoutRequestController::class,'index'])
         ->name('payouts.index');     // 👈 now exists

    Route::post('payouts', [\App\Http\Controllers\Seller\PayoutRequestController::class,'store'])
         ->name('payouts.store');


            Route::get('analytics', [\App\Http\Controllers\Seller\AnalyticsController::class,'index'])
               ->name('analytics.index');
});


Route::get('wishlist', [ProductController::class, 'wishlist'])->name('wishlist');


// Settings resource
Route::resource('settings', \App\Http\Controllers\SettingController::class)
    ->only(['index', 'edit', 'update'])
    ->middleware('auth', 'can:admin');

require __DIR__ . '/auth.php';


Route::middleware('auth')->group(function () {
 Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/checkout/order', [OrderController::class, 'storeOrder'])->name('store_order');
});

