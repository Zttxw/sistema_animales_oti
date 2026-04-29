import api from '../../api/axios';
import type { PaginatedResponse } from '../../shared/types/api';
import type { Campaign, CampaignFormData, CampaignFilters } from './types';
import type { CampaignType } from '../../shared/types/common';

export const campaignService = {
  list: (params: CampaignFilters = {}) =>
    api.get<PaginatedResponse<Campaign>>('/campaigns', { params }),
  get: (id: number) =>
    api.get<Campaign>(`/campaigns/${id}`),
  create: (data: CampaignFormData) =>
    api.post<Campaign>('/campaigns', data),
  update: (id: number, data: Partial<CampaignFormData>) =>
    api.put<Campaign>(`/campaigns/${id}`, data),
  updateStatus: (id: number, status: string) =>
    api.patch(`/campaigns/${id}/status`, { status }),
  registerParticipant: (id: number, userId: number, animalId?: number) =>
    api.post(`/campaigns/${id}/participants`, { user_id: userId, animal_id: animalId }),
  campaignTypes: () =>
    api.get<CampaignType[]>('/catalogs/campaign-types'),
};
