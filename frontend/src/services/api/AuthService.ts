import api from '@/core/api';
import type { ApiResponse } from '@/types';
import type { User, LoginCredentials } from '@/modules/auth/types';

export const authService = {
  async login(credentials: LoginCredentials) {
    return api.post<ApiResponse<User>>('/login', credentials);
  },

  async logout() {
    return api.post('/logout');
  },

  async me() {
    return api.get<ApiResponse<User>>('/user');
  },
};
