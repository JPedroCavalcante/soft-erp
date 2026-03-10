import api from '@/core/api'
import type { ApiResponse } from '@/core/types/api'
import { API_ENDPOINTS } from '@/config/api'

export interface DashboardMetrics {
  sales_this_month: string
  profit_this_month: string
  low_stock_products: Array<{
    id: number
    name: string
    stock: number
    average_cost: string
  }>
  top_selling_products: Array<{
    id: number
    name: string
    total_quantity: number
  }>
  sales_comparison: {
    today: string
    yesterday: string
    change_percentage: number
  }
  total_stock_value: string
}

export class DashboardService {
  static async getMetrics(): Promise<ApiResponse<DashboardMetrics>> {
    const response = await api.get<ApiResponse<DashboardMetrics>>(API_ENDPOINTS.DASHBOARD)
    return response.data
  }
}
