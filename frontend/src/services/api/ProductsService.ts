import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Product, CreateProductDTO } from '@/modules/products/types';

export const productsService = {
  getAll() {
    return api.get<ApiResponse<Product[]>>('/product/products');
  },

  getById(id: number) {
    return api.get<ApiResponse<Product>>(`/product/products/${id}`);
  },

  create(data: CreateProductDTO) {
    return api.post<ApiResponse<Product>>('/product/products', data);
  },

  update(id: number, data: Partial<CreateProductDTO>) {
    return api.put<ApiResponse<Product>>(`/product/products/${id}`, data);
  },

  delete(id: number) {
    return api.delete(`/product/products/${id}`);
  },
};
