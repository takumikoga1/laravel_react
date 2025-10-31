<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

// テスト用エンドポイント（既存）
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

Route::get('/user/{userid}', function ($userid) {
    return response()->json([
        'id' => $userid,
        'name' => 'Taro Yamada',
        'email' => 'taro@example.com'
    ]);
});

// ★ 投稿のCRUD APIを追加
Route::apiResource('posts', PostController::class);