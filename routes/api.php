<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(CategoryController::class)
    ->group(function () {
        Route::get('', 'index');
        Route::get('{category}', 'show');
        Route::post('', 'store');
        Route::patch('{category}', 'update');
        Route::delete('{category}', 'destroy');
    })
    ->prefix('categories')
    ->middleware('auth:sanctum');
