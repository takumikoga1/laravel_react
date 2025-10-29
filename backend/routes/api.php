<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// テスト用
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// ユーザー情報取得
Route::get('/user/{userid}', function ($userid) {
    return response()->json([
        'id' => $userid,
        'name' => 'Taro Yamada',
        'email' => 'taro@example.com'
    ]);
});

// 投稿一覧（追加）
Route::get('/posts', function () {
    return response()->json([
        'posts' => [
            ['id' => 1, 'title' => 'First Post', 'body' => 'Content 1'],
            ['id' => 2, 'title' => 'Second Post', 'body' => 'Content 2'],
            ['id' => 3, 'title' => 'Third Post', 'body' => 'Content 3'],
        ]
    ]);
});

// POSTリクエストのテスト（追加）
Route::post('/posts', function (Request $request) {
    return response()->json([
        'message' => 'Post created successfully',
        'data' => $request->all()
    ], 201);
});