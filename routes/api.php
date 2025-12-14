<?php

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

// ==============================
// PUBLIC
// ==============================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/recommendations', [RecommendationController::class, 'byPalette']);

// ==============================
// PROTECTED
// ==============================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/uploads/image', [UploadController::class, 'uploadImage']);
    Route::post('/analysis', [AnalysisController::class, 'store']);

    Route::get('/history', [AnalysisController::class, 'index']);
    Route::delete('/history/{id}', [AnalysisController::class, 'destroy']);

    Route::get('/recommendation', [AnalysisController::class, 'recommend']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});
