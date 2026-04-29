import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';
import type { Adoption, AdoptionFormData, AdoptionFilters } from './types';

export const adoptionService = {
  list: (params: AdoptionFilters = {}) =>
    api.get<PaginatedResponse<Adoption>>('/adoptions', { params }),
  get: (id: number) =>
    api.get<Adoption>(`/adoptions/${id}`),
  create: (data: AdoptionFormData) =>
    api.post<Adoption>('/adoptions', data),
  update: (id: number, data: Partial<AdoptionFormData>) =>
    api.put<Adoption>(`/adoptions/${id}`, data),
  updateStatus: (id: number, status: string, extra?: Record<string, any>) =>
    api.patch(`/adoptions/${id}/status`, { status, ...extra }),
};
