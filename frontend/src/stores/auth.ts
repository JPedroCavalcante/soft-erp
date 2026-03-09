import { defineStore } from 'pinia';
import { authService } from '@/services/api/AuthService';
import type { AuthState, LoginCredentials } from '@/modules/auth/types';

const TOKEN_KEY = 'auth_token';

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  actions: {
    async login(credentials: LoginCredentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authService.login(credentials);
        const { user, token } = response.data.data;

        localStorage.setItem(TOKEN_KEY, token);

        this.user = user;
        this.isAuthenticated = true;
        return true;
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Erro ao fazer login';
        this.isAuthenticated = false;
        this.user = null;
        localStorage.removeItem(TOKEN_KEY);
        return false;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await authService.logout();
      } catch (err) {
        console.error('Error during logout:', err);
      } finally {
        this.user = null;
        this.isAuthenticated = false;
        this.error = null;
        localStorage.removeItem(TOKEN_KEY);
      }
    },

    async checkAuth() {
      const token = localStorage.getItem(TOKEN_KEY);

      if (!token) {
        this.user = null;
        this.isAuthenticated = false;
        return false;
      }

      this.loading = true;

      try {
        const response = await authService.me();
        this.user = response.data.data;
        this.isAuthenticated = true;
        return true;
      } catch (err) {
        this.user = null;
        this.isAuthenticated = false;
        localStorage.removeItem(TOKEN_KEY);
        return false;
      } finally {
        this.loading = false;
      }
    },

    getToken(): string | null {
      return localStorage.getItem(TOKEN_KEY);
    },
  },
});
