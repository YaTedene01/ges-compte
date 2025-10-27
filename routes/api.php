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
    // Passport routes
    Route::post('oauth/token', [Laravel\Passport\Http\Controllers\AccessTokenController::class, 'issueToken']);
    Route::post('oauth/token/refresh', [Laravel\Passport\Http\Controllers\TransientTokenController::class, 'refresh']);
    Route::post('oauth/authorize', [Laravel\Passport\Http\Controllers\AuthorizationController::class, 'authorize'])->middleware('auth:api');
    Route::delete('oauth/token', [Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController::class, 'destroy'])->middleware('auth:api');

    Route::post('auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::post('auth/refresh', [App\Http\Controllers\Api\V1\AuthController::class, 'refresh']);
    Route::post('auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout'])->middleware('auth:api');

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::prefix('faye-yatedene')->group(function () {
            Route::resource('comptes', App\Http\Controllers\Api\V1\CompteController::class)->parameters(['comptes' => 'numero']);
            Route::post('comptes/{numero}/bloquer', [App\Http\Controllers\Api\V1\CompteController::class, 'bloquer']);
            Route::post('comptes/{numero}/debloquer', [App\Http\Controllers\Api\V1\CompteController::class, 'debloquer']);
            Route::get('comptes/{numero}', [App\Http\Controllers\Api\V1\CompteController::class, 'show']);
            Route::patch('comptes/{numero}', [App\Http\Controllers\Api\V1\CompteController::class, 'update']);
        });
    });
});
