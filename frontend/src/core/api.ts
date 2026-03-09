import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { API_CONFIG } from '@/config';

const api = axios.create({
  baseURL: API_CONFIG.BASE_URL,
  timeout: API_CONFIG.TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error: AxiosError) => Promise.reject(error)
);

api.interceptors.response.use(
  (response: any) => response,
  (error: AxiosError) => {
    const status = error.response?.status;

    if (status === 500) {
      console.error('Erro interno do servidor');
    } else if (status === 404) {
      console.error('Recurso não encontrado');
    } else if (status === 403) {
      console.error('Sem permissão para acessar este recurso');
    } else if (status === 401) {
      console.error('Não autenticado');
    } else if (!error.response) {
      console.error('Erro de conexão com o servidor');
    }

    return Promise.reject(error);
  }
);

export default api;
