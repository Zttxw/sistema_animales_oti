export type Role = 'ADMIN' | 'COORDINATOR' | 'VETERINARIAN' | 'INSPECTOR' | 'CITIZEN';

export interface User {
  id: number;
  name: string;
  email: string;
  roles?: { name: string }[];
}
