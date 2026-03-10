<template>
  <div class="reports">
    <h1 class="title">Relatórios</h1>

    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="['tab', { active: activeTab === tab.value }]"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="filters-section">
      <div class="filters">
        <div v-if="activeTab !== 'stock'" class="filter-group">
          <label>Data Inicial:</label>
          <input v-model="filters.start_date" type="date" class="input" />
        </div>

        <div v-if="activeTab !== 'stock'" class="filter-group">
          <label>Data Final:</label>
          <input v-model="filters.end_date" type="date" class="input" />
        </div>

        <div v-if="activeTab === 'sales'" class="filter-group">
          <label>Cliente:</label>
          <input v-model="filters.customer" type="text" class="input" placeholder="Nome do cliente" />
        </div>

        <div v-if="activeTab === 'purchases'" class="filter-group">
          <label>Fornecedor:</label>
          <input v-model="filters.supplier" type="text" class="input" placeholder="Nome do fornecedor" />
        </div>

        <div v-if="activeTab === 'profit'" class="filter-group">
          <label>Produto ID:</label>
          <input v-model.number="filters.product_id" type="number" class="input" placeholder="ID do produto" />
        </div>
      </div>

      <div class="filter-actions">
        <button @click="applyFilters" class="btn btn-primary" :disabled="loading">
          🔍 Buscar
        </button>
        <button @click="clearFilters" class="btn btn-secondary">
          🗑️ Limpar
        </button>
      </div>
    </div>

    <div class="export-section">
      <button @click="exportPDF" class="btn btn-export" :disabled="loading || !hasData">
        📄 Exportar PDF
      </button>
      <button @click="exportExcel" class="btn btn-export" :disabled="loading || !hasData">
        📊 Exportar Excel
      </button>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Carregando...</p>
    </div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else class="report-content">
      <div v-if="activeTab === 'sales'" class="report-table">
        <table v-if="salesData.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Valor Total</th>
              <th>Lucro</th>
              <th>Qtd Itens</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="sale in salesData" :key="sale.id">
              <td>{{ sale.id }}</td>
              <td>{{ sale.customer }}</td>
              <td>R$ {{ formatCurrency(sale.total_amount) }}</td>
              <td>R$ {{ formatCurrency(sale.total_profit) }}</td>
              <td>{{ sale.items_count }}</td>
              <td>{{ formatDate(sale.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="no-data">Nenhum dado encontrado</p>
      </div>

      <div v-if="activeTab === 'purchases'" class="report-table">
        <table v-if="purchasesData.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fornecedor</th>
              <th>Valor Total</th>
              <th>Qtd Itens</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="purchase in purchasesData" :key="purchase.id">
              <td>{{ purchase.id }}</td>
              <td>{{ purchase.supplier }}</td>
              <td>R$ {{ formatCurrency(purchase.total_amount) }}</td>
              <td>{{ purchase.items_count }}</td>
              <td>{{ formatDate(purchase.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="no-data">Nenhum dado encontrado</p>
      </div>

      <div v-if="activeTab === 'profit'" class="report-table">
        <table v-if="profitData.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Produto</th>
              <th>Qtd Vendida</th>
              <th>Lucro Total</th>
              <th>Preço Médio</th>
              <th>Custo Médio</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in profitData" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.total_quantity_sold }}</td>
              <td class="profit">R$ {{ formatCurrency(item.total_profit) }}</td>
              <td>R$ {{ formatCurrency(item.avg_sale_price) }}</td>
              <td>R$ {{ formatCurrency(item.avg_cost) }}</td>
            </tr>
          </tbody>
        </table>
        <p v-else class="no-data">Nenhum dado encontrado</p>
      </div>

      <div v-if="activeTab === 'stock'" class="report-table">
        <table v-if="stockData.length > 0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Produto</th>
              <th>Estoque</th>
              <th>Custo Médio</th>
              <th>Preço Venda</th>
              <th>Valor Estoque</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in stockData" :key="product.id">
              <td>{{ product.id }}</td>
              <td>{{ product.name }}</td>
              <td>{{ product.stock }}</td>
              <td>R$ {{ formatCurrency(product.average_cost) }}</td>
              <td>R$ {{ formatCurrency(product.sale_price) }}</td>
              <td>R$ {{ formatCurrency(product.stock_value) }}</td>
              <td>
                <span :class="['status-badge', getStatusClass(product.stock_status)]">
                  {{ product.stock_status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-else class="no-data">Nenhum dado encontrado</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useReportsStore } from '../store/useReportsStore'
import type { ReportFilters } from '@/services/ReportsService'

const reportsStore = useReportsStore()

const activeTab = ref<'sales' | 'purchases' | 'profit' | 'stock'>('sales')
const filters = ref<ReportFilters>({})

const tabs = [
  { label: '📋 Vendas', value: 'sales' },
  { label: '📋 Compras', value: 'purchases' },
  { label: '💰 Lucro por Produto', value: 'profit' },
  { label: '📦 Estoque', value: 'stock' }
]

const salesData = computed(() => reportsStore.salesData)
const purchasesData = computed(() => reportsStore.purchasesData)
const profitData = computed(() => reportsStore.profitData)
const stockData = computed(() => reportsStore.stockData)
const loading = computed(() => reportsStore.loading)
const error = computed(() => reportsStore.error)

const hasData = computed(() => {
  switch (activeTab.value) {
    case 'sales':
      return salesData.value.length > 0
    case 'purchases':
      return purchasesData.value.length > 0
    case 'profit':
      return profitData.value.length > 0
    case 'stock':
      return stockData.value.length > 0
    default:
      return false
  }
})

function formatCurrency(value: string): string {
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('pt-BR')
}

function getStatusClass(status: string): string {
  const statusMap: Record<string, string> = {
    'SEM ESTOQUE': 'critical',
    'ESTOQUE BAIXO': 'warning',
    'ESTOQUE NORMAL': 'normal',
    'ESTOQUE ALTO': 'high'
  }
  return statusMap[status] || ''
}

async function applyFilters() {
  const cleanFilters = { ...filters.value }
  Object.keys(cleanFilters).forEach(key => {
    if (cleanFilters[key as keyof ReportFilters] === '' || cleanFilters[key as keyof ReportFilters] === undefined) {
      delete cleanFilters[key as keyof ReportFilters]
    }
  })

  switch (activeTab.value) {
    case 'sales':
      await reportsStore.fetchSalesReport(cleanFilters)
      break
    case 'purchases':
      await reportsStore.fetchPurchasesReport(cleanFilters)
      break
    case 'profit':
      await reportsStore.fetchProfitReport(cleanFilters)
      break
    case 'stock':
      await reportsStore.fetchStockReport()
      break
  }
}

function clearFilters() {
  filters.value = {}
}

async function exportPDF() {
  await reportsStore.exportReport({
    type: 'pdf',
    report_name: activeTab.value,
    ...filters.value
  })
}

async function exportExcel() {
  await reportsStore.exportReport({
    type: 'excel',
    report_name: activeTab.value,
    ...filters.value
  })
}

onMounted(() => {
  applyFilters()
})
</script>

<style scoped>
.reports {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.title {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #2c3e50;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  border-bottom: 2px solid #e0e0e0;
}

.tab {
  padding: 0.75rem 1.5rem;
  background: none;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-size: 1rem;
  color: #7f8c8d;
  transition: all 0.3s;
}

.tab:hover {
  color: #2c3e50;
}

.tab.active {
  color: #3498db;
  border-bottom-color: #3498db;
  font-weight: 600;
}

.filters-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  font-size: 0.875rem;
  color: #7f8c8d;
  margin-bottom: 0.25rem;
}

.input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.875rem;
}

.filter-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.export-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #3498db;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2980b9;
}

.btn-secondary {
  background-color: #95a5a6;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #7f8c8d;
}

.btn-export {
  background-color: #27ae60;
  color: white;
}

.btn-export:hover:not(:disabled) {
  background-color: #229954;
}

.loading {
  text-align: center;
  padding: 3rem;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  background-color: #fee;
  color: #c33;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.report-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.report-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background-color: #f8f9fa;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #2c3e50;
  border-bottom: 2px solid #e0e0e0;
}

td {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
  color: #34495e;
}

tr:hover {
  background-color: #f8f9fa;
}

.profit {
  color: #27ae60;
  font-weight: 600;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-badge.critical {
  background-color: #fee;
  color: #c33;
}

.status-badge.warning {
  background-color: #ffeaa7;
  color: #d35400;
}

.status-badge.normal {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-badge.high {
  background-color: #d4edda;
  color: #155724;
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: #95a5a6;
  font-style: italic;
}

@media (max-width: 768px) {
  .reports {
    padding: 1rem;
  }

  .tabs {
    flex-wrap: wrap;
  }

  .filters {
    grid-template-columns: 1fr;
  }

  .export-section {
    flex-direction: column;
  }
}
</style>
