<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Post;

class PostTest extends TestCase
{
    use RefreshDatabase;  // テスト実行後にDBをリセット

    /**
     * テスト: 投稿一覧の取得
     */
    public function test_can_get_all_posts(): void
    {
        // テストデータ作成
        Post::factory()->count(3)->create();

        // APIエンドポイントを呼び出し
        $response = $this->getJson('/api/posts');

        // レスポンスの検証
        $response->assertStatus(200)  // ステータスコード200
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'content',
                             'author',
                             'status',
                             'created_at',
                             'updated_at',
                         ]
                     ]
                 ])
                 ->assertJsonCount(3, 'data');  // 3件のデータがある
    }

    /**
     * テスト: 投稿の詳細取得
     */
    public function test_can_get_single_post(): void
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'author' => 'Taro'
        ]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $post->id,
                         'title' => 'Test Post',
                         'author' => 'Taro'
                     ]
                 ]);
    }

    /**
     * テスト: 存在しない投稿の取得（404エラー）
     */
    public function test_returns_404_when_post_not_found(): void
    {
        $response = $this->getJson('/api/posts/999');

        $response->assertStatus(404);
    }

    /**
     * テスト: 投稿の新規作成
     */
    public function test_can_create_post(): void
    {
        $postData = [
            'title' => 'New Post',
            'content' => 'This is content',
            'author' => 'Taro',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(201)  // 201 Created
                 ->assertJson([
                     'data' => [
                         'title' => 'New Post',
                         'author' => 'Taro'
                     ]
                 ]);

        // データベースに保存されたか確認
        $this->assertDatabaseHas('posts', [
            'title' => 'New Post',
            'author' => 'Taro'
        ]);
    }

    /**
     * テスト: バリデーションエラー（タイトルが空）
     */
    public function test_cannot_create_post_without_title(): void
    {
        $postData = [
            'content' => 'This is content',
            'author' => 'Taro',
            'status' => 'draft'
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(422)  // 422 Unprocessable Entity
                 ->assertJsonValidationErrors(['title']);
    }

    /**
     * テスト: バリデーションエラー（複数項目）
     */
    public function test_cannot_create_post_with_invalid_data(): void
    {
        $postData = [
            // title が空
            'content' => '',  // content も空
            'author' => '',   // author も空
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'content', 'author']);
    }

    /**
     * テスト: 投稿の更新
     */
    public function test_can_update_post(): void
    {
        $post = Post::factory()->create([
            'title' => 'Original Title'
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'published'
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'title' => 'Updated Title',
                         'status' => 'published'
                     ]
                 ]);

        // データベースが更新されたか確認
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'status' => 'published'
        ]);
    }

    /**
     * テスト: 投稿の削除
     */
    public function test_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);

        // データベースから削除されたか確認
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    /**
     * テスト: ステータスフィルター（例：公開済みのみ）
     */
    public function test_can_filter_posts_by_status(): void
    {
        Post::factory()->create(['status' => 'draft']);
        Post::factory()->create(['status' => 'published']);
        Post::factory()->create(['status' => 'published']);

        // 公開済みのみ取得
        $response = $this->getJson('/api/posts?status=published');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }
}