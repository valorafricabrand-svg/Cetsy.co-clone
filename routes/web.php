<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController, ProfileController, ShopController, ProductController,
    CategoryController, CartController, CheckoutController, OrderController,
    DashboardController, WalletController, OrderMessageController,
    AccountController, ProductInfoController, MpesaController, MediaController, DigitalFileController, ShippingProfileController, WishlistController, OfferController, MessageController
};

use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboard,
    SubscriptionController as AdminSubscriptionController,
    UserController, AdminReportController as AdminReport,
    SettingsController as AdminSetting,
    PayoutRequestController as AdminPayoutRequestController,
    PaymentController,
    PaymentTypeController
};

use App\Http\Controllers\Seller\{
    DashboardController as SellerDashboard,
    KycController, SubscriptionController,
    AnalyticsController, PayoutRequestController,
    ServiceController, BuyerController
};

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboard;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// pages
Route::get('/become-seller', function () {return themed_view('pages.become-seller');})->name('become-seller');
Route::get('/privacy', function () {return themed_view('pages.privacy');})->name('privacy');
Route::get('/terms', function () {return themed_view('pages.terms');})->name('terms');
Route::get('/seller-forum', function () {return themed_view('pages.seller-forum');})->name('seller-forum');
Route::get('/seller-tips', function () {return themed_view('pages.seller-tips');})->name('seller-tips');
Route::get('/buyer-tips', function () {return themed_view('pages.buyer-tips');})->name('buyer-tips');
Route::get('/buyer-terms', function () {return themed_view('pages.buyer-terms');})->name('buyer-terms');
Route::get('/about', function () {return themed_view('pages.about');})->name('about');
Route::get('/house-policy', function () {return themed_view('pages.house-policy');})->name('house-policy');

// Product listings & categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/listings', [ProductController::class, 'listings'])->name('listings');
Route::get('/listing/{slug}', [ProductController::class, 'listing'])->name('listing.show');
Route::get('/category/{slug}', [CategoryController::class, 'categoryShow'])->name('category.show');

// Shop public profile
Route::get('/shop/{id}', [ShopController::class, 'showPublic'])->name('shop.show');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'viewCart'])->name('view');
    Route::post('/add', [CartController::class, 'addToCart'])->name('add');
    Route::post('/buy', [CartController::class, 'addToBuy'])->name('buy');
    Route::post('/remove', [CartController::class, 'removeFromCart'])->name('remove');
    Route::post('/update', [CartController::class, 'updateCart'])->name('update');

});



// Wishlist
Route::get('/wishlist', [ProductController::class, 'wishlist'])->name('wishlist');

// MPESA
Route::get('/bmpesa', [MpesaController::class, 'initiate']);
Route::get('/bconfirm-payment/{id}', [MpesaController::class, 'checkStatus']);

// Payment routes
Route::get('/pay-now/{total}', [OrderController::class, 'payNow'])->name('pay_now');
Route::post('/products/{product}/pay-fee', [ProductController::class, 'payFee'])
      ->name('products.pay-fee');

Route::get('/pay-now-invoice/{total}', [OrderController::class, 'payNowInvoice'])->name('pay_now_invoice');
Route::get('/success-deposit/{id}', [OrderController::class, 'successDeposit'])->name('success_deposit');
Route::get('/success-deposit-fee/{id}', [ProductController::class, 'successDeposit'])->name('success_deposit_fee');
Route::get('/success-deposit-invoice/{id}', [OrderController::class, 'successDepositInvoice'])->name('success_deposit_invoice');
Route::resource('shipping-profiles', ShippingProfileController::class)
     ->only(['store']);
