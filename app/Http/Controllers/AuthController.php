<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            // 產生不重複的 user ID（使用整數）
            $id = time() . rand(1000, 9999);
            $username = trim($request->username);
            $email = trim($request->email);
            $hashedPassword = Hash::make($request->password);
            
            // 使用 Eloquent ORM 創建使用者
            User::create([
                'id' => $id,
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword
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
            // 使用 Eloquent ORM 查詢使用者
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '帳號不存在'
                ], 404);
            }

            // 驗證密碼
            if (Hash::check($request->password, $user->password)) {
                // 使用 Sanctum 產生標準的 API token
                $token = $user->createToken('auth-token', ['*'])->plainTextToken;
                
                return response()->json([
                    'success' => true,
                    'message' => '登入成功',
                    'user_id' => $user->id,
                    'access_token' => $token// ← 前端要存起來
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

    /**
     * 處理使用者登出
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // 撤銷當前的 API token
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => '登出成功'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '登出失敗：' . $e->getMessage()
            ], 500);
        }
    }
} 