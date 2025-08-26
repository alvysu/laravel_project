<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'category_name',
        'description',
    ];

    /**
     * 取得分類下的所有文章
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id', 'category_id');
    }
}
