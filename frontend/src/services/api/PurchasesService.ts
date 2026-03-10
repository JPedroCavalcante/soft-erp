import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Purchase, CreatePurchaseDTO } from '@/modules/purchases/types';
import { API_ENDPOINTS } from '@/config/api';

export class PurchasesService {
  static async getAll(): Promise<ApiResponse<Purchase[]>> {
    const response = await api.get<ApiResponse<Purchase[]>>(API_ENDPOINTS.PURCHASES);
    return response.data;
  }

  static async getById(id: number): Promise<ApiResponse<Purchase>> {
    const response = await api.get<ApiResponse<Purchase>>(`${API_ENDPOINTS.PURCHASES}/${id}`);
    return response.data;
  }

  static async create(data: CreatePurchaseDTO): Promise<ApiResponse<Purchase>> {
    const response = await api.post<ApiResponse<Purchase>>(API_ENDPOINTS.PURCHASES, data);
    return response.data;
  }

  static async delete(id: number): Promise<void> {
    await api.delete(`${API_ENDPOINTS.PURCHASES}/${id}`);
  }
}
