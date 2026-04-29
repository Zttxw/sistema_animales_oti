import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';

export interface StrayAnimal {
  id: number;
  species_id: number;
  description?: string;
  location: string;
  latitude?: number;
  longitude?: number;
  status: string;
  risk_level?: string;
  estimated_age?: string;
  color?: string;
  size?: string;
  health_condition?: string;
  notes?: string;
  reported_by: number;
  created_at: string;
  species?: { id: number; name: string };
  reporter?: { id: number; first_name: string; last_name: string };
  photos?: { id: number; url: string }[];
}

export interface StrayAnimalFormData {
  species_id: number | string;
  description?: string;
  location: string;
  risk_level?: string;
  estimated_age?: string;
  color?: string;
  size?: string;
  health_condition?: string;
  notes?: string;
}

export const strayAnimalService = {
  list: (params: Record<string, any> = {}) =>
    api.get<PaginatedResponse<StrayAnimal>>('/stray-animals', { params }),
  get: (id: number) =>
    api.get<StrayAnimal>(`/stray-animals/${id}`),
  create: (data: StrayAnimalFormData) =>
    api.post<StrayAnimal>('/stray-animals', data),
  update: (id: number, data: Partial<StrayAnimalFormData>) =>
    api.put<StrayAnimal>(`/stray-animals/${id}`, data),
  updateStatus: (id: number, status: string) =>
    api.patch(`/stray-animals/${id}/status`, { status }),
  delete: (id: number) =>
    api.delete(`/stray-animals/${id}`),
};
