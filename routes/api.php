<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserDetails;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Middleware\CheckProductOwner;

Route::post('register', [UserDetails::class, 'store']);
Route::post('login', [UserDetails::class, 'login']);

Route::middleware('auth:api')->group( function () {
    Route::resource('products', ProductController::class)->except(['destroy']);
    Route::resource('user', UserDetails::class)->except(['destroy']);
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware(CheckProductOwner::class);
    Route::middleware('auth:api')->post('/logout', [UserDetails::class, 'logout']);
    
    // Cart APIs
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::put('/cart/update/{id}', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeCartItem']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    //orders
    Route::post('/payment/order', [PaymentController::class, 'createOrder']);
    Route::post('/payment/verify', [PaymentController::class, 'verifyPayment']);
});
