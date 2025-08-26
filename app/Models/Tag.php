<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';
    protected $primaryKey = 'tag_id';
    
    public $timestamps = false;

    protected $fillable = [
        'tag_id',
        'tag_name',
        'description',
    ];

    /**
     * 取得標籤下的所有文章
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'tag_id', 'tag_id');
    }
}
