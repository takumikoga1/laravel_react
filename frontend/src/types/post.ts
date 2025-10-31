export interface Post {
  id: number;
  title: string;
  content: string;
  author: string;
  status: 'draft' | 'published';
  created_at: string;
  updated_at: string;
}

export type CreatePostData = Omit<Post, 'id' | 'created_at' | 'updated_at'>;

export type UpdatePostData = Partial<CreatePostData>;