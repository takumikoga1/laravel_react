# Laravel + React (TypeScript) API開発環境

Laravel（バックエンドAPI）と React + TypeScript（フロントエンド）を分離したCSR構成のプロジェクトです。

## プロジェクト構成

```
laravel_study/
├── backend/              # Laravel API (ポート8000)
│   ├── app/
│   ├── routes/
│   │   └── api.php      # APIエンドポイント定義
│   ├── config/
│   │   └── cors.php     # CORS設定
│   └── ...
│
└── frontend/            # React + TypeScript (ポート5173)
    ├── src/
    │   ├── services/
    │   │   └── api.ts   # API通信設定
    │   ├── App.tsx      # メインコンポーネント
    │   └── main.tsx
    └── package.json
```

---

## 必要な環境

- **PHP**: 8.1以上
- **Composer**: 最新版
- **Node.js**: 18以上
- **npm**: 9以上

### インストール確認

```bash
php --version
composer --version
node --version
npm --version
```

---

## 環境構築手順

### 1. 必要なツールのインストール（macOS）

```bash
# Homebrewのインストール（未インストールの場合）
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# PHP、Composer、Node.jsのインストール
brew install php composer node
```

---

### 2. Laravel（バックエンド）のセットアップ

```bash
# プロジェクトディレクトリに移動
cd ~/Desktop/laravel_study

# Laravelプロジェクトの作成
composer create-project laravel/laravel backend

# バックエンドディレクトリに移動
cd backend
```

#### CORS設定ファイルの作成

```bash
# config/cors.php を作成
touch config/cors.php
```

`config/cors.php` に以下を記述：

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'],  // Reactのオリジン
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

#### APIルートファイルの作成

```bash
# routes/api.php を作成（存在しない場合）
touch routes/api.php
```

`routes/api.php` に以下を記述：

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// テスト用エンドポイント
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// ユーザー情報取得API
Route::get('/user/{userid}', function ($userid) {
    return response()->json([
        'id' => $userid,
        'name' => 'Taro Yamada',
        'email' => 'taro@example.com'
    ]);
});
```

#### bootstrap/app.php の確認

`bootstrap/app.php` で以下の設定があることを確認：

```php
->withRouting(
    api: __DIR__.'/../routes/api.php',  // この行があることを確認
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
->withMiddleware(function (Middleware $middleware) {
    // CORSミドルウェアの有効化
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

#### キャッシュのクリア

```bash
php artisan config:clear
php artisan route:list  # ルート確認
```

---

### 3. React + TypeScript（フロントエンド）のセットアップ

```bash
# プロジェクトルートに戻る
cd ~/Desktop/laravel_study

# Vite + React + TypeScriptプロジェクトの作成
npm create vite@latest frontend -- --template react-ts

# フロントエンドディレクトリに移動
cd frontend

# 依存パッケージのインストール
npm install

# axios と react-router-dom をインストール
npm install axios react-router-dom
```

#### API通信設定ファイルの作成

```bash
# servicesディレクトリを作成
mkdir src/services

# api.tsファイルを作成
touch src/services/api.ts
```

`src/services/api.ts` に以下を記述：

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;
```

#### App.tsxの編集

`src/App.tsx` を以下に書き換え：

```typescript
import { useState, useEffect } from 'react'
import './App.css'
import api from './services/api'

interface TestData {
  message: string;
  timestamp: string;
}

interface UserData {
  id: string;
  name: string;
  email: string;
}

function App() {
  const [testData, setTestData] = useState<TestData | null>(null);
  const [userData, setUserData] = useState<UserData | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const testResponse = await api.get<TestData>('/test');
        setTestData(testResponse.data);

        const userResponse = await api.get<UserData>('/user/123');
        setUserData(userResponse.data);

        setLoading(false);
      } catch (err) {
        setError((err as Error).message);
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div className="App">
      <h1>Laravel + React (TypeScript) API Test</h1>
      
      <div style={{ textAlign: 'left', margin: '20px' }}>
        <h2>Test API Response:</h2>
        <pre>{JSON.stringify(testData, null, 2)}</pre>
        
        <h2>User API Response:</h2>
        <pre>{JSON.stringify(userData, null, 2)}</pre>
      </div>
    </div>
  )
}

export default App
```

---

## 起動方法

### ターミナル1: Laravelサーバー起動

```bash
cd ~/Desktop/laravel_study/backend
php artisan serve
```

→ `http://localhost:8000` で起動

### ターミナル2: Reactサーバー起動

```bash
cd ~/Desktop/laravel_study/frontend
npm run dev
```

→ `http://localhost:5173` で起動

### 動作確認

ブラウザで `http://localhost:5173` にアクセスして、以下が表示されればOK：

- Test API Response（LaravelのAPIから取得）
- User API Response（LaravelのAPIから取得）

---

## よく使うコマンド

### Laravel

```bash
# サーバー起動
php artisan serve

# サーバー停止
Ctrl + C

# ルート一覧表示
php artisan route:list

# キャッシュクリア
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# コントローラー作成
php artisan make:controller Api/UserController --api

# マイグレーション作成
php artisan make:migration create_posts_table

# マイグレーション実行
php artisan migrate
```

### React

```bash
# 開発サーバー起動
npm run dev

# サーバー停止
Ctrl + C

# ビルド（本番用）
npm run build

# 依存関係の追加
npm install [package-name]
```

---

## トラブルシューティング

### CORSエラーが出る場合

```bash
# backend/config/cors.php を確認
# 'allowed_origins' に 'http://localhost:5173' が含まれているか確認

# キャッシュをクリア
cd backend
php artisan config:clear

# 両サーバーを再起動
```

### 404 Not Found エラー

```bash
# Laravel側でルートを確認
cd backend
php artisan route:list --path=api

# api/test と api/user/{userid} が表示されるか確認
```

### npmのインストールが遅い場合

```bash
# yarnを使う
npm install -g yarn
cd frontend
yarn install
yarn add axios react-router-dom
```

---