<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], 'reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user', [AuthController::class, 'getUser']);
        Route::match(['get', 'post'], 'logout', [AuthController::class, 'logout']);
    });
});

Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function () {
    Route::get('get-users', [UserController::class, 'getUsers']);
    Route::post('create-user', [UserController::class, 'createUser']);
    Route::post('update-user', [UserController::class, 'updateUser']);
    Route::delete('delete-user', [UserController::class, 'deleteUser']);
});

Route::group(['prefix' => 'group', 'middleware' => 'auth:api'], function () {
    Route::post('create-group', [GroupController::class, 'createGroup']);
    Route::post('update-group', [GroupController::class, 'updateGroup']);
    Route::delete('delete-group', [GroupController::class, 'deleteGroup']);
});

