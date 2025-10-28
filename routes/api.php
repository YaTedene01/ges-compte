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
    // Note: authentication removed per request — account endpoints are public
    // Account management routes (public)
    Route::resource('accounts', App\Http\Controllers\Api\V1\CompteController::class)->parameters(['accounts' => 'numero']);
    Route::patch('accounts/{numero}/block', [App\Http\Controllers\Api\V1\CompteController::class, 'bloquer']);
    Route::patch('accounts/{numero}/unblock', [App\Http\Controllers\Api\V1\CompteController::class, 'debloquer']);
});
