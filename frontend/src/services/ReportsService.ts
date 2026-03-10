import api from '@/core/api'
import type { ApiResponse } from '@/core/types/api'

export interface SalesReport {
  id: number
  customer: string
  total_amount: string
  total_profit: string
  items_count: number
  created_at: string
}

export interface PurchasesReport {
  id: number
  supplier: string
  total_amount: string
  items_count: number
  created_at: string
}

export interface ProfitReport {
  id: number
  name: string
  total_quantity_sold: number
  total_profit: string
  avg_sale_price: string
  avg_cost: string
}

export interface StockReport {
  id: number
  name: string
  stock: number
  average_cost: string
  sale_price: string
  stock_value: string
  stock_status: string
}

export interface ReportFilters {
  start_date?: string
  end_date?: string
  customer?: string
  supplier?: string
  product_id?: number
}

export interface ExportRequest {
  type: 'pdf' | 'excel'
  report_name: 'sales' | 'purchases' | 'profit' | 'stock'
  start_date?: string
  end_date?: string
  customer?: string
  supplier?: string
  product_id?: number
}

export class ReportsService {
  static async getSalesReport(filters: ReportFilters): Promise<ApiResponse<SalesReport[]>> {
    const response = await api.get<ApiResponse<SalesReport[]>>('/report/reports/sales', {
      params: filters
    })
    return response.data
  }

  static async getPurchasesReport(filters: ReportFilters): Promise<ApiResponse<PurchasesReport[]>> {
    const response = await api.get<ApiResponse<PurchasesReport[]>>('/report/reports/purchases', {
      params: filters
    })
    return response.data
  }

  static async getProfitReport(filters: ReportFilters): Promise<ApiResponse<ProfitReport[]>> {
    const response = await api.get<ApiResponse<ProfitReport[]>>('/report/reports/profit', {
      params: filters
    })
    return response.data
  }

  static async getStockReport(): Promise<ApiResponse<StockReport[]>> {
    const response = await api.get<ApiResponse<StockReport[]>>('/report/reports/stock')
    return response.data
  }

  static async exportReport(request: ExportRequest): Promise<Blob> {
    const response = await api.post('/report/reports/export', request, {
      responseType: 'blob'
    })
    return response.data
  }
}
