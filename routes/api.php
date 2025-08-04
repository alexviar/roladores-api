<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PunishmentController;
use App\Http\Controllers\RentalPeriodController;
use App\Http\Controllers\RoladorController;
use App\Http\Controllers\RoladorVisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('login', 'login')->withoutMiddleware('auth:sanctum');
        Route::post('logout', 'logout');
        Route::get('user', function (Request $request) {
            return $request->user();
        });
    });

Route::controller(DashboardController::class)
    ->prefix('dashboard')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('daily-stats', 'dailyStats');
        Route::get('category-distribution', 'categoryDistribution');
        Route::get('activity-log', 'activityLog');
    });

Route::controller(CategoryController::class)
    ->prefix('categories')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::get('{category}', 'show');
        Route::post('', 'store');
        Route::patch('{category}', 'update');
        Route::delete('{category}', 'destroy');
    });

Route::controller(RoladorController::class)
    ->prefix('roladores')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::get('{rolador}', 'show')->withoutMiddleware('auth:sanctum');
        Route::post('', 'store');
        Route::patch('{rolador}', 'update');
        Route::delete('{rolador}', 'destroy');
    });

Route::controller(PunishmentController::class)
    ->prefix('punishments')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::get('{punishment}', 'show');
        Route::post('', 'store');
        Route::patch('{punishment}', 'update');
        Route::delete('{punishment}', 'destroy');
    });

Route::controller(RentalPeriodController::class)
    ->prefix('rental-periods')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::get('{rentalPeriod}', 'show');
        Route::post('', 'store');
        Route::patch('{rentalPeriod}', 'update');
        Route::patch('{rentalPeriod}/paid', 'markAsPaid');
        Route::delete('{rentalPeriod}', 'destroy');
    });

Route::controller(\App\Http\Controllers\CreditController::class)
    ->prefix('credits')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::get('{credit}', 'show');
        Route::post('', 'store');
        Route::patch('{credit}', 'update');
        Route::patch('{credit}/reset', 'resetCredit');
        Route::delete('{credit}', 'destroy');

        Route::controller(\App\Http\Controllers\CreditPaymentController::class)
            ->prefix('{credit}/payments')
            ->middleware('auth:sanctum')
            ->group(function () {
                Route::get('', 'index');
                Route::get('{payment}', 'show');
                Route::post('', 'store');
                Route::patch('{payment}', 'update');
                Route::delete('{payment}', 'destroy');
            });
    });

Route::controller(RoladorVisitController::class)
    ->prefix('visits')
    // ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('', 'index');
        Route::post('', 'store');
    });
