/** Reusable status type used across modules */
export type AnimalStatus = 'ACTIVE' | 'LOST' | 'FOR_ADOPTION' | 'DECEASED';
export type UserStatus = 'ACTIVE' | 'SUSPENDED' | 'INACTIVE';
export type AdoptionStatus = 'AVAILABLE' | 'IN_PROCESS' | 'ADOPTED' | 'WITHDRAWN';
export type CampaignStatus = 'DRAFT' | 'PUBLISHED' | 'IN_PROGRESS' | 'FINISHED' | 'CANCELLED';
export type Gender = 'M' | 'F' | 'UNKNOWN';
export type AnimalSize = 'SMALL' | 'MEDIUM' | 'LARGE' | 'GIANT';
export type ReproductiveStatus = 'INTACT' | 'SPAYED' | 'NEUTERED' | 'UNKNOWN';

/** Catalog items */
export interface Species {
  id: number;
  name: string;
  active: boolean;
}

export interface Breed {
  id: number;
  species_id: number;
  name: string;
  active: boolean;
}

export interface VaccineCatalog {
  id: number;
  name: string;
  species_id: number;
}

export interface ProcedureType {
  id: number;
  name: string;
  description?: string;
}

export interface CampaignType {
  id: number;
  name: string;
  description?: string;
}

/** Status color mapping helper */
export const STATUS_COLORS: Record<string, { bg: string; color: string }> = {
  ACTIVE:       { bg: '#dcfce7', color: '#166534' },
  SUSPENDED:    { bg: '#fef3c7', color: '#92400e' },
  INACTIVE:     { bg: '#fee2e2', color: '#991b1b' },
  LOST:         { bg: '#fef3c7', color: '#92400e' },
  FOR_ADOPTION: { bg: '#dbeafe', color: '#1e40af' },
  DECEASED:     { bg: '#f3f4f6', color: '#6b7280' },
  AVAILABLE:    { bg: '#dcfce7', color: '#166534' },
  IN_PROCESS:   { bg: '#fef3c7', color: '#92400e' },
  ADOPTED:      { bg: '#dbeafe', color: '#1e40af' },
  WITHDRAWN:    { bg: '#fee2e2', color: '#991b1b' },
  DRAFT:        { bg: '#f3f4f6', color: '#6b7280' },
  PUBLISHED:    { bg: '#dcfce7', color: '#166534' },
  IN_PROGRESS:  { bg: '#fef3c7', color: '#92400e' },
  FINISHED:     { bg: '#dbeafe', color: '#1e40af' },
  CANCELLED:    { bg: '#fee2e2', color: '#991b1b' },
};

/** Status label mapping */
export const STATUS_LABELS: Record<string, string> = {
  ACTIVE: 'Activo',
  SUSPENDED: 'Suspendido',
  INACTIVE: 'Inactivo',
  LOST: 'Perdido',
  FOR_ADOPTION: 'En Adopción',
  DECEASED: 'Fallecido',
  AVAILABLE: 'Disponible',
  IN_PROCESS: 'En Proceso',
  ADOPTED: 'Adoptado',
  WITHDRAWN: 'Retirado',
  DRAFT: 'Borrador',
  PUBLISHED: 'Publicada',
  IN_PROGRESS: 'En Progreso',
  FINISHED: 'Finalizada',
  CANCELLED: 'Cancelada',
};
