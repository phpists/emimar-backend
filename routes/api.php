<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileEntryController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], 'reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user', [AuthController::class, 'getUser']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
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
    Route::get('get-groups', [GroupController::class, 'getGroups']);
    Route::post('create-group', [GroupController::class, 'createGroup']);
    Route::post('update-group', [GroupController::class, 'updateGroup']);
    Route::delete('delete-group', [GroupController::class, 'deleteGroup']);
});

Route::group(['prefix' => 'project', 'middleware' => 'auth:api'], function () {
    Route::get('get-projects', [ProjectController::class, 'getProjects']);
    Route::post('create-project', [ProjectController::class, 'createProject']);
    Route::post('update-project', [ProjectController::class, 'updateProject']);
    Route::delete('delete-project', [ProjectController::class, 'deleteProject']);
});

Route::group(['prefix' => 'file-entry', 'middleware' => 'auth:api'], function () {
    Route::get('get-project-tree', [FileEntryController::class, 'getProjectTree']);
    Route::get('get-project-file-entry', [FileEntryController::class, 'getProjectFileEntry']);

    Route::post('create-folder', [FileEntryController::class, 'createFolder']);
    Route::post('update-folder', [FileEntryController::class, 'updateFolder']);
    Route::post('delete-folder', [FileEntryController::class, 'deleteFolder']);
    Route::post('move-folder', [FileEntryController::class, 'moveFolder']);

    Route::post('upload-file', [FileEntryController::class, 'uploadFile']);
    Route::post('move-file', [FileEntryController::class, 'moveFile']);
    Route::post('delete-file', [FileEntryController::class, 'deleteFile']);
});



