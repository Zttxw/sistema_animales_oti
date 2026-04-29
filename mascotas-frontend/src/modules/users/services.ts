import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';
import type { User, UserFilters, UserFormData } from './types';

export const userService = {
  list: (params: UserFilters = {}) =>
    api.get<PaginatedResponse<User>>('/users', { params }),

  get: (id: number) =>
    api.get<User>(`/users/${id}`),

  create: (data: UserFormData) =>
    api.post<User>('/users', data),

  update: (id: number, data: Partial<UserFormData>) =>
    api.put<User>(`/users/${id}`, data),

  updateStatus: (id: number, status: string) =>
    api.patch(`/users/${id}/status`, { status }),

  updateRole: (id: number, role: string) =>
    api.patch(`/users/${id}/role`, { role }),

  delete: (id: number) =>
    api.delete(`/users/${id}`),
};
