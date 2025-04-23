<?php

use App\Http\Controllers\Api\GuestInfoController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\StatisticalDataController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ViolationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ExcelActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [UserController::class, 'store']);
Route::get('/team', [GuestInfoController::class, 'index']);
Route::post('/store/violation', [ViolationController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/user/status/show', [UserController::class, 'statusShow']);
Route::get('statistics/data', [StatisticalDataController::class, 'index']);

Route::get('/users/license_plate/{license_plate}', [UserController::class, 'getUserByPlate']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('api.forgot-password');
Route::middleware('auth:sanctum')->group(
    function () {

        Route::get('/users/search', [UserController::class, 'search']);
        Route::put('/user/status/{id}', [UserController::class, 'status']);

        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/user/{id}', [UserController::class, 'update']);
        Route::put('/users/{id}', [UserController::class, 'generalUpdate']);
        Route::delete('/user/destroy/{id}', [UserController::class, 'destroy']);
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('api.logout');


        Route::prefix('violations')->group(function () {
            Route::get('/', [ViolationController::class, 'index']);
            Route::get('/search', [ViolationController::class, 'search']);
            Route::get('/{id}', [ViolationController::class, 'show']);
            Route::put('/{id}', [ViolationController::class, 'update']);
            Route::delete('/{id}', [ViolationController::class, 'destroy']);
        });


        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index']);
            Route::post('/', [QuestionController::class, 'store']);
            Route::get('/search', [QuestionController::class, 'search'])->name('api.search');
            Route::get('/{id}', [QuestionController::class, 'show']);
            Route::put('/{id}', [QuestionController::class, 'update']);
            Route::delete('/{id}', [QuestionController::class, 'destroy']);
        });

        Route::prefix('replies')->group(function () {
            Route::get('/', [ReplyController::class, 'index']);
            Route::get('/search', [ReplyController::class, 'search'])->name('api.search');
            Route::post('/', [ReplyController::class, 'store']);
            Route::get('/{id}', [ReplyController::class, 'show']);
            Route::put('/{id}', [ReplyController::class, 'update']);
            Route::delete('/{id}', [ReplyController::class, 'destroy']);
        });

        Route::prefix('excel')->group(function () {
            Route::get('/export', [ExcelActivityController::class, 'export']);
            Route::post('/import', [ExcelActivityController::class, 'import']);
        });

        Route::prefix('statistics')->group(function(){
            Route::post('/create', [StatisticalDataController::class, 'store']);
            Route::put('/update/{id}', [StatisticalDataController::class, 'update']);
            Route::delete('/delete/{id}', [StatisticalDataController::class, 'destroy']);
        });
    }
);
