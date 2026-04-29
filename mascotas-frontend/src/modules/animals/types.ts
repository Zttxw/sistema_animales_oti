import type { Species, Breed, AnimalStatus, Gender, AnimalSize, ReproductiveStatus } from '../../shared/types/common';

export interface AnimalPhoto {
  id: number;
  animal_id: number;
  url: string;
  is_cover: boolean;
}

export interface AnimalHistory {
  id: number;
  animal_id: number;
  user_id: number;
  change_type: 'DATA' | 'STATUS' | 'VACCINE' | 'PROCEDURE' | 'ADOPTION';
  description: string;
  previous_data?: Record<string, any>;
  created_at: string;
  registered_by?: { id: number; first_name: string; last_name: string };
}

export interface Vaccination {
  id: number;
  animal_id: number;
  vaccine_id?: number;
  vaccine_name: string;
  applied_at: string;
  next_dose_at?: string;
  notes?: string;
  file_path?: string;
  campaign_id?: number;
  registered_by?: { id: number; first_name: string; last_name: string };
  vaccine?: { id: number; name: string };
  campaign?: { id: number; title: string };
}

export interface HealthProcedure {
  id: number;
  animal_id: number;
  procedure_type_id: number;
  type_detail?: string;
  performed_at: string;
  description?: string;
  notes?: string;
  file_url?: string;
  registered_by?: { id: number; first_name: string; last_name: string };
  procedure_type?: { id: number; name: string };
  campaign?: { id: number; title: string };
}

export interface Animal {
  id: number;
  municipal_code: string;
  user_id: number;
  species_id: number;
  breed_id?: number;
  name: string;
  gender: Gender;
  birth_date?: string;
  approximate_age?: string;
  color?: string;
  size?: AnimalSize;
  reproductive_status?: ReproductiveStatus;
  distinctive_features?: string;
  status: AnimalStatus;
  notes?: string;
  death_date?: string;
  death_reason?: string;
  created_at: string;
  updated_at: string;
  // Relations
  species?: Species;
  breed?: Breed;
  owner?: { id: number; first_name: string; last_name: string; email: string };
  photos?: AnimalPhoto[];
  cover_photo?: AnimalPhoto;
  vaccinations?: Vaccination[];
  health_procedures?: HealthProcedure[];
}

export interface AnimalFormData {
  user_id: number | string;
  species_id: number | string;
  breed_id?: number | string;
  name: string;
  gender: Gender | '';
  birth_date?: string;
  approximate_age?: string;
  color?: string;
  size?: AnimalSize | '';
  reproductive_status?: ReproductiveStatus | '';
  distinctive_features?: string;
  notes?: string;
}

export interface AnimalFilters {
  search?: string;
  species_id?: number | string;
  status?: AnimalStatus | '';
  page?: number;
}
