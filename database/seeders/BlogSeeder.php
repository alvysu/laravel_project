<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 插入分類
        $categories = [
            ['name' => '技術分享'],
            ['name' => '生活隨筆'],
            ['name' => '學習心得'],
            ['name' => '專案介紹'],
            ['name' => '工具推薦'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert($category);
        }

        // 插入標籤
        $tags = [
            ['name' => 'Laravel'],
            ['name' => 'PHP'],
            ['name' => 'JavaScript'],
            ['name' => 'Vue.js'],
            ['name' => 'CSS'],
            ['name' => 'HTML'],
            ['name' => 'MySQL'],
            ['name' => 'Docker'],
            ['name' => 'Git'],
            ['name' => 'API'],
        ];

        foreach ($tags as $tag) {
            DB::table('tags')->insert($tag);
        }

        // 插入測試文章（需要先有使用者）
        $users = DB::table('users')->get();
        if ($users->count() > 0) {
            $userId = $users->first()->id;
            
            $posts = [
                [
                    'user_id' => $userId,
                    'title' => '歡迎來到我的部落格',
                    'content' => '這是我部落格的第一篇文章！在這裡我將分享我的技術學習心得、專案經驗以及生活中的點點滴滴。

希望透過這個部落格能夠：
- 記錄學習過程
- 分享技術知識
- 與大家交流討論
- 提升寫作能力

讓我們一起學習成長吧！',
                    'created_time' => now(),
                    'category_id' => 1,
                    'tag_id' => 1,
                    'updated_time' => now(),
                ],
                [
                    'user_id' => $userId,
                    'title' => 'Laravel 專案開發心得',
                    'content' => '最近完成了一個使用 Laravel 框架的專案，想跟大家分享一些開發過程中的心得和遇到的問題。

## 專案概述
這是一個部落格系統，包含使用者管理、文章管理、檔案上傳等功能。

## 技術架構
- 後端：Laravel 10
- 資料庫：MySQL 8.0
- 前端：Bootstrap 5 + jQuery
- 檔案儲存：本地儲存

## 開發心得
1. Laravel 的 Eloquent ORM 真的很強大
2. 路由系統設計得很直觀
3. 中間件機制讓權限控制變得簡單
4. 資料庫遷移功能很實用

## 遇到的問題
- 檔案上傳的驗證和安全性
- 資料庫關聯的設計
- 前端與後端的資料交換

總的來說，Laravel 是一個非常優秀的 PHP 框架，值得深入學習！',
                    'created_time' => now()->subDays(1),
                    'category_id' => 1,
                    'tag_id' => 2,
                    'updated_time' => now()->subDays(1),
                ],
                [
                    'user_id' => $userId,
                    'title' => 'Docker 容器化部署經驗',
                    'content' => '在部署這個部落格系統時，我選擇使用 Docker 來進行容器化部署。以下是我的經驗分享：

## 為什麼選擇 Docker？
1. 環境一致性
2. 快速部署
3. 易於擴展
4. 版本控制

## Docker Compose 配置
我使用了 Docker Compose 來管理多個服務：
- PHP-FPM 8.3
- MySQL 5.7
- Nginx
- Redis
- Memcached

## 部署流程
1. 準備 Dockerfile
2. 配置 docker-compose.yml
3. 設定環境變數
4. 執行部署命令

## 注意事項
- 資料庫資料的持久化
- 環境變數的安全性
- 網路配置
- 效能優化

Docker 讓部署變得更加簡單和可靠！',
                    'created_time' => now()->subDays(2),
                    'category_id' => 4,
                    'tag_id' => 8,
                    'updated_time' => now()->subDays(2),
                ],
            ];

            foreach ($posts as $post) {
                DB::table('posts')->insert($post);
            }
        }
    }
}
