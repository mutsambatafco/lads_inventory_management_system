<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryAdjustmentController;
use App\Http\Controllers\Api\DashboardController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Products CRUD
    Route::apiResource('products', ProductController::class);

    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);

    // Inventory adjustments
    Route::post('inventory-adjustments', [InventoryAdjustmentController::class, 'store']);

    // Dashboard summary
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
});
