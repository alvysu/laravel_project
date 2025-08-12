<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * 取得個人資料
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            // 從請求中取得使用者 ID（前端會傳送）
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入'
                ], 401);
            }

            // 使用 PDO 查詢使用者資料
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            //\PDO::FETCH_ASSOC 代表只取欄位名稱當索引，不要取數字索引。
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => '使用者不存在'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $userData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得個人資料失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新個人資料
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入'
                ], 401);
            }

            // 驗證輸入資料
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:255|unique:users,username,' . $userId,
                'email' => 'required|email|unique:users,email,' . $userId,
            ], [
                'username.required' => '使用者名稱為必填',
                'username.unique' => '使用者名稱已存在',
                'email.required' => '信箱為必填',
                'email.email' => '信箱格式錯誤',
                'email.unique' => '信箱已存在',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // 使用 PDO 更新資料
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([
                trim($request->username),
                trim($request->email),
                $userId
            ]);

            return response()->json([
                'success' => true,
                'message' => '個人資料更新成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 修改密碼
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入'
                ], 401);
            }

            // 驗證輸入資料
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|same:new_password',
            ], [
                'current_password.required' => '目前密碼為必填',
                'new_password.required' => '新密碼為必填',
                'new_password.min' => '新密碼至少 6 字',
                'confirm_password.required' => '確認密碼為必填',
                'confirm_password.same' => '確認密碼與新密碼不符',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // 使用 PDO 查詢目前密碼
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            // 驗證目前密碼
            if (!Hash::check($request->current_password, $userData['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => '目前密碼錯誤'
                ], 401);
            }

            // 更新密碼
            $newHashedPassword = Hash::make($request->new_password);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHashedPassword, $userId]);

            return response()->json([
                'success' => true,
                'message' => '密碼修改成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '密碼修改失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 刪除帳號
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => '請先登入'
                ], 401);
            }

            // 驗證密碼
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ], [
                'password.required' => '密碼為必填',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // 使用 PDO 查詢密碼
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            // 驗證密碼
            if (!Hash::check($request->password, $userData['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => '密碼錯誤'
                ], 401);
            }

            // 刪除帳號
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            return response()->json([
                'success' => true,
                'message' => '帳號刪除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '帳號刪除失敗：' . $e->getMessage()
            ], 500);
        }
    }
}
