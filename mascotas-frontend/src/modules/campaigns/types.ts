import type { CampaignStatus, CampaignType } from '../../shared/types/common';

export interface Campaign {
  id: number;
  title: string;
  campaign_type_id: number;
  description?: string;
  scheduled_at: string;
  location?: string;
  capacity?: number;
  status: CampaignStatus;
  target_audience?: string;
  requirements?: string;
  created_by: number;
  created_at: string;
  campaign_type?: CampaignType;
  created_by_user?: { id: number; first_name: string; last_name: string };
  participants_count?: number;
}

export interface CampaignFormData {
  title: string;
  campaign_type_id: number | string;
  description?: string;
  scheduled_at: string;
  location?: string;
  capacity?: number | string;
  target_audience?: string;
  requirements?: string;
}

export interface CampaignFilters {
  status?: string;
  page?: number;
}
