<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Swagger documentation routes
Route::get('/docs', function () {
    $documentation = 'default';
    $urlToDocs = url('/api/documentation');
    $configUrl = config('l5-swagger.defaults.additional_config_url');
    $validatorUrl = config('l5-swagger.defaults.validator_url');
    $operationsSorter = config('l5-swagger.defaults.operations_sort');
    $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', false);

    return view('vendor.l5-swagger.index', compact(
        'documentation',
        'urlToDocs',
        'configUrl',
        'validatorUrl',
        'operationsSorter',
        'useAbsolutePath'
    ));
});

// Add missing Swagger routes
Route::get('/docs/{file}', function ($file) {
    $path = storage_path('api-docs/' . $file);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
})->where('file', '.*');

// Add the missing l5-swagger route
Route::get('/docs', function () {
    $documentation = 'default';
    $urlToDocs = url('/api/documentation');
    $configUrl = config('l5-swagger.defaults.additional_config_url');
    $validatorUrl = config('l5-swagger.defaults.validator_url');
    $operationsSorter = config('l5-swagger.defaults.operations_sort');
    $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', false);

    return view('vendor.l5-swagger.index', compact(
        'documentation',
        'urlToDocs',
        'configUrl',
        'validatorUrl',
        'operationsSorter',
        'useAbsolutePath'
    ));
})->name('l5-swagger.default.docs');
