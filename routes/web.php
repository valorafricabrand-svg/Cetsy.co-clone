<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    HomeController,
    ProfileController,
    ShopController,

    CategoryController,
    CartController,
    CheckoutController,
    OrderController,
    DashboardController,
    WalletController,
    OrderMessageController,
    AccountController,
    ProductInfoController,
    MpesaController,
    MediaController,
    DigitalFileController,
    ShippingProfileController,
    WishlistController,
    OfferController,
    MessageController,
    VariationController,
    DealController,
    BulkPriceController,
    NotificationController,
    ProductReportController,
    ProductShippingController,
    ProductVariationController
};
use App\Http\Controllers\admin\ProductController;

use App\Http\Controllers\Admin\{
    AdminMessageController,
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
    ReviewController as AdminReviewController,
    NotificationController as AdminNotificationController,
    DisputeController
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

// messages route



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Seller Messages
    Route::prefix('seller')->name('seller.')->group(function () {
        Route::get('messages', [SellerMessageController::class, 'index'])->name('messages.index');
    });

    // Admin Messages
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
    });


    // Buyer Messages
    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::get('messages', [BuyerMessageController::class, 'index'])->name('messages.index');
    });

});
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('messages/{id}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
});




Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/wallet/deposit/mpesa/callback', [WalletController::class, 'mpesaCallback'])
    ->name('wallet.deposit.mpesa.callback');

// pages
Route::get('/become-seller', fn() => themed_view('pages.become-seller'))->name('become-seller');
Route::get('/privacy', fn() => themed_view('pages.privacy'))->name('privacy');
Route::get('/terms', fn() => themed_view('pages.terms'))->name('terms');
Route::get('/seller-forum', fn() => themed_view('pages.seller-forum'))->name('seller-forum');
Route::get('/seller-tips', fn() => themed_view('pages.seller-tips'))->name('seller-tips');
Route::get('/buyer-tips', fn() => themed_view('pages.buyer-tips'))->name('buyer-tips');
Route::get('/buyer-terms', fn() => themed_view('pages.buyer-terms'))->name('buyer-terms');
Route::get('/about', fn() => themed_view('pages.about'))->name('about');
Route::get('/house-policy', fn() => themed_view('pages.house-policy'))->name('house-policy');

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
    Route::get('/',        [CartController::class, 'viewCart'])->name('view');
    Route::post('/add',    [CartController::class, 'addToCart'])->name('add');
    Route::post('/buy',    [CartController::class, 'addToBuy'])->name('buy');
    Route::post('/remove', [CartController::class, 'removeFromCart'])->name('remove');
    Route::post('/update', [CartController::class, 'updateCart'])->name('update');
    Route::post('/shipping', [CartController::class, 'updateShippingSelection'])->name('shipping');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
});

// Attribute template
Route::get('/categories/{id}/attribute-template', [CategoryController::class, 'attributeTemplate'])->name('categories.attributeTemplate');

// Wishlist
Route::get('/wishlist', [ProductController::class, 'wishlist'])->name('wishlist');

// Payment routes
Route::get('/pay-now/{total}', [OrderController::class, 'payNow'])->name('pay_now');
Route::post('/products/{product}/pay-fee', [ProductController::class, 'payFee'])->name('products.pay-fee');

// Product Reports
Route::post('/product-reports', [ProductReportController::class, 'store'])->name('product-reports.store');

// Product detail routes
Route::prefix('products/{product}')->name('products.')->group(function () {
    Route::get('/pricing',     [ProductController::class, 'pricing'])->name('pricing');
    Route::get('/variations',  [ProductController::class, 'variations'])->name('variations');
    Route::get('/details',     [ProductController::class, 'details'])->name('details');
    Route::get('/shipping',    [ProductController::class, 'shipping'])->name('shipping');
    Route::get('/settings',    [ProductController::class, 'settings'])->name('settings');
    Route::get('/media',       [ProductController::class, 'media'])->name('media');
    Route::patch('/pricing',     [ProductController::class, 'updatePricing'])->name('pricing.update');
    Route::patch('/variations',  [ProductController::class, 'updateVariations'])->name('variations.update');
    Route::patch('/details',     [ProductController::class, 'updateDetails'])->name('details.update');
    Route::patch('/shipping',    [ProductController::class, 'updateShipping'])->name('shipping.update');
    Route::patch('/settings',    [ProductController::class, 'updateSettings'])->name('settings.update');
});

