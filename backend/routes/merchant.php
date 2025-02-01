<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Departments\DepartmentBillingController;
use App\Http\Controllers\Departments\DepartmentController;
use App\Http\Controllers\Merchants\MerchantController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/site-data', [MerchantController::class, 'layout'])->name('merchants.layout');
Route::get('/token/{token}', [AuthController::class, 'token'])->name('auth.token');

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::prefix('merchants')->group(function () {
    Route::middleware(['optional.auth:sanctum'])->group(function () {
        //cart
        Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('merchants.checkout');
        Route::prefix('card-web')->group(function () {
            Route::post('/fees', [CheckoutController::class, 'cardFees'])->name('merchants.card-fees');
            Route::post('/gateway', [CheckoutController::class, 'iFrameSrc'])->name('merchants.card-iframe');
        });
        Route::prefix('check')->group(function () {
            Route::post('/fees', [CheckoutController::class, 'checkFees'])->name('merchants.check-fees');
        });
        //Department billings
        Route::prefix('departments')->group(function () {
            Route::get('/', [DepartmentController::class, 'index'])->name('departments.list');
            Route::get('/{slug}', [DepartmentBillingController::class, 'index'])->name('department.billing.index');
            Route::get('/{department}/hosted', [DepartmentBillingController::class, 'getHosted'])->name('department.billing.hosted');
        });
    });
    //Auth Users Only
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [MerchantController::class, 'get'])->name('merchant.get');
        //Route::put('/{merchant}', [MerchantController::class, 'update'])->name('merchants.update');

        //All users list
        Route::get('/users', [UsersController::class, 'index'])->name('users-list');
        Route::get('/users/{user}', [UserController::class, 'edit'])->name('users-edit');
        Route::prefix('departments')->group(function () {
            //Route::get('/', [DepartmentController::class, 'index'])->name('department.list');
        });
    });
});
