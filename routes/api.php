<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::get('categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
Route::get('books', [\App\Http\Controllers\Api\BookController::class, 'index']);
Route::get('books/{id}', [\App\Http\Controllers\Api\BookController::class, 'show']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('books/{id}/rate', [\App\Http\Controllers\Api\BookController::class, 'rate']);
    Route::delete('books/{id}/rate', [\App\Http\Controllers\Api\BookController::class, 'unrate']);
});

Route::group(['prefix' => 'transactions', 'middleware' => 'auth:api'], function () {
    Route::get('/', [\App\Http\Controllers\Api\TransactionController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\Api\TransactionController::class, 'show']);
    Route::post('/borrow', [\App\Http\Controllers\Api\TransactionController::class, 'borrow']);
    Route::post('/{id}/return', [\App\Http\Controllers\Api\TransactionController::class, 'returnBook']);
});

Route::group(['prefix' => 'member', 'middleware' => 'auth:api'], function () {
    Route::get('/profile', [\App\Http\Controllers\Api\MemberController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\MemberController::class, 'update']);
});

// Integration Routes (Protected by Secret Key)
Route::middleware([\App\Http\Middleware\CheckIntegrationSecret::class])->prefix('integration')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Api\IntegrationController::class, 'getUsers']);
    Route::put('/users/{nim}', [\App\Http\Controllers\Api\IntegrationController::class, 'updateUser']);
    Route::post('/books', [\App\Http\Controllers\Api\BookController::class, 'storeFromIntegration']);
});