/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    Route::post('/cart/update-shipping-selection', [CartController::class, 'updateShippingSelection'])->name('cart.updateShippingSelection');

    Route::post('/favorites/toggle', [WishlistController::class, 'toggle'])
         ->name('favorites.toggle');
        Route::delete('/favorites/{wishlist}', [WishlistController::class, 'remove'])
         ->name('wishlist.remove'); 
        Route::post('/offers', [OfferController::class, 'store'])
         ->name('offers.store');
          Route::post('/messages', [MessageController::class, 'store'])
         ->name('messages.store');
    Route::post('/products/{product}/media', [MediaController::class, 'upload'])->name('media.upload');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::delete('/digital-files/{digitalFile}', [DigitalFileController::class, 'destroy'])->name('digital-files.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

 

    // Products
    Route::resource('products', ProductController::class);

    Route::resource('shipping_profiles', ShippingProfileController::class)
        ->except(['show']);
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/order', [OrderController::class, 'storeOrder'])->name('store_order');

    Route::get('/downloads/{file}', [DigitalFileController::class, 'download'])
     ->name('digital-files.download');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Chat
    Route::get('/orders/{order}/chat', [OrderMessageController::class, 'show'])->name('orders.chat.show');
    Route::get('/orders/{order}/chat/messages', [OrderMessageController::class, 'fetch'])->name('orders.chat.fetch');
    Route::post('/orders/{order}/chat', [OrderMessageController::class, 'send'])->name('orders.chat.send');

    // Reviews
    Route::post('/orders/{order}/items/{item}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('orders.items.reviews.store');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'depositForm'])->name('deposit.form');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
        Route::post('/deposit/paypal', [WalletController::class, 'handlePayPalDeposit'])->name('deposit.paypal');
    });

    // Account
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/payments', [AccountController::class, 'payments'])->name('payments');
        Route::get('/details', [AccountController::class, 'details'])->name('details');
        Route::post('/details/update', [AccountController::class, 'updateDetails'])->name('updateDetails');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::get('/logout', [AccountController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');



    Route::resource('users', UserController::class);
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::get('sellers/{userId}/login-as', [UserController::class, 'loginAs'])->name('sellers.login-as');
    Route::get('return-from-impersonation', [UserController::class, 'returnFromImpersonation'])->name('return-from-impersonation');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::post('products/{product}/toggle-status', [\App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])->name('kyc.showDetails');

    Route::get('settings', [AdminSetting::class, 'index'])->name('settings');
    Route::get('reports', [AdminReport::class, 'index'])->name('reports');

    Route::post('subscriptions/deactivate-expired', [AdminSubscriptionController::class, 'deactivateExpired'])->name('subscriptions.deactivate-expired');

    Route::prefix('payout-requests')->name('payouts.')->controller(AdminPayoutRequestController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{payout}', 'show')->name('show');
        Route::post('/{payout}/approve', 'approve')->name('approve');
        Route::post('/{payout}/reject', 'reject')->name('reject');
        Route::post('/{payout}/paid', 'markPaid')->name('paid');
    });

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
//Payment Types
    Route::resource('payment-types', PaymentTypeController::class);
});

/*
|--------------------------------------------------------------------------
| Seller Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');


       // Shop (one-shop-per-user logic)
    Route::get('/shop/index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shops.store');
    Route::get('/shops/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])->name('shops.edit');
    Route::patch('/shops/{shop}', [ShopController::class, 'update'])->name('shops.update');



    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('order/payments', [OrderController::class, 'orderPayments'])->name('orders.payments');
    Route::patch('orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    

    Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription');
    Route::post('subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('subscription/success/{id}', [SubscriptionController::class, 'successDeposit'])->name('subscription.success');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    Route::get('kyc', [KycController::class, 'show'])->name('kyc');
    Route::post('kyc', [KycController::class, 'submit'])->name('kyc.submit');

    Route::get('payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PayoutRequestController::class, 'store'])->name('payouts.store');

    Route::resource('services', ServiceController::class);

    // Buyers
    Route::get('buyers', [BuyerController::class, 'index'])->name('buyers.index');
    Route::get('buyers/{buyer}', [BuyerController::class, 'show'])->name('buyers.show');

    Route::resource('offers', App\Http\Controllers\Seller\OfferController::class);

    Route::get('messages', [App\Http\Controllers\Seller\MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [App\Http\Controllers\Seller\MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{message}/reply', [App\Http\Controllers\Seller\MessageController::class, 'reply'])->name('messages.reply');

    // Payment Methods
    Route::resource('payment-methods', \App\Http\Controllers\Seller\PaymentMethodController::class);
});

/*
|--------------------------------------------------------------------------
| Buyer Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('buyer')->name('buyer.')->group(function () {
    Route::get('dashboard', [BuyerDashboard::class, 'index'])->name('dashboard');
    Route::get('orders/{order}', [AccountController::class, 'orderDetails'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('messages', [MessageController::class, 'buyerIndex'])->name('messages.index');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');

    Route::get('/messages/{message}/chat', [MessageController::class, 'show'])->name('messages.chat.show');
    Route::get('/messages/{message}/chat/messages', [MessageController::class, 'fetch'])->name('messages.chat.fetch');
    Route::post('/messages/{message}/chat', [MessageController::class, 'send'])->name('messages.chat.send');
});

/*
|--------------------------------------------------------------------------
| Settings (Admin Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->resource('settings', AdminSetting::class)
    ->only(['index', 'edit', 'update']);

require __DIR__ . '/auth.php';
