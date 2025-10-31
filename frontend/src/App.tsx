import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import PostList from './pages/PostList';
import PostDetail from './pages/PostDetail';
import PostCreate from './pages/PostCreate';
import PostEdit from './pages/PostEdit';
import './App.css';

function App() {
  return (
    <BrowserRouter>
      <div style={{ width: '100%', minHeight: '100vh' }}>
        <nav style={{ 
          padding: '20px', 
          backgroundColor: '#333', 
          color: 'white',
          marginBottom: '20px',
          width: '100%',
          boxSizing: 'border-box'
        }}>
          <h2 style={{ margin: 0 }}>Laravel + React Blog System</h2>
        </nav>

        <Routes>
          <Route path="/" element={<Navigate to="/posts" replace />} />
          <Route path="/posts" element={<PostList />} />
          <Route path="/posts/create" element={<PostCreate />} />
          <Route path="/posts/:id" element={<PostDetail />} />
          <Route path="/posts/:id/edit" element={<PostEdit />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}

export default App;