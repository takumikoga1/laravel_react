import { useState, useEffect } from 'react'
import './App.css'
import api from './services/api'

// ▼ TypeScriptの型定義：APIから返ってくるデータの形を定義
interface TestData {
  message: string;      // 文字列
  timestamp: string;    // 文字列
}

interface UserData {
  id: string;          // 文字列
  name: string;        // 文字列
  email: string;       // 文字列
}

function App() {
  // ▼ 状態管理：データを保存する箱を3つ用意
  const [testData, setTestData] = useState<TestData | null>(null);     // テストAPIの結果
  const [userData, setUserData] = useState<UserData | null>(null);     // ユーザーAPIの結果
  const [loading, setLoading] = useState<boolean>(true);               // 読み込み中かどうか
  const [error, setError] = useState<string | null>(null);             // エラーメッセージ

  // ▼ useEffect：画面が表示されたときに1回だけ実行される
  useEffect(() => {
    const fetchData = async () => {
      try {
        // ▼ Laravel の /api/test を呼び出す
        const testResponse = await api.get<TestData>('/test');
        setTestData(testResponse.data);  // 結果を保存

        // ▼ Laravel の /api/user/123 を呼び出す
        const userResponse = await api.get<UserData>('/user/123');
        setUserData(userResponse.data);  // 結果を保存

        setLoading(false);  // 読み込み完了
      } catch (err) {
        setError((err as Error).message);  // エラーを保存
        setLoading(false);
      }
    };

    fetchData();  // 上の関数を実行
  }, []);  // [] = 画面表示時に1回だけ実行

  // ▼ 読み込み中は「Loading...」を表示
  if (loading) return <div>Loading...</div>;
  
  // ▼ エラーがあれば「Error: ...」を表示
  if (error) return <div>Error: {error}</div>;

  // ▼ データ取得成功時の画面
  return (
    <div className="App">
      <h1>Laravel + React (TypeScript) API Test</h1>
      
      <div style={{ textAlign: 'left', margin: '20px' }}>
        {/* ▼ /api/test の結果を表示 */}
        <h2>Test API Response:</h2>
        <pre>{JSON.stringify(testData, null, 2)}</pre>
        
        {/* ▼ /api/user/123 の結果を表示 */}
        <h2>User API Response:</h2>
        <pre>{JSON.stringify(userData, null, 2)}</pre>
      </div>
    </div>
  )
}

export default App