<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';
    protected $primaryKey = 'posts_id';
    
    // 停用自動時間戳記，因為我們有自己的時間欄位
    public $timestamps = false;

    protected $fillable = [
        'posts_id',
        'user_id',
        'title',
        'content',
        'created_time',
        'category_id',
        'tag_id',
        'updated_time',
    ];

    protected $casts = [
        'created_time' => 'datetime',
        'updated_time' => 'datetime',
    ];

    /**
     * 取得文章所屬的使用者
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 取得文章的分類
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    /**
     * 取得文章的標籤
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'tag_id');
    }

    /**
     * 取得文章的所有檔案
     */
    public function files()
    {
        return $this->hasMany(File::class, 'post_id', 'posts_id');
    }
}
