<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 創建測試使用者（如果不存在）
        if (!DB::table('users')->where('id', 'user_770f0005165b3900')->exists()) {
            DB::table('users')->insert([
                'id' => 'user_770f0005165b3900',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 創建測試分類
        DB::table('categories')->insert([
            'category_id' => 1,
            'name' => '技術',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('categories')->insert([
            'category_id' => 2,
            'name' => '生活',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('categories')->insert([
            'category_id' => 3,
            'name' => '其他',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 創建測試標籤
        DB::table('tags')->insert([
            'tag_id' => 1,
            'name' => 'Laravel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tags')->insert([
            'tag_id' => 2,
            'name' => 'PHP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tags')->insert([
            'tag_id' => 3,
            'name' => 'Web開發',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
