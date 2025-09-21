<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\MetaController;
use App\Http\Controllers\API\SettingsController;
use App\Http\Controllers\API\CurrencyController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\WalletController as ApiWalletController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\CategoryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/meta/countries', [MetaController::class, 'countries']);
Route::get('/meta/currencies', [MetaController::class, 'currencies']);
Route::get('/settings/currency', [SettingsController::class, 'currency']);

// Password reset (JSON for mobile)
Route::post('/forgot-password', [PasswordController::class, 'forgot']);
Route::post('/reset-password', [PasswordController::class, 'reset']);

// routes/web.php

Route::get('/categories/by-type/{type}', [CategoryController::class, 'byType']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn () => request()->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/my/products', [ProductController::class, 'myProducts']);
    Route::post('/settings/currency', [SettingsController::class, 'updateCurrency']);
    // Manage currencies (admin/auth scope; add policy as needed)
    Route::get('/admin/currencies', [CurrencyController::class, 'index']);
    Route::post('/admin/currencies', [CurrencyController::class, 'upsert']);
    // Listing management endpoints (mirror web features)
    Route::post('/products/{product}/settings', [ProductController::class, 'updateSettings']);
    Route::post('/products/{product}/details', [ProductController::class, 'updateDetails']);
    Route::post('/products/{product}/variations', [ProductController::class, 'saveVariations']);
    Route::post('/products/{product}/shipping', [ProductController::class, 'updateShipping']);
    Route::post('/products/{product}/media', [ProductController::class, 'uploadMedia']);
    Route::delete('/products/{product}/media/{media}', [ProductController::class, 'destroyMedia']);
    Route::post('/products/{product}/media/reorder', [ProductController::class, 'reorderMedia']);
    Route::post('/products/{product}/digital-file', [ProductController::class, 'uploadDigitalFile']);
    Route::get('/orders', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\API\OrderController::class, 'store']);
    Route::get('/orders/{order}', [\App\Http\Controllers\API\OrderController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [PasswordController::class, 'change']);
    Route::post('/change-email', [ProfileController::class, 'changeEmail']);

    // Wallet
    Route::get('/wallet/summary', [ApiWalletController::class, 'summary']);
    Route::post('/wallet/payout', [ApiWalletController::class, 'requestPayout']);
    Route::post('/wallet/payout/{payout}/verify', [ApiWalletController::class, 'verifyPayoutOtp']);
    Route::post('/wallet/payout/{payout}/resend-otp', [ApiWalletController::class, 'resendPayoutOtp']);
    Route::post('/wallet/payout/{payout}/cancel', [ApiWalletController::class, 'cancelPayout']);
    Route::get('/wallet/transactions', [ApiWalletController::class, 'transactions']);
    Route::get('/wallet/paypal/config', [ApiWalletController::class, 'paypalConfig']);
    // Pay order via wallet (mirror web route)
    Route::post('/orders/{order}/wallet', [\App\Http\Controllers\WalletController::class, 'payOrder']);
    // Map M-Pesa STK deposit to existing web controller actions
    Route::post('/wallet/deposit/mpesa/stk', [\App\Http\Controllers\WalletController::class, 'startMpesaStk']);
    Route::get('/wallet/deposit/mpesa/status/{ref}', [\App\Http\Controllers\WalletController::class, 'mpesaStatus']);
    // PayPal manual deposit (same logic as web)
    Route::post('/wallet/deposit/paypal', [\App\Http\Controllers\WalletController::class, 'handlePayPalDeposit']);
    // Seller stats
    Route::get('/seller/stats', [StatsController::class, 'seller']);
});
