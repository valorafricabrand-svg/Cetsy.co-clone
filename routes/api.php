<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\CategoryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

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
    Route::get('/orders', [\App\Http\Controllers\API\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\API\OrderController::class, 'store']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [PasswordController::class, 'change']);
    Route::post('/change-email', [ProfileController::class, 'changeEmail']);
});
