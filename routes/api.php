<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController, DocumentController};
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\UiController;
use App\Http\Controllers\Api\V1\RoleSwitchController;

Route::prefix('v1')->group(function() {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware(['auth:sanctum','tenant'])->group(function() {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/ui/menu', [UiController::class, 'menu']);
        Route::get('/me/roles', [RoleSwitchController::class, 'list']);
        Route::post('/me/roles/switch', [RoleSwitchController::class, 'switch']);

        Route::get('/documents', [DocumentController::class, 'index']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::get('/documents/{id}', [DocumentController::class, 'show']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

        Route::get('/profile/me', [ProfileController::class, 'me']);
        Route::put('/profile', [ProfileController::class, 'updateProfile']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);

     Route::middleware(['auth:sanctum','tenant','role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{id}', [UserController::class, 'detach']); // desasociar del tenant
    });
    });
});
