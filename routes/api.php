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
    // Account management routes
    Route::resource('accounts', App\Http\Controllers\Api\V1\CompteController::class)->parameters(['accounts' => 'numero']);
    Route::post('accounts/{numero}/bloquer', [App\Http\Controllers\Api\V1\CompteController::class, 'bloquer']);
    Route::post('accounts/{numero}/debloquer', [App\Http\Controllers\Api\V1\CompteController::class, 'debloquer']);
});
