import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';

export interface Post {
  id: number;
  author_id: number;
  post_type_id: number;
  title: string;
  content: string;
  status: string;
  created_at: string;
  post_type?: { id: number; name: string };
  author?: { id: number; first_name: string; last_name: string };
  comments_count?: number;
}

export interface Comment {
  id: number;
  post_id: number;
  user_id: number;
  content: string;
  status: string;
  created_at: string;
  user?: { id: number; first_name: string; last_name: string };
}

export const postService = {
  list: (params: Record<string, any> = {}) =>
    api.get<PaginatedResponse<Post>>('/posts', { params }),
  get: (id: number) =>
    api.get<Post>(`/posts/${id}`),
  create: (data: Record<string, any>) =>
    api.post<Post>('/posts', data),
  update: (id: number, data: Record<string, any>) =>
    api.put<Post>(`/posts/${id}`, data),
  delete: (id: number) =>
    api.delete(`/posts/${id}`),
  updateStatus: (id: number, status: string) =>
    api.patch(`/posts/${id}/status`, { status }),
  // Comments
  comments: (postId: number) =>
    api.get<Comment[]>(`/posts/${postId}/comments`),
  addComment: (postId: number, content: string) =>
    api.post(`/posts/${postId}/comments`, { content }),
  deleteComment: (postId: number, commentId: number) =>
    api.delete(`/posts/${postId}/comments/${commentId}`),
  // Catalogs
  postTypes: () =>
    api.get('/catalogs/post-types'),
};
