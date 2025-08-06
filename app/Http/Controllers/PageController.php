<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * 顯示註冊頁面
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * 顯示登入頁面
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * 顯示上傳頁面
     */
    public function showUpload()
    {
        return view('upload');
    }

    /**
     * 顯示檔案清單頁面
     */
    public function showFiles()
    {
        return view('files');
    }
} 