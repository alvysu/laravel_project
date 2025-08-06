<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * 處理使用者註冊
     */
    public function register(Request $request): JsonResponse
    {
        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'email' => 'required|email|unique:users,email',
        ], [
            'username.required' => '使用者名稱為必填',
            'username.unique' => '使用者名稱已存在',
            'email.required' => '信箱為必填',
            'email.email' => '信箱格式錯誤',
            'email.unique' => '信箱已存在',
            'password.required' => '密碼為必填',
            'password.min' => '密碼至少 6 字',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // 產生不重複的 user ID
            $id = 'user_' . bin2hex(random_bytes(8));
            
            // 建立新使用者
            $user = User::create([
                'id' => $id,
                'username' => trim($request->username),
                'password' => Hash::make($request->password),
                'email' => trim($request->email),
            ]);

            return response()->json([
                'success' => true,
                'message' => '註冊成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '註冊失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 處理使用者登入
     */
    public function login(Request $request): JsonResponse
    {
        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => '使用者名稱為必填',
            'password.required' => '密碼為必填',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // 查詢使用者
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '帳號不存在'
                ], 404);
            }

            // 驗證密碼
            if (Hash::check($request->password, $user->password)) {
                // 登入成功，建立 session
                Auth::login($user);
                
                return response()->json([
                    'success' => true,
                    'message' => '登入成功',
                    'user_id' => $user->id
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '密碼錯誤'
                ], 401);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '登入失敗：' . $e->getMessage()
            ], 500);
        }
    }
} 