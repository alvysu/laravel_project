<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * 創建新文章
     */
    public function create(Request $request): JsonResponse
    {
        // 檢查登入狀態 - 接受前端傳送的 user_id
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => '請先登入 (缺少 user_id)',
                'redirect' => '/login'
            ], 401);
        }

        // 驗證 user_id 是否存在於資料庫
        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            if (!$stmt->fetch()) {
                return response()->json([
                    'success' => false,
                    'message' => '使用者不存在',
                    'redirect' => '/login'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '驗證使用者失敗：' . $e->getMessage(),
                'redirect' => '/login'
            ], 500);
        }

        // 驗證輸入資料
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,category_id',
            'tag_id' => 'nullable|integer|exists:tags,tag_id',
            'user_id' => 'required|string|exists:users,id',
        ], [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過 255 字',
            'content.required' => '文章內容為必填',
            'category_id.exists' => '選擇的分類不存在',
            'tag_id.exists' => '選擇的標籤不存在',
            'user_id.required' => '使用者 ID 為必填',
            'user_id.exists' => '使用者不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $pdo = DB::connection()->getPdo();
            
            $title = trim($request->title);
            $content = trim($request->content);
            $categoryId = $request->input('category_id');
            $tagId = $request->input('tag_id');
            $currentTime = now();

            // 生成一個簡單的 posts_id（使用時間戳的後幾位數字）
            $postId = (int)substr(time(), -6);
            
            // 插入文章（包含 posts_id）
            $stmt = $pdo->prepare("
                INSERT INTO posts (posts_id, user_id, title, content, created_time, category_id, tag_id, updated_time) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$postId, $userId, $title, $content, $currentTime, $categoryId, $tagId, $currentTime]);

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
            $pdo = DB::connection()->getPdo();
            
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $categoryId = $request->input('category_id');
            $tagId = $request->input('tag_id');
            $search = $request->input('search');
            
            $offset = ($page - 1) * $perPage;
            
            // 構建查詢條件
            $whereConditions = [];
            $params = [];
            
            if ($categoryId) {
                $whereConditions[] = "p.category_id = ?";
                $params[] = $categoryId;
            }
            
            if ($tagId) {
                $whereConditions[] = "p.tag_id = ?";
                $params[] = $tagId;
            }
            
            if ($search) {
                $whereConditions[] = "(p.title LIKE ? OR p.content LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
            
            // 查詢文章總數
            $countSql = "SELECT COUNT(*) FROM posts p {$whereClause}";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // 查詢文章列表
            $sql = "
                SELECT 
                    p.posts_id,
                    p.user_id,
                    p.title,
                    p.content,
                    p.created_time,
                    p.updated_time,
                    p.category_id,
                    p.tag_id,
                    u.username as author_name
                    /* c.name as category_name, */
                    /* t.name as tag_name */
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                /* LEFT JOIN categories c ON p.category_id = c.category_id */
                /* LEFT JOIN tags t ON p.tag_id = t.tag_id */
                {$whereClause}
                ORDER BY p.created_time DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return response()->json([
                'success' => true,
                'posts' => $posts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
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
            $pdo = DB::connection()->getPdo();
            
            $stmt = $pdo->prepare("
                SELECT 
                    p.posts_id,
                    p.user_id,
                    p.title,
                    p.content,
                    p.created_time,
                    p.updated_time,
                    p.category_id,
                    p.tag_id,
                    u.username as author_name,
                    c.name as category_name,
                    t.name as tag_name
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.category_id
                LEFT JOIN tags t ON p.tag_id = t.tag_id
                WHERE p.posts_id = ?
            ");
            $stmt->execute([$postId]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'post' => $post
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
            'category_id' => 'required|integer|exists:categories,category_id',
            'tag_id' => 'nullable|integer|exists:tags,tag_id',
            'user_id' => 'required|string|exists:users,id',
        ], [
            'title.required' => '文章標題為必填',
            'title.max' => '文章標題不能超過 255 字',
            'content.required' => '文章內容為必填',
            'category_id.required' => '分類為必填',
            'category_id.exists' => '選擇的分類不存在',
            'tag_id.exists' => '選擇的標籤不存在',
            'user_id.required' => '使用者 ID 為必填',
            'user_id.exists' => '使用者不存在',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $pdo = DB::connection()->getPdo();
            
            // 檢查文章是否存在且屬於該使用者
            $checkStmt = $pdo->prepare("SELECT user_id FROM posts WHERE posts_id = ?");
            $checkStmt->execute([$postId]);
            $post = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            // 檢查權限 - 使用前端傳送的 user_id
            $requestUserId = $request->input('user_id');
            if ($post['user_id'] !== $requestUserId) {
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

            // 更新文章
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, category_id = ?, tag_id = ?, updated_time = ?
                WHERE posts_id = ?
            ");
            $stmt->execute([$title, $content, $categoryId, $tagId, $currentTime, $postId]);

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
            $pdo = DB::connection()->getPdo();
            
            // 檢查文章是否存在且屬於該使用者
            $checkStmt = $pdo->prepare("SELECT user_id FROM posts WHERE posts_id = ?");
            $checkStmt->execute([$postId]);
            $post = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => '文章不存在'
                ], 404);
            }
            
            // 檢查權限 - 使用前端傳送的 user_id
            $requestUserId = $request->input('user_id');
            if ($post['user_id'] !== $requestUserId) {
                return response()->json([
                    'success' => false,
                    'message' => '您沒有權限刪除此文章'
                ], 403);
            }
            
            // 刪除文章
            $stmt = $pdo->prepare("DELETE FROM posts WHERE posts_id = ?");
            $stmt->execute([$postId]);

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
            $pdo = DB::connection()->getPdo();
            
            $stmt = $pdo->prepare("SELECT category_id, name FROM categories ORDER BY name");
            $stmt->execute();
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
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
            $pdo = DB::connection()->getPdo();
            
            $stmt = $pdo->prepare("SELECT tag_id, name FROM tags ORDER BY name");
            $stmt->execute();
            $tags = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
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
