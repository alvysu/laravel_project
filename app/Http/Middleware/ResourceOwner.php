<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ResourceOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 檢查是否已經通過認證
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => '認證失敗'
            ], 401);
        }

        $authenticatedUserId = $request->user()->id;
        $resourceUserId = null;

        // 根據不同的路由參數來取得資源擁有者 ID
        if ($request->route('postId')) {
            // 文章相關操作
            $postId = $request->route('postId');
            try {
                $pdo = DB::connection()->getPdo();
                $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE posts_id = ?");
                $stmt->execute([$postId]);
                $post = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$post) {
                    return response()->json([
                        'success' => false,
                        'message' => '資源不存在'
                    ], 404);
                }
                
                $resourceUserId = $post['user_id'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '驗證資源權限失敗'
                ], 500);
            }
        } elseif ($request->route('fileId')) {
            // 檔案相關操作
            $fileId = $request->route('fileId');
            try {
                $pdo = DB::connection()->getPdo();
                $stmt = $pdo->prepare("SELECT user_id FROM files WHERE id = ?");
                $stmt->execute([$fileId]);
                $file = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$file) {
                    return response()->json([
                        'success' => false,
                        'message' => '檔案不存在'
                    ], 404);
                }
                
                $resourceUserId = $file['user_id'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '驗證檔案權限失敗'
                ], 500);
            }
        }

        // 檢查是否成功取得資源擁有者 ID
        if ($resourceUserId === null) {
            return response()->json([
                'success' => false,
                'message' => '無法驗證資源權限'
            ], 500);
        }

        // 檢查資源擁有者是否為當前登入使用者
        // 確保類型一致進行比較
        if ((string)$resourceUserId !== (string)$authenticatedUserId) {
            return response()->json([
                'success' => false,
                'message' => '您沒有權限存取此資源'
            ], 403);
        }

        return $next($request);
    }
}
