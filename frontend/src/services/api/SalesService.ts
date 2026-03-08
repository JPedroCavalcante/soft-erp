import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Sale, CreateSaleDTO } from '@/stores/sales';

export const salesService = {
  getAll() {
    return api.get<ApiResponse<Sale[]>>('/sale/sales');
  },

  getById(id: number) {
    return api.get<ApiResponse<Sale>>(`/sale/sales/${id}`);
  },

  create(data: CreateSaleDTO) {
    return api.post<ApiResponse<Sale>>('/sale/sales', data);
  },

  delete(id: number) {
    return api.delete(`/sale/sales/${id}`);
  },
};
