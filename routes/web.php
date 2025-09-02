<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController, ProfileController, ShopController, ProductController,
    CategoryController, CartController, CheckoutController, OrderController,
    DashboardController, WalletController, OrderMessageController,
    AccountController, ProductInfoController, MpesaController, MediaController, DigitalFileController, ShippingProfileController, WishlistController, OfferController, MessageController, VariationController, DealController, BulkPriceController,
    ProductReportController,ProductShippingController, ProductVariationController
};

use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboard,
    SubscriptionController as AdminSubscriptionController,
    UserController,
    AdminReportController as AdminReport,
    SettingsController as AdminSetting,
    PayoutRequestController as AdminPayoutRequestController,
    PaymentController,
    PaymentTypeController,
    CategoryAttributeController,
    ProductReportController as AdminProductReportController,
    AdminWalletController,
    ReviewController,
    AdminNotificationController
};
use App\Http\Controllers\Buyer\BuyerDashboard;
use App\Http\Controllers\Seller\{
    DashboardController as SellerDashboard,
    KycController,
    SubscriptionController,
    AnalyticsController,
    PayoutRequestController,
    ServiceController,
    BuyerController,
    FavoriteController,
    PaymentMethodController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
// Safaricom callback (must be reachable publicly)
Route::post('/wallet/deposit/mpesa/callback', [WalletController::class, 'mpesaCallback'])
    ->name('wallet.deposit.mpesa.callback');



Route::post('/wallet/deposit/mpesa/callback', [WalletController::class, 'mpesaCallback'])->name('wallet.deposit.mpesa.callback');
Route::post('/wallet/deposit/mpesa/timeout',  [WalletController::class, 'mpesaTimeout'])->name('wallet.deposit.mpesa.timeout');


// pages
Route::get('/become-seller', function () {
    return themed_view('pages.become-seller');
})->name('become-seller');
Route::get('/privacy', function () {
    return themed_view('pages.privacy');
})->name('privacy');
Route::get('/terms', function () {
    return themed_view('pages.terms');
})->name('terms');
Route::get('/seller-forum', function () {
    return themed_view('pages.seller-forum');
})->name('seller-forum');
Route::get('/seller-tips', function () {
    return themed_view('pages.seller-tips');
})->name('seller-tips');
Route::get('/buyer-tips', function () {
    return themed_view('pages.buyer-tips');
})->name('buyer-tips');
Route::get('/buyer-terms', function () {
    return themed_view('pages.buyer-terms');
})->name('buyer-terms');
Route::get('/about', function () {
    return themed_view('pages.about');
})->name('about');
Route::get('/house-policy', function () {
    return themed_view('pages.house-policy');
})->name('house-policy');

// Product listings & categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/listings', [ProductController::class, 'listings'])->name('listings');
Route::get('/listing/{slug}', [ProductController::class, 'listing'])->name('listing.show');
Route::get('/category/{slug}', [CategoryController::class, 'categoryShow'])->name('category.show');

// All shops listing
Route::get('/shops', [ShopController::class, 'publicIndex'])->name('shops.index');

// Shop public profile
Route::get('/shop/{id}', [ShopController::class, 'showPublic'])->name('shop.show');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',        [CartController::class, 'viewCart'])->name('view');
    Route::post('/add',    [CartController::class, 'addToCart'])->name('add');
    Route::post('/buy',    [CartController::class, 'addToBuy'])->name('buy');
    Route::post('/remove', [CartController::class, 'removeFromCart'])->name('remove');
    Route::post('/update', [CartController::class, 'updateCart'])->name('update');

    // persist per-item shipping selection to session
    Route::post('/shipping', [CartController::class, 'updateShippingSelection'])->name('shipping');

    // checkout page
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
});

// routes/web.php
Route::get(
    '/categories/{id}/attribute-template',
    [CategoryController::class, 'attributeTemplate']
)->name('categories.attributeTemplate');

// Wishlist
Route::get('/wishlist', [ProductController::class, 'wishlist'])->name('wishlist');

