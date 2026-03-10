import { defineStore } from 'pinia'
import { ref } from 'vue'
import { ReportsService, type SalesReport, type PurchasesReport, type ProfitReport, type StockReport, type ReportFilters, type ExportRequest } from '@/services/ReportsService'

export const useReportsStore = defineStore('reports', () => {
  const salesData = ref<SalesReport[]>([])
  const purchasesData = ref<PurchasesReport[]>([])
  const profitData = ref<ProfitReport[]>([])
  const stockData = ref<StockReport[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchSalesReport(filters: ReportFilters) {
    loading.value = true
    error.value = null
    try {
      const response = await ReportsService.getSalesReport(filters)
      salesData.value = response.data
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao carregar relatório de vendas'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPurchasesReport(filters: ReportFilters) {
    loading.value = true
    error.value = null
    try {
      const response = await ReportsService.getPurchasesReport(filters)
      purchasesData.value = response.data
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao carregar relatório de compras'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchProfitReport(filters: ReportFilters) {
    loading.value = true
    error.value = null
    try {
      const response = await ReportsService.getProfitReport(filters)
      profitData.value = response.data
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao carregar relatório de lucro'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchStockReport() {
    loading.value = true
    error.value = null
    try {
      const response = await ReportsService.getStockReport()
      stockData.value = response.data
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao carregar relatório de estoque'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function exportReport(request: ExportRequest) {
    loading.value = true
    error.value = null
    try {
      const blob = await ReportsService.exportReport(request)
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      const extension = request.type === 'pdf' ? 'pdf' : 'xlsx'
      link.download = `${request.report_name}_${new Date().toISOString().split('T')[0]}.${extension}`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao exportar relatório'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    salesData,
    purchasesData,
    profitData,
    stockData,
    loading,
    error,
    fetchSalesReport,
    fetchPurchasesReport,
    fetchProfitReport,
    fetchStockReport,
    exportReport
  }
})
