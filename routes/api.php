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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 認證路由
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// 個人資料管理路由
Route::post('/profile', [UserController::class, 'profile']);
Route::post('/profile/update', [UserController::class, 'updateProfile']);
Route::post('/profile/change-password', [UserController::class, 'changePassword']);
Route::post('/profile/delete-account', [UserController::class, 'deleteAccount']);

// 檔案管理路由
Route::post('/upload', [FileController::class, 'upload']);
Route::get('/files', [FileController::class, 'listFiles']);
Route::get('/download/{fileId}', [FileController::class, 'download']);
Route::post('/download-file/{fileId}', [FileController::class, 'downloadFile']);

// 文章管理 API 路由
Route::post('/posts', [PostController::class, 'create']);
Route::get('/posts', [PostController::class, 'list']);
Route::get('/posts/{postId}', [PostController::class, 'show']);
Route::put('/posts/{postId}', [PostController::class, 'update']);
Route::delete('/posts/{postId}', [PostController::class, 'delete']);
// Route::get('/categories', [PostController::class, 'getCategories']);
// Route::get('/tags', [PostController::class, 'getTags']);
