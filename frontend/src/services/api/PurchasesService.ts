import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Purchase, CreatePurchaseDTO } from '@/stores/purchases';

export const purchasesService = {
  getAll() {
    return api.get<ApiResponse<Purchase[]>>('/purchase/purchases');
  },

  getById(id: number) {
    return api.get<ApiResponse<Purchase>>(`/purchase/purchases/${id}`);
  },

  create(data: CreatePurchaseDTO) {
    return api.post<ApiResponse<Purchase>>('/purchase/purchases', data);
  },

  delete(id: number) {
    return api.delete(`/purchase/purchases/${id}`);
  },
};