Route::get('/products/{product}/variation-types/{type}/manage', [ProductVariationController::class, 'manage'])
    ->name('products.variations.manage');

// Product shipping rows
Route::post('/products/{product}/shipping/rows', [ProductShippingController::class, 'storeShippingRow'])->name('products.shipping.rows.store');
Route::delete('/products/{product}/shipping/rows/{row}', [ProductShippingController::class, 'destroyShippingRow'])->name('products.shipping.rows.destroy');
Route::patch('/products/{product}/shipping/rows/{row}', [ProductShippingController::class, 'updateShippingRow'])->name('products.shipping.rows.update');

// Payment success routes
Route::get('/pay-now-invoice/{total}', [OrderController::class, 'payNowInvoice'])->name('pay_now_invoice');
Route::get('/success-deposit/{id}', [OrderController::class, 'successDeposit'])->name('success_deposit');
Route::post('/success-deposit-fee/{id}', [ProductController::class, 'successDeposit'])->name('success_deposit_fee');
Route::get('/success-deposit-invoice/{id}', [OrderController::class, 'successDepositInvoice'])->name('success_deposit_invoice');

// Shipping profile
Route::resource('shipping-profiles', ShippingProfileController::class)->only(['store']);

// Public Reviews (non-admin)
Route::get('reviews/create', [\App\Http\Controllers\ReviewController::class, 'create'])->name('reviews.create');
Route::post('reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
Route::delete('reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::patch('products/{product}/renewal', [ProductController::class, 'updateRenewal'])->name('products.updateRenewal');
    Route::post('/listing/{order}/wallet', [WalletController::class, 'payListing'])->name('listing.wallet.pay');
    Route::post('/order/{order}/wallet', [WalletController::class, 'payOrder'])->name('order.wallet.pay');
    Route::post('/products/{product}/status', [ProductController::class, 'changeStatus'])->name('products.changeStatus');

    Route::prefix('products/{product}')->group(function () {
        Route::post('variation-types', [VariationController::class, 'storeType'])->name('variationTypes.store');
        Route::post('variations', [VariationController::class, 'store'])->name('variations.store');
        Route::post('variations/bulk', [VariationController::class, 'bulkStore'])->name('variations.bulkStore');
    });

    Route::patch('variations/{variation}', [VariationController::class, 'update'])->name('variations.update');
    Route::delete('variations/{variation}', [VariationController::class, 'destroy'])->name('variations.destroy');
    Route::delete('variation-types/{variationType}', [VariationController::class, 'destroyType'])->name('variationTypes.destroy');
    Route::post('variation-types/{variationType}/options', [VariationController::class, 'storeOption'])->name('variationOptions.store');
    Route::patch('variation-options/{option}', [VariationController::class, 'updateOption'])->name('variationOptions.update');
    Route::delete('variation-options/{option}', [VariationController::class, 'destroyOption'])->name('variationOptions.destroy');

    Route::post('/favorites/toggle', [WishlistController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{wishlist}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/products/{product}/media', [MediaController::class, 'upload'])->name('media.upload');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::delete('/digital-files/{digitalFile}', [DigitalFileController::class, 'destroy'])->name('digital-files.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products
    Route::get('products/create', [ProductController::class, 'create'])->middleware('kyc.after.two.sales')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('kyc.after.two.sales')->name('products.store');
    Route::resource('products', ProductController::class)->except(['create', 'store'])->middleware('kyc.after.two.sales');
    Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::post('media/{media}/crop', [MediaController::class, 'crop'])->name('media.crop')->middleware('auth');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/order', [OrderController::class, 'storeOrder'])->name('store_order');
    Route::get('/downloads/{file}', [DigitalFileController::class, 'download'])->name('digital-files.download');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Chat
    Route::get('/orders/{order}/chat', [OrderMessageController::class, 'show'])->name('orders.chat.show');
    Route::get('/orders/{order}/chat/messages', [OrderMessageController::class, 'fetch'])->name('orders.chat.fetch');
    Route::post('/orders/{order}/chat', [OrderMessageController::class, 'send'])->name('orders.chat.send');
    Route::patch('/products/{product}/set-featured-image', [ProductController::class, 'setFeaturedImage'])->name('products.setFeaturedImage');

    // User Reviews
    Route::post('/orders/{order}/items/{item}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('orders.items.reviews.store');
    Route::get('/shops/{shop}/reviews', [\App\Http\Controllers\ReviewController::class, 'shopReviews'])->name('shop.reviews');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'depositForm'])->name('deposit.form');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
        Route::post('/deposit/paypal', [WalletController::class, 'handlePayPalDeposit'])->name('deposit.paypal');
    });
    Route::post('/wallet/deposit/mpesa/stk', [WalletController::class, 'startMpesaStk'])->name('wallet.deposit.mpesa.stk');

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

    // Notifications (user notifications)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Disputes
    Route::prefix('disputes')->name('disputes.')->group(function () {

        Route::get('/', [\App\Http\Controllers\DisputeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DisputeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [\App\Http\Controllers\DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/messages', [\App\Http\Controllers\DisputeController::class, 'addMessage'])->name('messages.store');
        Route::get('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'showAppealForm'])->name('appeal.create');
        Route::post('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'submitAppeal'])->name('appeal.store');
        Route::post('/{dispute}/mutual-resolution', [\App\Http\Controllers\DisputeController::class, 'initiateMutualResolution'])->name('mutual-resolution.initiate');
        Route::post('/{dispute}/mutual-resolution/agree', [\App\Http\Controllers\DisputeController::class, 'agreeToMutualResolution'])->name('mutual-resolution.agree');

    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Admin Reviews - FIXED ROUTES
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{id}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Wallets
    Route::resource('wallets', AdminWalletController::class)->except(['create', 'store']);
    Route::delete('wallets/bulk', [AdminWalletController::class, 'bulk'])->name('wallets.bulk');
    Route::patch('kyc/bulk', [KycController::class, 'bulk'])->name('kyc.bulk');

    // Users
    Route::resource('users', UserController::class);
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::get('sellers/{userId}/login-as', [UserController::class, 'loginAs'])->name('sellers.login-as');
    Route::get('return-from-impersonation', [UserController::class, 'returnFromImpersonation'])->name('return-from-impersonation');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Admin Notifications
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('notifications/{id}/mark-read', [AdminNotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::get('notifications/recent', [AdminNotificationController::class, 'getRecent'])->name('notifications.recent');

    // Category attributes
    Route::put('category-attributes/{attribute}', [CategoryAttributeController::class, 'update'])->name('category-attributes.update');
    Route::delete('category-attributes/{attribute}', [CategoryAttributeController::class, 'destroy'])->name('category-attributes.destroy');
    Route::resource('categories', CategoryController::class);

    // KYC
    Route::get('kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])->name('kyc.showDetails');

    // Settings, Reports
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
    Route::resource('payment-types', PaymentTypeController::class);

    // Product Reports
    Route::get('product-reports', [AdminProductReportController::class, 'index'])->name('product-reports.index');
    Route::put('product-reports/{id}', [AdminProductReportController::class, 'update'])->name('product-reports.update');

    // Admin Disputes
    Route::prefix('admin-disputes')->name('admin-disputes.')->group(function () {
        Route::get('/', [DisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [DisputeController::class, 'show'])->name('show');
        Route::get('/{dispute}/resolve', [DisputeController::class, 'showResolveForm'])->name('resolve.create');
        Route::post('/{dispute}/resolve', [DisputeController::class, 'resolve'])->name('resolve.store');
        Route::post('/{dispute}/messages', [DisputeController::class, 'addMessage'])->name('messages.store');
        Route::post('/{dispute}/finalize', [DisputeController::class, 'finalizeDispute'])->name('finalize.store');
        Route::get('/statistics', [DisputeController::class, 'statistics'])->name('statistics');
    });

    // Appeals
    Route::prefix('appeals')->name('appeals.')->group(function () {
        Route::get('/', [DisputeController::class, 'appeals'])->name('index');
        Route::get('/{appeal}', [DisputeController::class, 'showAppeal'])->name('show');
        Route::post('/{appeal}/review', [DisputeController::class, 'reviewAppeal'])->name('review.store');
    });
});

/*
|--------------------------------------------------------------------------
| Seller Routes - Subscription Management (No Active Subscription Required)
|--------------------------------------------------------------------------
*/
// ... (unchanged seller and buyer routes below)

// ------------------------------
// ADD THIS AT THE END FOR LOGOUT
// ------------------------------
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

require __DIR__ . '/auth.php';
