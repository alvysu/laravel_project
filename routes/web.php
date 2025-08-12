<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 頁面路由
Route::get('/register', [PageController::class, 'showRegister'])->name('register');
Route::get('/login', [PageController::class, 'showLogin'])->name('login');
Route::get('/upload', [PageController::class, 'showUpload'])->name('upload');
Route::get('/files', [PageController::class, 'showFiles'])->name('files');
Route::get('/profile', [PageController::class, 'showProfile'])->name('profile');

// 簡單的文章路由
Route::get('/posts', function () {
    return view('posts.index');
})->name('posts');

Route::get('/posts/create', function () {
    return view('posts.create');
})->name('posts.create');
