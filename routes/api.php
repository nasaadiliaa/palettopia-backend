<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =================================================================
// 1. PUBLIC ROUTES (Bisa diakses tanpa Login)
// =================================================================

Route::post('/register', [AuthController::class, 'register']);

// PENTING: Login harus DI LUAR middleware auth:sanctum
Route::post('/login', [AuthController::class, 'login']); 

// Public product & content endpoints
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/recommendations', [RecommendationController::class, 'byPalette']);


// =================================================================
// 2. PROTECTED ROUTES (Wajib Login / Punya Cookie Session)
// =================================================================

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Logout butuh login dulu, jadi taruh di sini
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Ini endpoint "/api/me" yang dipanggil frontend
    Route::get('/me', [AuthController::class, 'me']);
    
    // Endpoint lainnya
    Route::post('/uploads/image', [UploadController::class, 'uploadImage']);
    Route::post('/analysis', [AnalysisController::class, 'store']);
    Route::get('/history', [AnalysisController::class, 'index']);
    Route::delete('/history/{id}', [AnalysisController::class, 'destroy']);
    Route::get('/recommendation', [AnalysisController::class, 'recommend']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});