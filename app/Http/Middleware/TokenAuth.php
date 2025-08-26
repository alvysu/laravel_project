<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 步驟 1：檢查是否有有效的 Bearer token
        if (!$request->bearerToken()) {
            return response()->json([
                'success' => false,
                'message' => '請提供有效的認證 token',
                'redirect' => '/login'
            ], 401);
        }

        // 步驟 2：使用 Sanctum 的認證機制（不重複造輪子）
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => '認證 token 無效或已過期',
                'redirect' => '/login'
            ], 401);
        }

        // 步驟 3：取得認證使用者
        $user = Auth::guard('sanctum')->user();

        // 步驟 4：檢查 token 能力（abilities）
        if (!$this->checkTokenAbilities($request, $user)) {
            return response()->json([
                'success' => false,
                'message' => '您的 token 沒有足夠的權限執行此操作',
                'redirect' => '/login'
            ], 403);
        }

        // 步驟 5：使用 setUserResolver注入使用者資訊到請求中，而不是 merge，避免污染輸入資料
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // 步驟 6：將額外的認證資訊放在 attributes 中，避免與輸入資料衝突
        $request->attributes->set('auth.user_id', $user->id);
        $request->attributes->set('auth.username', $user->username);

        // 步驟 7：繼續到下一個 middleware 或控制器
        // 如果沒有下一個 middleware 或控制器，則會執行到最後一個 middleware 或控制器
        return $next($request);
    }

    /**
     * 檢查 token 的能力（abilities）
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return bool
     */
    private function checkTokenAbilities(Request $request, $user): bool
    {
        // 檢查 token 是否過期
        $token = $user->currentAccessToken();
        if ($token->expires_at && $token->expires_at->isPast()) {
            return false;
        }

        // 檢查 token 是否有必要的能力
        $requiredAbilities = $this->getRequiredAbilities($request);
        foreach ($requiredAbilities as $ability) {
            if (!$token->can($ability)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 根據請求路徑和方法決定需要的能力
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    private function getRequiredAbilities(Request $request): array
    {
        $path = $request->path();
        $method = $request->method();

        // 根據不同的操作設定不同的能力要求
        if (str_contains($path, 'posts') && in_array($method, ['PUT', 'DELETE'])) {
            return ['posts:write']; // 需要文章寫入權限
        }

        if (str_contains($path, 'upload')) {
            return ['files:write']; // 需要檔案寫入權限
        }

        if (str_contains($path, 'profile')) {
            return ['profile:manage']; // 需要個人資料管理權限
        }

        // 預設只需要基本讀取權限
        return ['*'];
    }
}
