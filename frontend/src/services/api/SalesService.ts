import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Sale, CreateSaleDTO } from '@/modules/sales/types';
import { API_ENDPOINTS } from '@/config/api';

export class SalesService {
  static async getAll(): Promise<ApiResponse<Sale[]>> {
    const response = await api.get<ApiResponse<Sale[]>>(API_ENDPOINTS.SALES);
    return response.data;
  }

  static async getById(id: number): Promise<ApiResponse<Sale>> {
    const response = await api.get<ApiResponse<Sale>>(`${API_ENDPOINTS.SALES}/${id}`);
    return response.data;
  }

  static async create(data: CreateSaleDTO): Promise<ApiResponse<Sale>> {
    const response = await api.post<ApiResponse<Sale>>(API_ENDPOINTS.SALES, data);
    return response.data;
  }

  static async cancel(id: number): Promise<ApiResponse<Sale>> {
    const response = await api.post<ApiResponse<Sale>>(`${API_ENDPOINTS.SALES}/${id}/cancel`);
    return response.data;
  }

  static async delete(id: number): Promise<void> {
    await api.delete(`${API_ENDPOINTS.SALES}/${id}`);
  }
}
