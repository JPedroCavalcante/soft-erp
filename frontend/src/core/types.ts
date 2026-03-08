export interface ApiResponse<T> {
  data: T;
  message?: string;
}

export interface ValidationError {
  message: string;
  errors: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}
