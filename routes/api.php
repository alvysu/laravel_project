<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ==================== 認證路由 ====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==================== 需要認證的路由 ====================
Route::middleware(['token.auth'])->group(function () {
    // 登出
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // 個人資料管理
    Route::post('/profile', [UserController::class, 'profile']);
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/change-password', [UserController::class, 'changePassword']);
    Route::post('/profile/delete-account', [UserController::class, 'deleteAccount']);
    
    // 檔案管理
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/files', [FileController::class, 'listFiles']);
    Route::get('/download/{fileId}', [FileController::class, 'download']);
    Route::post('/download-file/{fileId}', [FileController::class, 'downloadFile']);
    
    // 文章管理
    Route::post('/posts', [PostController::class, 'create']);
});

// ==================== 公開路由（不需要認證） ====================
Route::get('/posts', [PostController::class, 'list']);
Route::get('/posts/{postId}', [PostController::class, 'show']);

// ==================== 需要資源擁有者權限的路由 ====================
Route::middleware(['token.auth', 'resource.owner'])->group(function () {
    Route::put('/posts/{postId}', [PostController::class, 'update']);
    Route::delete('/posts/{postId}', [PostController::class, 'delete']);
});

// ==================== 未來可能的路由 ====================
// Route::get('/categories', [PostController::class, 'getCategories']);
// Route::get('/tags', [PostController::class, 'getTags']);
