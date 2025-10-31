<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    // GET /api/posts - 一覧取得
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        return PostResource::collection($posts);
    }

    // POST /api/posts - 新規作成
    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated());
        return new PostResource($post);
    }

    // GET /api/posts/{post} - 詳細取得
    public function show(Post $post)
    {
        return new PostResource($post);
    }

    // PUT /api/posts/{post} - 更新
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
        return new PostResource($post);
    }

    // DELETE /api/posts/{post} - 削除
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }
}