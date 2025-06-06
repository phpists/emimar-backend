<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::group(['middleware' => 'api'],
        function () {
            Route::get('user', [AuthController::class, 'getUser']);
            Route::match(['get', 'post'], 'logout', [AuthController::class, 'logout']);
        }
    );
});

