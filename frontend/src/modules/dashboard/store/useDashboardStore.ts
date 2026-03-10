import { defineStore } from 'pinia'
import { ref } from 'vue'
import { DashboardService, type DashboardMetrics } from '@/services/DashboardService'

export const useDashboardStore = defineStore('dashboard', () => {
  const metrics = ref<DashboardMetrics | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchMetrics() {
    loading.value = true
    error.value = null
    try {
      const response = await DashboardService.getMetrics()
      metrics.value = response.data
    } catch (err: unknown) {
      const axiosError = err as { response?: { data?: { message?: string } } }
      error.value = axiosError.response?.data?.message || 'Erro ao carregar métricas'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    metrics,
    loading,
    error,
    fetchMetrics
  }
})
