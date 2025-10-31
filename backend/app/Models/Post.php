<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // 一括代入を許可するカラム
    protected $fillable = [
        'title',
        'content',
        'author',
        'status',
    ];

    // 型キャスト
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
