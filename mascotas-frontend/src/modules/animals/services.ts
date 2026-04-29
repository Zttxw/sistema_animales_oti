import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';
import type { Animal, AnimalFormData, AnimalFilters, AnimalHistory, Vaccination, HealthProcedure } from './types';
import type { Species, Breed } from '../../shared/types/common';

// ── Animals CRUD ──
export const animalService = {
  list: (params: AnimalFilters = {}) =>
    api.get<PaginatedResponse<Animal>>('/animals', { params }),

  get: (id: number) =>
    api.get<Animal>(`/animals/${id}`),

  create: (data: AnimalFormData) =>
    api.post<Animal>('/animals', data),

  update: (id: number, data: Partial<AnimalFormData>) =>
    api.put<Animal>(`/animals/${id}`, data),

  updateStatus: (id: number, status: string, extra?: Record<string, any>) =>
    api.patch<Animal>(`/animals/${id}/status`, { status, ...extra }),

  delete: (id: number) =>
    api.delete(`/animals/${id}`),

  history: (id: number) =>
    api.get<AnimalHistory[]>(`/animals/${id}/history`),
};

// ── Photos ──
export const photoService = {
  upload: (animalId: number, urls: string[], coverIndex?: number) =>
    api.post(`/animals/${animalId}/photos`, { urls, cover_index: coverIndex }),

  setCover: (animalId: number, photoId: number) =>
    api.patch(`/animals/${animalId}/photos/${photoId}/cover`),

  delete: (animalId: number, photoId: number) =>
    api.delete(`/animals/${animalId}/photos/${photoId}`),
};

// ── Vaccinations ──
export const vaccinationService = {
  list: (animalId: number) =>
    api.get<Vaccination[]>(`/animals/${animalId}/vaccinations`),

  create: (animalId: number, data: Record<string, any>) =>
    api.post<Vaccination>(`/animals/${animalId}/vaccinations`, data),

  update: (animalId: number, id: number, data: Record<string, any>) =>
    api.put<Vaccination>(`/animals/${animalId}/vaccinations/${id}`, data),

  delete: (animalId: number, id: number) =>
    api.delete(`/animals/${animalId}/vaccinations/${id}`),
};

// ── Health Procedures ──
export const procedureService = {
  list: (animalId: number) =>
    api.get<HealthProcedure[]>(`/animals/${animalId}/procedures`),

  create: (animalId: number, data: Record<string, any>) =>
    api.post<HealthProcedure>(`/animals/${animalId}/procedures`, data),

  update: (animalId: number, id: number, data: Record<string, any>) =>
    api.put<HealthProcedure>(`/animals/${animalId}/procedures/${id}`, data),

  delete: (animalId: number, id: number) =>
    api.delete(`/animals/${animalId}/procedures/${id}`),
};

// ── Catalogs ──
export const catalogService = {
  species: () => api.get<Species[]>('/catalogs/species'),
  breeds: (speciesId: number) => api.get<Breed[]>(`/catalogs/species/${speciesId}/breeds`),
  vaccines: () => api.get('/catalogs/vaccines'),
  procedureTypes: () => api.get('/catalogs/procedure-types'),
};
