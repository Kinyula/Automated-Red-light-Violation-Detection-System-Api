<?php

use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ViolationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [UserController::class, 'store']);

Route::middleware('auth:sanctum')->group(
    function () {

        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('api.logout');


        Route::prefix('violations')->group(function () {
            Route::get('/', [ViolationController::class, 'index']);
            Route::post('/', [ViolationController::class, 'store']);
            Route::get('/{id}', [ViolationController::class, 'show']);
            Route::put('/{id}', [ViolationController::class, 'update']);
            Route::delete('/{id}', [ViolationController::class, 'destroy']);
        });


        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index']);
            Route::post('/', [QuestionController::class, 'store']);
            Route::get('/{id}', [QuestionController::class, 'show']);
            Route::put('/{id}', [QuestionController::class, 'update']);
            Route::delete('/{id}', [QuestionController::class, 'destroy']);
        });

        Route::prefix('replies')->group(function () {
            Route::get('/', [ReplyController::class, 'index']);
            Route::post('/', [ReplyController::class, 'store']);
            Route::get('/{id}', [ReplyController::class, 'show']);
            Route::put('/{id}', [ReplyController::class, 'update']);
            Route::delete('/{id}', [ReplyController::class, 'destroy']);
        });
    }
);
