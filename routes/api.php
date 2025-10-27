<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('authentication/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('authentication/refresh', [App\Http\Controllers\Api\V1\AuthController::class, 'refresh']);
    Route::post('authentication/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:api');

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Account management routes
        Route::resource('accounts', App\Http\Controllers\Api\V1\CompteController::class)->parameters(['accounts' => 'numero']);
        Route::patch('accounts/{numero}/block', [App\Http\Controllers\Api\V1\CompteController::class, 'bloquer']);
        Route::patch('accounts/{numero}/unblock', [App\Http\Controllers\Api\V1\CompteController::class, 'debloquer']);
    });
});
