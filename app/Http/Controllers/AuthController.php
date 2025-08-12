<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            // 使用 PDO 操作資料庫
            $pdo = DB::connection()->getPdo();
            
            // 產生不重複的 user ID
            $id = 'user_' . bin2hex(random_bytes(8));
            $username = trim($request->username);
            $email = trim($request->email);
            $hashedPassword = Hash::make($request->password);
            
            // 使用 PDO 插入資料
            $stmt = $pdo->prepare("INSERT INTO users (id, username, email, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $username, $email, $hashedPassword]);

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
            // 使用 PDO 查詢使用者
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$request->username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '帳號不存在'
                ], 404);
            }

            // 驗證密碼
            if (Hash::check($request->password, $user['password'])) {
                // 登入成功，建立 session
                Auth::login(User::find($user['id']));
                
                return response()->json([
                    'success' => true,
                    'message' => '登入成功',
                    'user_id' => $user['id']
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
    public function logout(): JsonResponse
    {
        try {
            Auth::logout();
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