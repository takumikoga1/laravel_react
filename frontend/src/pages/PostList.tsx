import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { postService } from '../services/postService';
import type { Post } from '../types/post';

function PostList() {
  const [posts, setPosts] = useState<Post[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
    try {
      setLoading(true);
      const data = await postService.getAll();
      setPosts(data);
    } catch (err) {
      setError('投稿の取得に失敗しました');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm('本当に削除しますか？')) return;

    try {
      await postService.delete(id);
      setPosts(posts.filter(post => post.id !== id));
      alert('削除しました');
    } catch (err) {
      alert('削除に失敗しました');
      console.error(err);
    }
  };

  if (loading) return <div style={{ padding: '20px' }}>読み込み中...</div>;
  if (error) return <div style={{ padding: '20px', color: 'red' }}>{error}</div>;

  return (
    <div style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
        <h1>投稿一覧</h1>
        <Link to="/posts/create">
          <button style={{ padding: '10px 20px', backgroundColor: '#4CAF50', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
            新規作成
          </button>
        </Link>
      </div>

      {posts.length === 0 ? (
        <p>投稿がありません</p>
      ) : (
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
          <thead>
            <tr style={{ backgroundColor: '#f2f2f2' }}>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>ID</th>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>タイトル</th>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>著者</th>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>ステータス</th>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>作成日</th>
              <th style={{ border: '1px solid #ddd', padding: '12px', textAlign: 'left' }}>操作</th>
            </tr>
          </thead>
          <tbody>
            {posts.map(post => (
              <tr key={post.id}>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>{post.id}</td>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>
                  <Link to={`/posts/${post.id}`} style={{ color: '#1976d2', textDecoration: 'none' }}>
                    {post.title}
                  </Link>
                </td>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>{post.author}</td>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>
                  <span style={{
                    padding: '4px 8px',
                    borderRadius: '4px',
                    backgroundColor: post.status === 'published' ? '#4CAF50' : '#FFC107',
                    color: 'white',
                    fontSize: '12px'
                  }}>
                    {post.status === 'published' ? '公開' : '下書き'}
                  </span>
                </td>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>
                  {new Date(post.created_at).toLocaleDateString('ja-JP')}
                </td>
                <td style={{ border: '1px solid #ddd', padding: '12px' }}>
                  <Link to={`/posts/${post.id}/edit`}>
                    <button style={{ marginRight: '8px', padding: '6px 12px', backgroundColor: '#2196F3', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}>
                      編集
                    </button>
                  </Link>
                  <button
                    onClick={() => handleDelete(post.id)}
                    style={{ padding: '6px 12px', backgroundColor: '#f44336', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                  >
                    削除
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}

export default PostList;