// MPESA
// Route::get('/bmpesa', [MpesaController::class, 'initiate']);
// Route::get('/bconfirm-payment/{id}', [MpesaController::class, 'checkStatus']);

// Payment routes
Route::get('/pay-now/{total}', [OrderController::class, 'payNow'])->name('pay_now');



Route::post('/products/{product}/pay-fee', [ProductController::class, 'payFee'])
    ->name('products.pay-fee');



// Product Reports
Route::post('/product-reports', [ProductReportController::class, 'store'])->name('product-reports.store');

Route::prefix('products/{product}')->name('products.')->group(function () {
    // About
    // About (view page you already have)
    Route::get('/pricing',     [ProductController::class, 'pricing'])->name('pricing');    // Edit forms
    Route::get('/variations',  [ProductController::class, 'variations'])->name('variations');
    Route::get('/details',     [ProductController::class, 'details'])->name('details');
    Route::get('/shipping',    [ProductController::class, 'shipping'])->name('shipping');
    Route::get('/settings',    [ProductController::class, 'settings'])->name('settings');
    Route::get('/media',       [ProductController::class, 'media'])->name('media');

    // Updates
    Route::patch('/pricing',     [ProductController::class, 'updatePricing'])->name('pricing.update');
    Route::patch('/variations',  [ProductController::class, 'updateVariations'])->name('variations.update');
    Route::patch('/details',     [ProductController::class, 'updateDetails'])->name('details.update');
    Route::patch('/shipping',    [ProductController::class, 'updateShipping'])->name('shipping.update');
    Route::patch('/settings',    [ProductController::class, 'updateSettings'])->name('settings.update');






});

    Route::get('/products/{product}/variation-types/{type}/manage', [ProductVariationController::class, 'manage'])
    ->name('products.variations.manage');





// web.php
Route::post   ('/products/{product}/shipping/rows',           [ProductShippingController::class, 'storeShippingRow'])
     ->name('products.shipping.rows.store');
Route::delete ('/products/{product}/shipping/rows/{row}',     [ProductShippingController::class, 'destroyShippingRow'])
     ->name('products.shipping.rows.destroy');

Route::patch  ('/products/{product}/shipping/rows/{row}', [ProductShippingController::class, 'updateShippingRow'])
     ->name('products.shipping.rows.update');

Route::get('/pay-now-invoice/{total}', [OrderController::class, 'payNowInvoice'])->name('pay_now_invoice');
Route::get('/success-deposit/{id}', [OrderController::class, 'successDeposit'])->name('success_deposit');
Route::post('/success-deposit-fee/{id}', [ProductController::class, 'successDeposit'])->name('success_deposit_fee');
Route::get('/success-deposit-invoice/{id}', [OrderController::class, 'successDepositInvoice'])->name('success_deposit_invoice');
Route::resource('shipping-profiles', ShippingProfileController::class)
    ->only(['store']);
