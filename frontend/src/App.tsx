// src/App.tsx
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import Login from './components/Login'; // 追加
import './App.css';

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="App">
          <h1>Laravel + React 学習プロジェクト</h1>
          
          <Routes>
            <Route path="/" element={<div>ホーム</div>} />
            <Route path="/login" element={<Login />} /> {/* 追加 */}
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;