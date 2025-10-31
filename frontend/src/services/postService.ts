import api from './api';
import type { Post, CreatePostData, UpdatePostData } from '../types/post';

// APIレスポンスの型定義
interface PostResponse {
  data: Post;
}

interface PostsResponse {
  data: Post[];
}

export const postService = {
  // 一覧取得
  getAll: async (): Promise<Post[]> => {
    const response = await api.get<PostsResponse>('/posts');
    return response.data.data;
  },

  // 詳細取得
  getById: async (id: number): Promise<Post> => {
    const response = await api.get<PostResponse>(`/posts/${id}`);
    return response.data.data;
  },

  // 新規作成
  create: async (data: CreatePostData): Promise<Post> => {
    const response = await api.post<PostResponse>('/posts', data);
    return response.data.data;
  },

  // 更新
  update: async (id: number, data: UpdatePostData): Promise<Post> => {
    const response = await api.put<PostResponse>(`/posts/${id}`, data);
    return response.data.data;
  },

  // 削除
  delete: async (id: number): Promise<void> => {
    await api.delete(`/posts/${id}`);
  },
};