Route::get('reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
Route::post('reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
// routes/web.php

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    
    Route::patch('products/{product}/renewal', [ProductController::class, 'updateRenewal'])
         ->name('products.updateRenewal');

    Route::post('/listing/{order}/wallet', [WalletController::class,'payListing'])
         ->name('listing.wallet.pay');

    Route::post('/order/{order}/wallet', [WalletController::class,'payOrder'])
         ->name('order.wallet.pay');

    Route::post('/products/{product}/status', [ProductController::class, 'changeStatus'])
         ->name('products.changeStatus');

    Route::prefix('products/{product}')->group(function () {
        Route::post('variation‑types', [VariationController::class, 'storeType'])
             ->name('variationTypes.store');
        Route::post('variations', [VariationController::class, 'store'])
             ->name('variations.store');
        Route::post('variations/bulk', [VariationController::class, 'bulkStore'])
            ->name('variations.bulkStore');
    });

    Route::patch('variations/{variation}', [VariationController::class, 'update'])
         ->name('variations.update');
    Route::delete('variations/{variation}', [VariationController::class, 'destroy'])
         ->name('variations.destroy');
    Route::delete('variation‑types/{variationType}', [VariationController::class, 'destroyType'])
        ->name('variationTypes.destroy');

    Route::post('variation-types/{variationType}/options', [VariationController::class, 'storeOption'])
         ->name('variationOptions.store');
    Route::patch('variation-options/{option}', [VariationController::class, 'updateOption'])
         ->name('variationOptions.update');
    Route::delete('variation-options/{option}', [VariationController::class, 'destroyOption'])
         ->name('variationOptions.destroy');

    Route::post('/favorites/toggle', [WishlistController::class, 'toggle'])
         ->name('favorites.toggle');
    Route::delete('/favorites/{wishlist}', [WishlistController::class, 'remove'])
         ->name('wishlist.remove'); 
    Route::post('/offers', [OfferController::class, 'store'])
         ->name('offers.store');
    Route::post('/messages', [MessageController::class, 'store'])
         ->name('messages.store');
    Route::post('/products/{product}/media', [MediaController::class, 'upload'])
         ->name('media.upload');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])
         ->name('media.destroy');
    Route::delete('/digital-files/{digitalFile}', [DigitalFileController::class, 'destroy'])
         ->name('digital-files.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products
    Route::get('products/create', [ProductController::class, 'create'])
        ->middleware('kyc.after.two.sales')
        ->name('products.create');
    Route::post('products', [ProductController::class, 'store'])
        ->middleware('kyc.after.two.sales')
        ->name('products.store');
    Route::resource('products', ProductController::class)
        ->except(['create', 'store'])
        ->middleware('kyc.after.two.sales');
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
        ->name('products.duplicate');

    Route::post('media/{media}/crop', [MediaController::class, 'crop'])
         ->name('media.crop')
         ->middleware('auth');
   
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
    
    //notification routes
    Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    
    // Chat
    Route::get('/orders/{order}/chat', [OrderMessageController::class, 'show'])->name('orders.chat.show');
    Route::get('/orders/{order}/chat/messages', [OrderMessageController::class, 'fetch'])->name('orders.chat.fetch');
    Route::post('/orders/{order}/chat', [OrderMessageController::class, 'send'])->name('orders.chat.send');
    
    Route::patch('/products/{product}/set-featured-image', [ProductController::class, 'setFeaturedImage'])
         ->name('products.setFeaturedImage');
    
    // Reviews
    Route::post('/orders/{order}/items/{item}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
         ->name('orders.items.reviews.store');
    Route::get('/shops/{shop}/reviews', [\App\Http\Controllers\ReviewController::class, 'shopReviews'])
         ->name('shop.reviews');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'depositForm'])->name('deposit.form');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
        Route::post('/deposit/paypal', [WalletController::class, 'handlePayPalDeposit'])->name('deposit.paypal');

    
    });

    Route::post('/wallet/deposit/mpesa/stk', [WalletController::class, 'startMpesaStk'])
        ->name('wallet.deposit.mpesa.stk');

        // Poll status (frontend “listens” by polling this)
 Route::get ('/wallet/deposit/mpesa/status/{ref}', [WalletController::class, 'mpesaStatus'])->name('wallet.deposit.mpesa.status');

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

    Route::get('buyer/favorites', [ProductController::class, 'favorites'])->name('buyer.favorites');
    Route::get('buyer/offers', [ProductController::class, 'offers'])->name('buyer.offers');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // Disputes - Keep only this to test
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DisputeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DisputeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [\App\Http\Controllers\DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/messages', [\App\Http\Controllers\DisputeController::class, 'addMessage'])->name('messages.store');
        Route::get('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'showAppealForm'])->name('appeal.create');
        Route::post('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'submitAppeal'])->name('appeal.store');
    });

    // Evidence Requests (Binance-style)
    Route::prefix('evidence-requests')->name('evidence-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EvidenceRequestController::class, 'index'])->name('index');
        Route::get('/{evidenceRequest}', [\App\Http\Controllers\EvidenceRequestController::class, 'show'])->name('show');
        Route::post('/{evidenceRequest}/submit', [\App\Http\Controllers\EvidenceRequestController::class, 'submit'])->name('submit');
    });
    
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::resource('wallets', AdminWalletController::class)->except(['create', 'store']);
    Route::delete('wallets/bulk', [AdminWalletController::class, 'bulk'])->name('wallets.bulk');

    Route::patch('kyc/bulk', [KycController::class, 'bulk'])->name('kyc.bulk');
    Route::resource('users', UserController::class);
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::get('sellers/{userId}/login-as', [UserController::class, 'loginAs'])->name('sellers.login-as');
    Route::get('return-from-impersonation', [UserController::class, 'returnFromImpersonation'])->name('return-from-impersonation');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::post('products/{product}/toggle-status', [\App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    // Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    /* update + destroy — shallow, no category prefix */
    Route::put(
        '/category-attributes/{attribute}',
        [CategoryAttributeController::class, 'update']
    )->name('category-attributes.update');

    Route::delete(
        '/category-attributes/{attribute}',
        [CategoryAttributeController::class, 'destroy']
    )->name('category-attributes.destroy');
    Route::resource('categories', CategoryController::class);

    Route::get('kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('kyc/{kyc}', [KycController::class, 'show'])->name('kyc.show');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])->name('kyc.showDetails');

    Route::get('settings', [AdminSetting::class, 'index'])->name('settings');
    Route::get('reports', [AdminReport::class, 'index'])->name('reports');
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    
    // Messages
    Route::get('messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{conversation}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('messages.show');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

    // Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

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

    // Product Reports
    Route::get('product-reports', [AdminProductReportController::class, 'index'])->name('product-reports.index');
    Route::put('product-reports/{id}', [AdminProductReportController::class, 'update'])->name('product-reports.update');

    // Admin Disputes
    Route::prefix('admin-disputes')->name('admin-disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('show');
        Route::get('/{dispute}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'showResolveForm'])->name('resolve.create');
        Route::post('/{dispute}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'resolve'])->name('resolve.store');
        Route::post('/{dispute}/messages', [\App\Http\Controllers\Admin\DisputeController::class, 'addMessage'])->name('messages.store');
        Route::post('/{dispute}/finalize', [\App\Http\Controllers\Admin\DisputeController::class, 'finalizeDispute'])->name('finalize.store');
        Route::get('/statistics', [\App\Http\Controllers\Admin\DisputeController::class, 'statistics'])->name('statistics');
    });

    // Appeals
    Route::prefix('appeals')->name('appeals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DisputeController::class, 'appeals'])->name('index');
        Route::get('/{appeal}', [\App\Http\Controllers\Admin\DisputeController::class, 'showAppeal'])->name('show');
        Route::post('/{appeal}/review', [\App\Http\Controllers\Admin\DisputeController::class, 'reviewAppeal'])->name('review.store');
        Route::post('/{appeal}/request-evidence', [\App\Http\Controllers\Admin\DisputeController::class, 'requestEvidence'])->name('request-evidence');
        Route::post('/{appeal}/close', [\App\Http\Controllers\Admin\DisputeController::class, 'closeAppeal'])->name('close');
    });

});

/*
|--------------------------------------------------------------------------
| Seller Routes - Subscription Management (No Active Subscription Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {

    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::resource('deals', DealController::class)
        ->only(['index', 'create', 'store']);

    Route::get('/products/pricing/bulk', [BulkPriceController::class, 'create'])
        ->name('products.pricing.bulk');
    Route::post('/products/pricing/bulk', [BulkPriceController::class, 'store'])
        ->name('products.pricing.bulk.store');

    // Subscription management - accessible without active subscription
    Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription');
    Route::post('subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('subscription/wallet', [SubscriptionController::class, 'walletPay'])->name('subscription.wallet.pay');
    Route::get('subscription/success/{id}', [SubscriptionController::class, 'successDeposit'])->name('subscription.success');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');


    // Shop Management
    Route::get('/shop/index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shops.store');
    Route::get('/shops/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])->name('shops.edit');
    Route::patch('/shops/{shop}', [ShopController::class, 'update'])->name('shops.update');
});

/*
|--------------------------------------------------------------------------
| Seller Routes - Active Subscription Required
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'seller', 'ensure.seller.subscription'])->prefix('seller')->name('seller.')->group(function () {
    // Dashboard & Analytics
    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Holiday Mode
    Route::post('holiday-mode/enable', [SellerDashboard::class, 'enableHolidayMode'])->name('holiday-mode.enable');
    Route::post('holiday-mode/disable', [SellerDashboard::class, 'disableHolidayMode'])->name('holiday-mode.disable');


    // Order Management
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('order/payments', [OrderController::class, 'orderPayments'])->name('orders.payments');
    Route::patch('orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'sellerCancel'])->name('orders.cancel');

    Route::resource('shipping_profiles', ShippingProfileController::class)
        ->except(['show']);
    // KYC Management
    Route::get('kyc', [KycController::class, 'show'])->name('kyc');
    Route::post('kyc', [KycController::class, 'submit'])->name('kyc.submit');

    // Payout Management
    Route::get('payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PayoutRequestController::class, 'store'])->name('payouts.store');

    // Services
    Route::resource('services', ServiceController::class);

    // Buyer Management
    Route::get('buyers', [BuyerController::class, 'index'])->name('buyers.index');
    Route::get('buyers/{buyer}', [BuyerController::class, 'show'])->name('buyers.show');

    // Offer Management
    Route::resource('offers', \App\Http\Controllers\Seller\OfferController::class);
    Route::post('offers/{offer}/accept', [\App\Http\Controllers\Seller\OfferController::class, 'accept'])->name('offers.accept');
    Route::post('offers/{offer}/decline', [\App\Http\Controllers\Seller\OfferController::class, 'decline'])->name('offers.decline');
    Route::post('offers/{offer}/counter', [\App\Http\Controllers\Seller\OfferController::class, 'counterOffer'])->name('offers.counter');
    Route::post('offers/bulk-action', [\App\Http\Controllers\Seller\OfferController::class, 'bulkAction'])->name('offers.bulk-action');
    Route::get('offers/test-bulk', [\App\Http\Controllers\Seller\OfferController::class, 'testBulkAction'])->name('offers.test-bulk');

    // Message Management
    Route::get('messages', [\App\Http\Controllers\Seller\MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{conversationId}', [\App\Http\Controllers\Seller\MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{conversationId}/reply', [\App\Http\Controllers\Seller\MessageController::class, 'reply'])->name('messages.reply');
    Route::post('messages/{message}/mark-read', [\App\Http\Controllers\Seller\MessageController::class, 'markAsRead'])->name('messages.mark-read');
    Route::post('messages/bulk-mark-read', [\App\Http\Controllers\Seller\MessageController::class, 'bulkMarkAsRead'])->name('messages.bulk-mark-read');

    // Favorites
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Payment Methods
    Route::resource('payment-methods', PaymentMethodController::class);

    // Shop Posts
    Route::resource('shop-posts', ShopPostController::class);
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
    Route::get('messages/{conversationId}', [MessageController::class, 'show'])->name('messages.show');

    // Buyer Offer Management
    Route::get('offers/available-products', [\App\Http\Controllers\Buyer\OfferController::class, 'getAvailableProducts'])->name('offers.available-products');
    Route::post('offers/{productId}/create', [\App\Http\Controllers\Buyer\OfferController::class, 'createNewOffer'])->name('offers.create');
    Route::get('offers/{offerId}/details', [\App\Http\Controllers\Buyer\OfferController::class, 'showDetails'])->name('offers.details');
    Route::post('offers/{offerId}/respond', [\App\Http\Controllers\Buyer\OfferController::class, 'respondToCounterOffer'])->name('offers.respond');
});

/*
|--------------------------------------------------------------------------
| Settings (Admin Only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->resource('settings', AdminSetting::class)
    ->only(['index', 'edit', 'update']);

require __DIR__ . '/auth.php';