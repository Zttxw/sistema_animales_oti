import type { User } from '../../types/auth';

export interface UserFilters {
  search?: string;
  status?: string;
  role?: string;
  page?: number;
}

export interface UserFormData {
  first_name: string;
  last_name: string;
  identity_document: string;
  email: string;
  password?: string;
  birth_date?: string;
  gender?: string;
  phone?: string;
  address?: string;
  sector?: string;
  role: string;
}

export type { User };
