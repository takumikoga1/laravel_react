<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト: ユーザー登録が成功する
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                     'token',
                 ])
                 ->assertJson([
                     'user' => [
                         'name' => 'Test User',
                         'email' => 'test@example.com',
                     ]
                 ]);

        // データベースに保存されたか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        // パスワードがハッシュ化されているか確認
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * テスト: 登録時のバリデーションエラー（メールアドレスが空）
     */
    public function test_register_requires_email(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * テスト: 登録時のバリデーションエラー（パスワードが短い）
     */
    public function test_register_requires_password_min_8_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * テスト: 登録時のバリデーションエラー（パスワード確認が一致しない）
     */
    public function test_register_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * テスト: 登録時のバリデーションエラー（メールアドレス重複）
     */
    public function test_register_requires_unique_email(): void
    {
        // 既存ユーザーを作成
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        // 同じメールアドレスで登録を試みる
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * テスト: ログインが成功する
     */
    public function test_user_can_login(): void
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // ログイン
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token',
                 ])
                 ->assertJson([
                     'user' => [
                         'email' => 'test@example.com',
                     ]
                 ]);
    }

    /**
     * テスト: ログイン失敗（存在しないメールアドレス）
     */
    public function test_login_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * テスト: ログイン失敗（パスワードが間違っている）
     */
    public function test_login_fails_with_wrong_password(): void
    {
        // ユーザーを作成
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 間違ったパスワードでログイン
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * テスト: 認証済みユーザーが自分の情報を取得できる
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        // ユーザーを作成
        $user = User::factory()->create();

        // 認証付きでリクエスト
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                 ]);
    }

    /**
     * テスト: 未認証ユーザーはプロフィールを取得できない
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /**
     * テスト: 認証済みユーザーがログアウトできる
     */
    public function test_authenticated_user_can_logout(): void
    {
        // ユーザーを作成してトークンを発行
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // トークンを使ってログアウト
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'ログアウトしました',
                 ]);

        // トークンが削除されたか確認
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * テスト: 認証済みユーザーが投稿を作成できる
     */
    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/posts', [
                             'title' => 'Test Post',
                             'content' => 'Test Content',
                             'author' => $user->name,
                             'status' => 'published',
                         ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);
    }

    /**
     * テスト: 未認証ユーザーは投稿を作成できない
     */
    public function test_unauthenticated_user_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Test Content',
            'author' => 'Anonymous',
            'status' => 'published',
        ]);

        $response->assertStatus(401);
    }

    /**
     * テスト: トークンが発行される
     */
    public function test_token_is_generated_on_registration(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        // トークンが文字列であることを確認
        $this->assertIsString($response->json('token'));

        // トークンの形式を確認（ID|ランダム文字列）
        $token = $response->json('token');
        $this->assertMatchesRegularExpression('/^\d+\|[a-zA-Z0-9]+$/', $token);

        // personal_access_tokens テーブルに保存されているか確認
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}