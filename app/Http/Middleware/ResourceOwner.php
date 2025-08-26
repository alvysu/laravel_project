<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Models\File;

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
        // 步驟 1：檢查是否已經通過認證
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => '認證失敗'
            ], 401);
        }

        $authenticatedUserId = $request->user()->id;
        $resourceUserId = null;

        // 步驟 2：根據不同的路由參數來取得資源擁有者 ID
        if ($request->route('postId')) {
            // 文章相關操作
            $postId = $request->route('postId');
            try {
                // 使用 Eloquent ORM 查詢文章
                $post = Post::where('posts_id', $postId)->first();
                
                if (!$post) {
                    return response()->json([
                        'success' => false,
                        'message' => '資源不存在'
                    ], 404);
                }
                
                $resourceUserId = $post->user_id;
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
                // 使用 Eloquent ORM 查詢檔案
                $file = File::where('id', $fileId)->first();
                
                if (!$file) {
                    return response()->json([
                        'success' => false,
                        'message' => '檔案不存在'
                    ], 404);
                }
                
                $resourceUserId = $file->user_id;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '驗證檔案權限失敗'
                ], 500);
            }
        }

        // 步驟 3：檢查是否成功取得資源擁有者 ID
        if ($resourceUserId === null) {
            return response()->json([
                'success' => false,
                'message' => '無法驗證資源權限'
            ], 500);
        }

        // 步驟 4：檢查資源擁有者是否為當前登入使用者
        // 確保類型一致進行比較
        if ((string)$resourceUserId !== (string)$authenticatedUserId) {
            return response()->json([
                'success' => false,
                'message' => '您沒有權限存取此資源'
            ], 403);
        }

        // 步驟 5：繼續到下一個 middleware 或控制器
        // 如果沒有下一個 middleware 或控制器，則會執行到最後一個 middleware 或控制器
        return $next($request);
    }
}
