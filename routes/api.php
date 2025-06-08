<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PunishmentController;
use App\Http\Controllers\RoladorController;
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

Route::controller(RoladorController::class)
    ->group(function () {
        Route::get('', 'index');
        Route::get('{rolador}', 'show');
        Route::post('', 'store');
        Route::patch('{rolador}', 'update');
        Route::delete('{rolador}', 'destroy');
    })
    ->prefix('roladores')
    ->middleware('auth:sanctum');

Route::controller(PunishmentController::class)
    ->group(function () {
        Route::get('', 'index');
        Route::get('{punishment}', 'show');
        Route::post('', 'store');
        Route::patch('{punishment}', 'update');
        Route::delete('{punishment}', 'destroy');
    })
    ->prefix('punishments')
    ->middleware('auth:sanctum');
