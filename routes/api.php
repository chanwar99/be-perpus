<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowController;

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
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');

    Route::apiResource('role', RoleController::class)->middleware(['auth:api', 'isOwner']);

    Route::post('profile', [ProfileController::class, 'updateOrCreateProfile'])->middleware('auth:api');

    Route::apiResource('category', CategoryController::class);
    Route::apiResource('book', BookController::class);

    Route::post('borrow', [BorrowController::class, 'updateOrCreateBorrow'])->middleware('auth:api');
    Route::get('borrow', [BorrowController::class, 'index'])->middleware(['auth:api', 'isOwner']);
});
