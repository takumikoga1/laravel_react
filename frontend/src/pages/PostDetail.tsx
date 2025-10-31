import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { postService } from '../services/postService';
import type { Post } from '../types/post';

function PostDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [post, setPost] = useState<Post | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (id) {
      fetchPost(Number(id));
    }
  }, [id]);

  const fetchPost = async (postId: number) => {
    try {
      setLoading(true);
      const data = await postService.getById(postId);
      setPost(data);
    } catch (err) {
      setError('投稿の取得に失敗しました');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!post || !window.confirm('本当に削除しますか？')) return;

    try {
      await postService.delete(post.id);
      alert('削除しました');
      navigate('/posts');
    } catch (err) {
      alert('削除に失敗しました');
      console.error(err);
    }
  };

  if (loading) return <div style={{ padding: '20px' }}>読み込み中...</div>;
  if (error) return <div style={{ padding: '20px', color: 'red' }}>{error}</div>;
  if (!post) return <div style={{ padding: '20px' }}>投稿が見つかりません</div>;

  return (
    <div style={{ padding: '20px', maxWidth: '800px', margin: '0 auto' }}>
      <div style={{ marginBottom: '20px' }}>
        <Link to="/posts">← 一覧に戻る</Link>
      </div>

      <div style={{ border: '1px solid #ddd', borderRadius: '8px', padding: '20px' }}>
        <div style={{ marginBottom: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <h1 style={{ margin: 0 }}>{post.title}</h1>
          <span style={{
            padding: '6px 12px',
            borderRadius: '4px',
            backgroundColor: post.status === 'published' ? '#4CAF50' : '#FFC107',
            color: 'white',
            fontSize: '14px'
          }}>
            {post.status === 'published' ? '公開' : '下書き'}
          </span>
        </div>

        <div style={{ marginBottom: '20px', color: '#666', fontSize: '14px' }}>
          <p>著者: {post.author}</p>
          <p>作成日: {new Date(post.created_at).toLocaleString('ja-JP')}</p>
          <p>更新日: {new Date(post.updated_at).toLocaleString('ja-JP')}</p>
        </div>

        <div style={{ marginBottom: '30px', lineHeight: '1.8', whiteSpace: 'pre-wrap' }}>
          {post.content}
        </div>

        <div style={{ display: 'flex', gap: '10px' }}>
          <Link to={`/posts/${post.id}/edit`}>
            <button style={{
              padding: '10px 20px',
              backgroundColor: '#2196F3',
              color: 'white',
              border: 'none',
              borderRadius: '4px',
              cursor: 'pointer'
            }}>
              編集
            </button>
          </Link>
          <button
            onClick={handleDelete}
            style={{
              padding: '10px 20px',
              backgroundColor: '#f44336',
              color: 'white',
              border: 'none',
              borderRadius: '4px',
              cursor: 'pointer'
            }}
          >
            削除
          </button>
        </div>
      </div>
    </div>
  );
}

export default PostDetail;