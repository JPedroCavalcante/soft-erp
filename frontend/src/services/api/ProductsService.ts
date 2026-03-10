import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Product, CreateProductDTO } from '@/modules/products/types';
import { API_ENDPOINTS } from '@/config/api';

export class ProductsService {
  static async getAll(): Promise<ApiResponse<Product[]>> {
    const response = await api.get<ApiResponse<Product[]>>(API_ENDPOINTS.PRODUCTS);
    return response.data;
  }

  static async getById(id: number): Promise<ApiResponse<Product>> {
    const response = await api.get<ApiResponse<Product>>(`${API_ENDPOINTS.PRODUCTS}/${id}`);
    return response.data;
  }

  static async create(data: CreateProductDTO): Promise<ApiResponse<Product>> {
    const response = await api.post<ApiResponse<Product>>(API_ENDPOINTS.PRODUCTS, data);
    return response.data;
  }

  static async update(id: number, data: Partial<CreateProductDTO>): Promise<ApiResponse<Product>> {
    const response = await api.put<ApiResponse<Product>>(`${API_ENDPOINTS.PRODUCTS}/${id}`, data);
    return response.data;
  }

  static async delete(id: number): Promise<void> {
    await api.delete(`${API_ENDPOINTS.PRODUCTS}/${id}`);
  }
}
