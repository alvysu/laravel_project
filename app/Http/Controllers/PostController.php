<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class PostController extends Controller
{
    /**
     * 創建新文章
     */
    public function create(Request $request): JsonResponse
    {
        // 使用 middleware 驗證後的認證使用者
        $userId = $request->user()->id;

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,category_id',
            'tag_id' => 'nullable|integer|exists:tags,tag_id',
        ], [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過 255 字',
            'content.required' => '文章內容為必填',
            'category_id.exists' => '選擇的分類不存在',
            'tag_id.exists' => '選擇的標籤不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $title = trim($request->title);
            $content = trim($request->content);
            $categoryId = $request->input('category_id');
            $tagId = $request->input('tag_id');
            $currentTime = now();

            // 生成一個簡單的 posts_id（使用時間戳的後幾位數字）
            $postId = (int)substr(time(), -6);
            
            // 使用 Eloquent ORM 創建文章
            Post::create([
                'posts_id' => $postId,
                'user_id' => $userId,
                'title' => $title,
                'content' => $content,
                'created_time' => $currentTime,
                'category_id' => $categoryId,
                'tag_id' => $tagId,
                'updated_time' => $currentTime
            ]);

            return response()->json([
                'success' => true,
                'message' => '文章創建成功',
                'post_id' => $postId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '創建文章失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得文章列表
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $categoryId = $request->input('category_id');
            $tagId = $request->input('tag_id');
            $search = $request->input('search');
            
            // 使用 Eloquent ORM 查詢文章
            $query = Post::with('user')
                ->select([
                    'posts_id',
                    'user_id',
                    'title',
                    'content',
                    'created_time',
                    'updated_time',
                    'category_id',
                    'tag_id'
                ]);
            
            // 添加查詢條件
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            if ($tagId) {
                $query->where('tag_id', $tagId);
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }
            
            // 分頁查詢
            $posts = $query->orderBy('created_time', 'desc')
                          ->paginate($perPage, ['*'], 'page', $page);
            
            // 格式化回應資料
            $formattedPosts = $posts->getCollection()->map(function($post) {
                return [
                    'posts_id' => $post->posts_id,
                    'user_id' => $post->user_id,
                    'title' => $post->title,
                    'content' => $post->content,
                    'created_time' => $post->created_time,
                    'updated_time' => $post->updated_time,
                    'category_id' => $post->category_id,
                    'tag_id' => $post->tag_id,
                    'author_name' => $post->user ? $post->user->username : null
                ];
            });
            
            return response()->json([
                'success' => true,
                'posts' => $formattedPosts,
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'last_page' => $posts->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得文章列表失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得單篇文章詳情
     */
    public function show($postId): JsonResponse
    {
        try {
            // 使用 Eloquent ORM 查詢文章
            $post = Post::with(['user', 'category', 'tag'])
                ->where('posts_id', $postId)
                ->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            // 格式化回應資料
            $postData = [
                'posts_id' => $post->posts_id,
                'user_id' => $post->user_id,
                'title' => $post->title,
                'content' => $post->content,
                'created_time' => $post->created_time,
                'updated_time' => $post->updated_time,
                'category_id' => $post->category_id,
                'tag_id' => $post->tag_id,
                'author_name' => $post->user ? $post->user->username : null,
                'category_name' => $post->category ? $post->category->category_name : null,
                'tag_name' => $post->tag ? $post->tag->tag_name : null
            ];
            
            return response()->json([
                'success' => true,
                'post' => $postData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得文章詳情失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新文章
     */
    public function update(Request $request, $postId): JsonResponse
    {
        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,category_id',
            'tag_id' => 'nullable|integer|exists:tags,tag_id',
        ], [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過 255 字',
            'content.required' => '文章內容為必填',
            'category_id.exists' => '選擇的分類不存在',
            'tag_id.exists' => '選擇的標籤不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // 使用 Eloquent ORM 查詢文章
            $post = Post::where('posts_id', $postId)->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            // 使用 middleware 驗證後的認證使用者
            $authenticatedUserId = $request->user()->id;
            if ((string)$post->user_id !== (string)$authenticatedUserId) {
                return response()->json([
                    'success' => false,
                    'message' => '您沒有權限編輯此文章'
                ], 403);
            }
            
            $title = trim($request->title);
            $content = trim($request->content);
            $categoryId = $request->category_id;
            $tagId = $request->tag_id;
            $currentTime = now();

            // 使用 Eloquent ORM 更新文章
            $post->update([
                'title' => $title,
                'content' => $content,
                'category_id' => $categoryId,
                'tag_id' => $tagId,
                'updated_time' => $currentTime
            ]);

            return response()->json([
                'success' => true,
                'message' => '文章更新成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新文章失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 刪除文章
     */
    public function delete(Request $request, $postId): JsonResponse
    {
        try {
            // 使用 Eloquent ORM 查詢文章
            $post = Post::where('posts_id', $postId)->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            // 使用 middleware 驗證後的認證使用者
            $authenticatedUserId = $request->user()->id;
            if ((string)$post->user_id !== (string)$authenticatedUserId) {
                return response()->json([
                    'success' => false,
                    'message' => '您沒有權限刪除此文章'
                ], 403);
            }
            
            // 使用 Eloquent ORM 刪除文章
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => '文章刪除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '刪除文章失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得分類列表
     */
    public function getCategories(): JsonResponse
    {
        try {
            // 使用 Eloquent ORM 查詢分類
            $categories = Category::select('category_id', 'category_name')
                ->orderBy('category_name')
                ->get();
            
            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得分類列表失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得標籤列表
     */
    public function getTags(): JsonResponse
    {
        try {
            // 使用 Eloquent ORM 查詢標籤
            $tags = Tag::select('tag_id', 'tag_name')
                ->orderBy('tag_name')
                ->get();
            
            return response()->json([
                'success' => true,
                'tags' => $tags
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '取得標籤列表失敗：' . $e->getMessage()
            ], 500);
        }
    }
}
