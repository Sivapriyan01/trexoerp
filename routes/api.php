<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebsiteOrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── Square ERP Website Order Management APIs (v1) ────────────────
Route::prefix('v1')->group(function () {
    Route::get('/health',              [WebsiteOrderController::class, 'health']);
    Route::get('/products',            [WebsiteOrderController::class, 'getProducts']);
    Route::post('/orders',             [WebsiteOrderController::class, 'createOrder']);
    Route::get('/orders/{id}',         [WebsiteOrderController::class, 'getOrder']);
    Route::post('/orders/{id}/cancel', [WebsiteOrderController::class, 'cancelOrder']);
    Route::post('/orders/{id}/return', [WebsiteOrderController::class, 'returnOrder']);
    Route::post('/otp/send',           [WebsiteOrderController::class, 'sendOtp']);
    Route::post('/otp/verify',         [WebsiteOrderController::class, 'verifyOtp']);
    Route::get('/settings',            [WebsiteOrderController::class, 'getSettings']);
    Route::put('/settings',            [WebsiteOrderController::class, 'updateSettings']);  // Live preview/configurator
    Route::get('/templates',           [WebsiteOrderController::class, 'getTemplates']);    // Template gallery data
    Route::get('/assets/{path}',       [WebsiteOrderController::class, 'getAsset'])->where('path', '.*'); // Serve tenant product & store assets
});


