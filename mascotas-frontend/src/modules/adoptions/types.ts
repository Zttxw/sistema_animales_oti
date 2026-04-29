import type { AdoptionStatus } from '../../shared/types/common';

export interface Adoption {
  id: number;
  animal_id: number;
  status: AdoptionStatus;
  reason?: string;
  description?: string;
  requirements?: string;
  contact?: string;
  admin_notes?: string;
  adopted_by?: number;
  adopted_at?: string;
  reviewed_by?: number;
  reviewed_at?: string;
  created_at: string;
  animal?: { id: number; name: string; municipal_code: string; species?: { name: string }; breed?: { name: string }; photos?: { url: string; is_cover: boolean }[] };
  adopter?: { id: number; first_name: string; last_name: string };
  reviewer?: { id: number; first_name: string; last_name: string };
}

export interface AdoptionFormData {
  animal_id: number | string;
  reason?: string;
  description?: string;
  requirements?: string;
  contact?: string;
  photo_url?: string;
}

export interface AdoptionFilters {
  status?: string;
  species_id?: number | string;
  breed_id?: number | string;
  page?: number;
}
