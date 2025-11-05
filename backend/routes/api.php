<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\AuthController;

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

// 認証不要（公開API）
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// テスト用（既存）
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// 認証が必要なAPI
Route::middleware('auth:sanctum')->group(function () {
    // 認証関連
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // 投稿API（認証必須に変更）
    Route::apiResource('posts', PostController::class);
});
