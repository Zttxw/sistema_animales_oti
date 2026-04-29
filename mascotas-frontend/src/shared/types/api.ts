/** Generic paginated response from Laravel */
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

/** Generic API error shape */
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

/** Query params for paginated endpoints */
export interface PaginationParams {
  page?: number;
  per_page?: number;
  search?: string;
}
