<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CurrencySelectionController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\{
    HomeController,
    ProfileController,
    ShopController,
    ProductController,
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
    ProductReportController,
    ProductShippingController,
    ProductVariationController
};

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
    PaymentMethodController as AdminPaymentMethodController,
    CategoryAttributeController,
    ProductReportController as AdminProductReportController,
    AdminWalletController,
    ReviewController,
    AdminNotificationController,
    BlogPostController as AdminBlogPostController,
    BlogCategoryController as AdminBlogCategoryController,
    HeroSlideController as AdminHeroSlideController
};
use App\Http\Controllers\Webhooks\PayoutWebhookController;
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
    PaymentMethodController,
    ReviewController as SellerReviewController,
    ShopPostController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
// Currency selector (accept GET or POST; CSRF not required for this benign action)
Route::match(['GET','POST'], '/set-currency', [CurrencySelectionController::class, 'set'])
    ->name('currency.set')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
// Safaricom callback (must be reachable publicly)
Route::post('/wallet/deposit/mpesa/callback', [WalletController::class, 'mpesaCallback'])
    ->name('wallet.deposit.mpesa.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);



Route::post('/wallet/deposit/mpesa/callback', [WalletController::class, 'mpesaCallback'])
    ->name('wallet.deposit.mpesa.callback')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/wallet/deposit/mpesa/timeout',  [WalletController::class, 'mpesaTimeout'])
    ->name('wallet.deposit.mpesa.timeout')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);


// pages
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/become-seller', function () {
    return themed_view('pages.become-seller');
})->name('become-seller');
Route::get('/privacy', function () {
    return themed_view('pages.privacy');
})->name('privacy');
Route::get('/terms', function () {
    return themed_view('pages.terms');
})->name('terms');
Route::get('/refunds-returns', function () {
    return themed_view('pages.refunds-returns');
})->name('refunds-returns');
Route::get('/shipping-delivery', function () {
    return themed_view('pages.shipping-delivery');
})->name('shipping-delivery');
Route::get('/seller-policy', function () {
    return themed_view('pages.seller-policy');
})->name('seller-policy');
Route::get('/prohibited-items', function () {
    return themed_view('pages.prohibited-items');
})->name('prohibited-items');
Route::get('/contact', function () {
    return themed_view('pages.contact');
})->name('contact');
// Aliases for legacy links
Route::get('/privacy-policy', function () {
    return themed_view('pages.privacy');
})->name('privacy.policy');
Route::get('/terms-of-service', function () {
    return themed_view('pages.terms');
})->name('terms.of.service');
Route::get('/returns', function () {
    return redirect()->to('/refunds-returns');
})->name('returns.alias');
Route::get('/shipping', function () {
    return redirect()->to('/shipping-delivery');
})->name('shipping.alias');
Route::get('/intro', function () {
    // Direct to About as an intro/overview
    return themed_view('pages.about');
})->name('intro');
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

// User Agreement (footer link)
Route::get('/user-agreement', function () {
    return themed_view('pages.user-agreement');
})->name('user-agreement');

// Additional policy pages (linked from House Rules)
Route::get('/cetsyip_policy', function () {
    return themed_view('pages.cetsyip_policy');
})->name('cetsyip_policy');
Route::get('/payment_policy', function () {
    return themed_view('pages.payment_policy');
})->name('payment_policy');

// Restricted / Prohibited items
Route::get('/restricted_for_sale', function () {
    return themed_view('pages.restricted_for_sale');
})->name('restricted_for_sale');

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
    Route::get('/', [CartController::class, 'viewCart'])->name('view');
    Route::post('/add', [CartController::class, 'addToCart'])->name('add');
    Route::post('/buy', [CartController::class, 'addToBuy'])->name('buy');
    Route::post('/remove', [CartController::class, 'removeFromCart'])->name('remove');
    Route::post('/update', [CartController::class, 'updateCart'])->name('update');

    // persist per-item shipping selection to session
    Route::post('/shipping', [CartController::class, 'updateShippingSelection'])->name('shipping');

    // checkout page (requires authentication)
    Route::get('/checkout', [CartController::class, 'checkout'])
        ->middleware('auth')
        ->name('checkout');
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
Route::post('/products/{product}/shipping/rows',           [ProductShippingController::class, 'storeShippingRow'])
    ->name('products.shipping.rows.store');
