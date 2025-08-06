<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';
    protected $primaryKey = 'file_id';
    
    // 停用自動時間戳記
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'file_path',
        'upload_time',
        'post_id',
    ];

    protected $casts = [
        'upload_time' => 'datetime',
    ];

    /**
     * 取得檔案所屬的使用者
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 取得檔案所屬的文章
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'posts_id');
    }
} 