export type Role = 'ADMIN' | 'COORDINATOR' | 'VETERINARIAN' | 'INSPECTOR' | 'CITIZEN';

export interface UserRole {
  id: number;
  name: Role;
}

export interface User {
  id: number;
  first_name: string;
  last_name: string;
  name: string;
  email: string;
  identity_document?: string;
  phone?: string;
  address?: string;
  sector?: string;
  gender?: 'M' | 'F' | 'O';
  birth_date?: string;
  status: string;
  roles?: UserRole[];
  created_at?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  first_name: string;
  last_name: string;
  identity_document: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}