Route::delete('/products/{product}/shipping/rows/{row}',     [ProductShippingController::class, 'destroyShippingRow'])
    ->name('products.shipping.rows.destroy');

Route::patch('/products/{product}/shipping/rows/{row}', [ProductShippingController::class, 'updateShippingRow'])
    ->name('products.shipping.rows.update');

Route::get('/pay-now-invoice/{total}', [OrderController::class, 'payNowInvoice'])->name('pay_now_invoice');
Route::get('/success-deposit/{id}', [OrderController::class, 'successDeposit'])->name('success_deposit');
Route::post('/success-deposit-fee/{id}', [ProductController::class, 'successDeposit'])->name('success_deposit_fee');
Route::get('/success-deposit-invoice/{id}', [OrderController::class, 'successDepositInvoice'])->name('success_deposit_invoice');
Route::middleware(['auth','seller'])->group(function () {
    Route::resource('shipping-profiles', ShippingProfileController::class)
        ->only(['store']);
});
// routes/web.php

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

// Allow all authenticated users to access dashboard (show alert if unverified)
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

Route::middleware(['auth','verified'])->group(function () {

    // Navbar unread counters (AJAX)
    Route::get('/nav/counts', [\App\Http\Controllers\NotificationController::class, 'counts'])
        ->name('notifications.counts');

    Route::patch('products/{product}/renewal', [ProductController::class, 'updateRenewal'])
        ->name('products.updateRenewal');

    Route::post('/listing/{order}/wallet', [WalletController::class, 'payListing'])
        ->name('listing.wallet.pay');

    Route::post('/order/{order}/wallet', [WalletController::class, 'payOrder'])
        ->name('order.wallet.pay');

    // Stripe Checkout (wallet top-up + pay order)
    Route::post('/order/{order}/stripe/session', [WalletController::class, 'createStripeOrderSession'])
        ->name('order.stripe.session');
    Route::get('/order/{order}/stripe/success', [WalletController::class, 'stripeOrderSuccess'])
        ->name('order.stripe.success');
    Route::get('/order/{order}/stripe/cancel', [WalletController::class, 'stripeOrderCancel'])
        ->name('order.stripe.cancel');

    Route::post('/products/{product}/status', [ProductController::class, 'changeStatus'])
        ->name('products.changeStatus');

    // Variation routes
    Route::prefix('products/{product}')->group(function () {
        Route::post('variationâ€‘types', [VariationController::class, 'storeType'])
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
    Route::delete('variationâ€‘types/{variationType}', [VariationController::class, 'destroyType'])
        ->name('variationTypes.destroy');

    Route::post('variation-types/{variationType}/options', [VariationController::class, 'storeOption'])
        ->name('variationOptions.store');
    Route::patch('variation-types/{variationType}/affects-price', [VariationController::class, 'toggleAffectsPrice'])
        ->name('variationTypes.affects_price');
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
    Route::delete('/products/{product}/media/bulk', [MediaController::class, 'bulkDestroy'])
        ->name('media.bulk-destroy');
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
        ->except(['create', 'store', 'edit'])
        ->middleware('kyc.after.two.sales');
    // Product Shipping (page + save)
    Route::get('products/{product}/shipping', [ProductController::class, 'shipping'])
        ->name('products.shipping');
    Route::patch('products/{product}/shipping', [ProductController::class, 'updateShipping'])
        ->name('products.shipping.update');
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
    Route::post('/admin/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');
    Route::post('/admin/notifications/{id}/mark-read', [AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    
    // Chat
    Route::get('/orders/{order}/chat', [OrderMessageController::class, 'show'])->name('orders.chat.show');
    Route::get('/orders/{order}/chat/messages', [OrderMessageController::class, 'fetch'])->name('orders.chat.fetch');
    Route::post('/orders/{order}/chat', [OrderMessageController::class, 'send'])->name('orders.chat.send');

    Route::patch('/products/{product}/set-featured-image', [ProductController::class, 'setFeaturedImage'])
        ->name('products.setFeaturedImage');

    // Reviews
    Route::post('/orders/{order}/items/{item}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])
        ->name('orders.items.reviews.store');
    Route::patch('/orders/{order}/items/{item}/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'update'])
        ->name('orders.items.reviews.update');
    Route::get('/shops/{shop}/reviews', [\App\Http\Controllers\ReviewController::class, 'shopReviews'])
        ->name('shop.reviews');

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/deposit', [WalletController::class, 'depositForm'])->name('deposit.form');
        Route::post('/deposit', [WalletController::class, 'storeDeposit'])->name('deposit.store');
        Route::post('/deposit/paypal', [WalletController::class, 'handlePayPalDeposit'])->name('deposit.paypal');
        Route::post('/deposit/stripe/session', [WalletController::class, 'createStripeDepositSession'])->name('deposit.stripe.session');
        Route::get('/deposit/stripe/success', [WalletController::class, 'stripeDepositSuccess'])->name('deposit.stripe.success');
        Route::get('/deposit/stripe/cancel', [WalletController::class, 'stripeDepositCancel'])->name('deposit.stripe.cancel');

        // Payout OTP gate (pre-modal)
        Route::get('/payout/verify', [WalletController::class, 'payoutOtpForm'])->name('payout.otp.form');
        Route::post('/payout/otp-verify', [WalletController::class, 'verifyPayoutOtp'])->name('payout.otp.verify');
        Route::post('/payout/otp-resend', [WalletController::class, 'resendPayoutOtp'])->name('payout.otp.resend');

    
    });

    Route::post('/wallet/deposit/mpesa/stk', [WalletController::class, 'startMpesaStk'])
        ->name('wallet.deposit.mpesa.stk');

    // Poll status (frontend â€œlistensâ€ by polling this)
    Route::get('/wallet/deposit/mpesa/status/{ref}', [WalletController::class, 'mpesaStatus'])->name('wallet.deposit.mpesa.status');

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

    // Buyer favorites and offers
    Route::get('buyer/favorites', [ProductController::class, 'favorites'])->name('buyer.favorites');
    Route::get('buyer/offers', [ProductController::class, 'offers'])->name('buyer.offers');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/open/{id}', [\App\Http\Controllers\NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Disputes - Keep only this to test
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DisputeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DisputeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [\App\Http\Controllers\DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/messages', [\App\Http\Controllers\DisputeController::class, 'addMessage'])->name('messages.store');
        // Escalate to support
        Route::post('/{dispute}/contact-support', [\App\Http\Controllers\DisputeController::class, 'contactSupport'])->name('contact-support');
        if (config('disputes.enable_appeals')) {
            Route::get('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'showAppealForm'])->name('appeal.create');
            Route::post('/{dispute}/appeal', [\App\Http\Controllers\DisputeController::class, 'submitAppeal'])->name('appeal.store');
        }
        Route::post('/{dispute}/mutual-resolution', [\App\Http\Controllers\DisputeController::class, 'initiateMutualResolution'])->name('mutual-resolution.initiate');
        Route::post('/{dispute}/mutual-resolution/agree', [\App\Http\Controllers\DisputeController::class, 'agreeToMutualResolution'])->name('mutual-resolution.agree');

        // Seller refund acceptance (partial or full refund to buyer wallet)
        Route::post('/{dispute}/refund', [\App\Http\Controllers\DisputeController::class, 'refund'])->name('refund');
        // Buyer response to refund proposals
        Route::post('/{dispute}/refund-proposal/accept', [\App\Http\Controllers\DisputeController::class, 'acceptRefundProposal'])->name('refund-proposal.accept');
        Route::post('/{dispute}/refund-proposal/decline', [\App\Http\Controllers\DisputeController::class, 'declineRefundProposal'])->name('refund-proposal.decline');

        // Mark Dispute as Closed
        Route::post('/{dispute}/close', [\App\Http\Controllers\DisputeController::class, 'markAsClosed'])->name('close');

        // Evidence Request Responses
        // Evidence responses (enabled regardless of appeals)
        Route::post('/evidence-requests/{evidenceRequest}/respond', [\App\Http\Controllers\EvidenceRequestController::class, 'respond'])->name('evidence-requests.respond');
        // Admin evidence request from dispute page (admin only)
        Route::post('/{dispute}/request-evidence', [\App\Http\Controllers\DisputeController::class, 'requestEvidence'])->name('request-evidence');
        // Admin assign dispute
        Route::post('/{dispute}/assign-admin', [\App\Http\Controllers\DisputeController::class, 'assignAdmin'])->name('assign-admin');
    });
});

// Admin routes for user management additions
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Resend email verification for a specific user
    Route::post('/users/{user}/resend-verification', [UserController::class, 'resendVerification'])
        ->name('users.resend-verification');

    // Toggle email verification status
    Route::post('/users/{user}/mark-verified', [UserController::class, 'markEmailVerified'])
        ->name('users.mark-verified');
    Route::post('/users/{user}/mark-unverified', [UserController::class, 'markEmailUnverified'])
        ->name('users.mark-unverified');
});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Admin Wallets: enable create/store so admins can top up sellers
    Route::resource('wallets', AdminWalletController::class);
    Route::delete('wallets/bulk', [AdminWalletController::class, 'bulk'])->name('wallets.bulk');
    Route::patch('kyc/bulk', [KycController::class, 'bulk'])->name('kyc.bulk');

    // Users
    Route::resource('users', UserController::class);
    // Buyers index alias, reusing the same controller (filters buyers by default)
    Route::get('buyers', [UserController::class, 'index'])->name('buyers.index');
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::get('sellers/{userId}/login-as', [UserController::class, 'loginAs'])->name('sellers.login-as');
    Route::get('return-from-impersonation', [UserController::class, 'returnFromImpersonation'])->name('return-from-impersonation');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('blog-posts', AdminBlogPostController::class);
    Route::resource('blog-categories', AdminBlogCategoryController::class)->except(['show']);
    Route::resource('hero-slides', AdminHeroSlideController::class)->except(['show']);
    Route::post('products/{product}/toggle-status', [\App\Http\Controllers\Admin\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('products/bulk-status', [\App\Http\Controllers\Admin\ProductController::class, 'bulkStatus'])->name('products.bulk-status');
    // Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    /* update + destroy â€” shallow, no category prefix */
    Route::put(
        '/category-attributes/{attribute}',
        [CategoryAttributeController::class, 'update']
    )->name('category-attributes.update');

    Route::delete(
        '/category-attributes/{attribute}',
        [CategoryAttributeController::class, 'destroy']
    )->name('category-attributes.destroy');
    Route::resource('categories', CategoryController::class);
    // Bulk update and move categories
    Route::post('categories/bulk-update', [CategoryController::class, 'bulkUpdate'])->name('categories.bulk-update');
    Route::post('categories/bulk-move',   [CategoryController::class, 'bulkMove'])->name('categories.bulk-move');
    // Bulk update categories (listing_fee, listing_type, listing_frequency)
    Route::post('categories/bulk-update', [CategoryController::class, 'bulkUpdate'])->name('categories.bulk-update');

    // KYC
    Route::get('kyc', [KycController::class, 'index'])->name('kyc.index');
    Route::get('kyc/{kyc}', [KycController::class, 'show'])->name('kyc.show');
    Route::patch('kyc/{kyc}', [KycController::class, 'update'])->name('kyc.update');
    Route::get('kyc/{kyc}', [KycController::class, 'showDetails'])->name('kyc.showDetails');

    // User Agreement (Policies)
    Route::get('user-agreement', [\App\Http\Controllers\Admin\PolicySectionController::class, 'index'])->name('policies.index');
    Route::get('user-agreement/{slug}/edit', [\App\Http\Controllers\Admin\PolicySectionController::class, 'edit'])->name('policies.edit');
    Route::put('user-agreement/{slug}', [\App\Http\Controllers\Admin\PolicySectionController::class, 'update'])->name('policies.update');
    // Settings, Reports
    Route::get('settings', [AdminSetting::class, 'index'])->name('settings');
    Route::put('settings/{setting}', [AdminSetting::class, 'update'])->name('settings.update');
    Route::get('reports', [AdminReport::class, 'index'])->name('reports');
    Route::get('reports/mrr', [AdminSubscriptionController::class, 'mrr'])->name('reports.mrr');
    Route::get('reports/mrr/{ym}/shops', [AdminSubscriptionController::class, 'mrrShops'])->name('reports.mrr.shops');
    Route::get('reports/mrr/{ym}/shops/export', [AdminSubscriptionController::class, 'mrrShopsExport'])->name('reports.mrr.shops.export');
    // Listing fee revenue
    Route::get('reports/listing-fees', [AdminSubscriptionController::class, 'listingFees'])->name('reports.listing-fees');
    Route::get('reports/transaction-fees', [\App\Http\Controllers\Admin\TransactionFeeReportController::class, 'index'])->name('reports.transaction-fees');
    Route::get('reports/transaction-fees/export', [\App\Http\Controllers\Admin\TransactionFeeReportController::class, 'export'])->name('reports.transaction-fees.export');
    Route::get('reports/listing-fees/{ym}/payments', [AdminSubscriptionController::class, 'listingFeesPayments'])->name('reports.listing-fees.payments');
    Route::get('reports/listing-fees/{ym}/export', [AdminSubscriptionController::class, 'listingFeesExport'])->name('reports.listing-fees.export');
    Route::get('reports/inventory', [\App\Http\Controllers\Admin\InventoryReportController::class, 'index'])->name('reports.inventory');
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('reviews/bulk-approve', [ReviewController::class, 'bulkApprove'])->name('reviews.bulk-approve');
    Route::post('reviews/bulk-delete', [ReviewController::class, 'bulkDelete'])->name('reviews.bulk-delete');

    // Messages
    Route::get('messages', [\App\Http\Controllers\Admin\AdminMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{conversation}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'show'])->name('messages.show');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');

    // Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    // Make sure you have this route defined
    Route::post('subscriptions/deactivate-expired', [AdminSubscriptionController::class, 'deactivateExpired'])->name('subscriptions.deactivate-expired');

    // Payout Requests
    Route::prefix('payout-requests')->name('payouts.')->controller(AdminPayoutRequestController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{payout}', 'show')->name('show');
        Route::post('/{payout}/approve', 'approve')->name('approve');
        Route::post('/{payout}/reject', 'reject')->name('reject');
        Route::post('/{payout}/paid', 'markPaid')->name('paid');
        Route::post('/{payout}/resend', 'resendAuto')->name('resend');
        Route::post('/{payout}/fail', 'fail')->name('fail');
    });
        Route::get('payout-requests/export', [AdminPayoutRequestController::class, 'export'])->name('payouts.export');
        Route::post('payout-requests/bulk-approve', [AdminPayoutRequestController::class, 'bulkApprove'])->name('payouts.bulk-approve');
        Route::post('payout-requests/bulk-reject', [AdminPayoutRequestController::class, 'bulkReject'])->name('payouts.bulk-reject');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    // Seller payment methods (admin view)
    Route::get('payment-methods', [AdminPaymentMethodController::class, 'index'])->name('payment-methods.index');

    //Payment Types
    Route::resource('payment-types', PaymentTypeController::class);

    // Product Reports
    Route::get('product-reports', [AdminProductReportController::class, 'index'])->name('product-reports.index');
    Route::put('product-reports/{id}', [AdminProductReportController::class, 'update'])->name('product-reports.update');

    // Product Activities (audits)
    Route::get('product-activities', [\App\Http\Controllers\Admin\ProductActivityController::class, 'index'])->name('product-activities.index');
    Route::get('product-activities/{activity}', [\App\Http\Controllers\Admin\ProductActivityController::class, 'show'])->name('product-activities.show');

    // Admin Disputes
    Route::prefix('admin-disputes')->name('admin-disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('index');
        Route::get('/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('show');
        Route::get('/{dispute}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'showResolveForm'])->name('resolve.create');
        Route::post('/{dispute}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'resolve'])->name('resolve.store');
        Route::post('/{dispute}/messages', [\App\Http\Controllers\Admin\DisputeController::class, 'addMessage'])->name('messages.store');
        if (config('disputes.enable_appeals')) {
            Route::post('/{dispute}/finalize', [\App\Http\Controllers\Admin\DisputeController::class, 'finalizeDispute'])->name('finalize.store');
        }
        Route::get('/statistics', [\App\Http\Controllers\Admin\DisputeController::class, 'statistics'])->name('statistics');
    });

    // Appeals
    if (config('disputes.enable_appeals')) {
        Route::prefix('appeals')->name('appeals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DisputeController::class, 'appeals'])->name('index');
            Route::get('/{appeal}', [\App\Http\Controllers\Admin\DisputeController::class, 'showAppeal'])->name('show');
            Route::post('/{appeal}/review', [\App\Http\Controllers\Admin\DisputeController::class, 'reviewAppeal'])->name('review.store');
            Route::post('/{appeal}/request-evidence', [\App\Http\Controllers\Admin\DisputeController::class, 'requestEvidence'])->name('request-evidence');
            Route::post('/{appeal}/close', [\App\Http\Controllers\Admin\DisputeController::class, 'closeAppeal'])->name('close');
        });
    }

});
Route::post('/admin/categories/{category}/attributes', [CategoryAttributeController::class, 'store'])
    ->name('admin.categories.attributes.store');
/*
|--------------------------------------------------------------------------

| Seller Routes - Subscription Management (No Active Subscription Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    // Subscription management - accessible without active subscription only
    Route::get('billing', function () {
        return view('seller.billing');
    })->name('billing.index');
    Route::get('subscription', [SubscriptionController::class, 'show'])->name('subscription');
    Route::post('subscription', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('subscription/wallet', [SubscriptionController::class, 'walletPay'])->name('subscription.wallet.pay');
    Route::get('subscription/success/{id}', [SubscriptionController::class, 'successDeposit'])->name('subscription.success');
    Route::post('subscription/trial', [SubscriptionController::class, 'startTrial'])->name('subscription.trial');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // Seller notifications (accessible without active subscription)
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Seller KYC (accessible without active subscription)
    Route::get('kyc', [KycController::class, 'show'])->name('kyc');
    // 2-step KYC
    Route::get('kyc/info', [KycController::class, 'info'])->name('kyc.info');
    Route::post('kyc/info', [KycController::class, 'postInfo'])->name('kyc.info.submit');
    Route::get('kyc/documents', [KycController::class, 'documents'])->name('kyc.documents');
    Route::post('kyc/documents', [KycController::class, 'postDocuments'])->name('kyc.documents.submit');
    // Legacy single-submit (kept for compatibility)
    Route::post('kyc', [KycController::class, 'submit'])->name('kyc.submit');

    // Seller order history (read-only; accessible without active subscription)
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Seller Routes - Active Subscription Required
|--------------------------------------------------------------------------
*/
// Seller dashboard accessible to authenticated sellers (email may be unverified)
Route::middleware(['auth', 'seller', 'ensure.seller.subscription'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('dashboard', [SellerDashboard::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'seller', 'ensure.seller.subscription'])->prefix('seller')->name('seller.')->group(function () {
    // Dashboard & Analytics
    // (dashboard route defined above without 'verified' to allow access with alert)
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Seller inventory report
    Route::get('reports/inventory', [\App\Http\Controllers\Seller\InventoryReportController::class, 'index'])->name('reports.inventory');

    // Deals (require active subscription)
    Route::resource('deals', DealController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::post('deals/{deal}/stop', [DealController::class, 'stop'])->name('deals.stop');
    Route::get('deals/products/search', [DealController::class, 'searchProducts'])->name('deals.products.search');

    // Bulk pricing (require active subscription)
    Route::get('/products/pricing/bulk', [BulkPriceController::class, 'create'])->name('products.pricing.bulk');
    Route::post('/products/pricing/bulk', [BulkPriceController::class, 'store'])->name('products.pricing.bulk.store');

    // Holiday Mode
    Route::post('holiday-mode/enable', [SellerDashboard::class, 'enableHolidayMode'])->name('holiday-mode.enable');
    Route::post('holiday-mode/disable', [SellerDashboard::class, 'disableHolidayMode'])->name('holiday-mode.disable');

    // Shop Management (require active subscription)
    Route::get('/shop/index', [ShopController::class, 'index'])->name('shops.index');
    Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shops.store');
    Route::get('/shops/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');
    Route::get('/shops/{shop}/edit', [ShopController::class, 'edit'])->name('shops.edit');
    Route::patch('/shops/{shop}', [ShopController::class, 'update'])->name('shops.update');

    
    // Order Management (actions require active subscription)
    Route::get('order/payments', [OrderController::class, 'orderPayments'])->name('orders.payments');
    Route::patch('orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
    Route::post('orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
    Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.tracking');
    Route::patch('orders/{order}/cancel', [OrderController::class, 'sellerCancel'])->name('orders.cancel');

    Route::resource('shipping_profiles', ShippingProfileController::class)
        ->except(['show']);
    // Payout Management
    Route::get('payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PayoutRequestController::class, 'store'])->name('payouts.store');
    // OTP routes defined in unguarded auth group below to ensure access without subscription

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
    Route::post('messages/{message}/mark-unread', [\App\Http\Controllers\Seller\MessageController::class, 'markAsUnread'])->name('messages.mark-unread');
    Route::post('messages/bulk-mark-read', [\App\Http\Controllers\Seller\MessageController::class, 'bulkMarkAsRead'])->name('messages.bulk-mark-read');

    // Favorites
    Route::get('favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Reviews
    Route::get('reviews', [SellerReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/respond', [SellerReviewController::class, 'respond'])->name('reviews.respond');

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
// Buyer dashboard accessible to authenticated users (email may be unverified)
Route::middleware(['auth'])->prefix('buyer')->name('buyer.')->group(function () {
    Route::get('dashboard', [BuyerDashboard::class, 'index'])->name('dashboard');
});

Route::middleware(['auth','verified'])->prefix('buyer')->name('buyer.')->group(function () {

    // Buyer notifications (view + mark as read)
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('orders/created', [OrderController::class, 'createdSummary'])->name('orders.created');
    Route::get('orders/{order}', [AccountController::class, 'orderDetails'])->name('orders.show');
    Route::post('orders/{order}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
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
Route::middleware(['auth','admin'])->resource('settings', AdminSetting::class)
    ->only(['index', 'edit', 'update']);

// Webhooks (public endpoints)
Route::post('/webhooks/paypal', [PayoutWebhookController::class, 'paypal'])
    ->name('webhooks.paypal')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/daraja/b2c/result', [PayoutWebhookController::class, 'darajaB2CResult'])
    ->name('webhooks.daraja.b2c.result')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
Route::post('/daraja/b2c/timeout', [PayoutWebhookController::class, 'darajaB2CTimeout'])
    ->name('webhooks.daraja.b2c.timeout')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);




// Fallback OTP routes (ensure named routes exist even if group middleware changes)
Route::middleware(['auth','seller'])->group(function () {
    Route::get('/seller/payouts/{payout}/verify', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'verifyForm'])->name('seller.payouts.otp.verify');
    Route::post('/seller/payouts/{payout}/verify', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'verifyOtp'])->name('seller.payouts.otp.submit');
    Route::post('/seller/payouts/{payout}/resend-otp', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'resendOtp'])->name('seller.payouts.otp.resend');
    Route::post('/seller/payouts/{payout}/cancel', [\App\Http\Controllers\Seller\PayoutRequestController::class, 'cancel'])->name('seller.payouts.otp.cancel');
});

// Local session debug helpers (safe to keep; only for local env)
if (app()->environment('local')) {
    Route::get('/__session/test', function () {
        session(['__ok' => now()->toISOString()]);
        return response('session set');
    });
    Route::post('/__session/test', function () {
        return session()->has('__ok') ? response('session ok') : response('session missing', 400);
    });
}






require __DIR__ . '/auth.php';

   

// Admin utilities for managing users' email verification
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/users/{user}/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
    Route::post('/users/{user}/mark-verified', [UserController::class, 'markEmailVerified'])->name('users.mark-verified');
    Route::post('/users/{user}/mark-unverified', [UserController::class, 'markEmailUnverified'])->name('users.mark-unverified');
});
