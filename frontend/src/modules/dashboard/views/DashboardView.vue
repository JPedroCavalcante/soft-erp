<template>
  <div class="dashboard">
    <h1 class="title">Dashboard</h1>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Carregando métricas...</p>
    </div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else-if="metrics" class="metrics-grid">
      <div class="metric-card sales">
        <div class="metric-icon">💰</div>
        <div class="metric-content">
          <h3>Vendas do Mês</h3>
          <p class="metric-value">R$ {{ formatCurrency(metrics.sales_this_month) }}</p>
        </div>
      </div>

      <div class="metric-card profit">
        <div class="metric-icon">📈</div>
        <div class="metric-content">
          <h3>Lucro do Mês</h3>
          <p class="metric-value">R$ {{ formatCurrency(metrics.profit_this_month) }}</p>
        </div>
      </div>

      <div class="metric-card comparison">
        <div class="metric-icon">📊</div>
        <div class="metric-content">
          <h3>Hoje vs Ontem</h3>
          <p class="metric-value">
            <span :class="comparisonClass">
              {{ metrics.sales_comparison.change_percentage.toFixed(1) }}%
            </span>
          </p>
          <p class="metric-subtitle">
            Hoje: R$ {{ formatCurrency(metrics.sales_comparison.today) }}
          </p>
        </div>
      </div>

      <div class="metric-card stock-value">
        <div class="metric-icon">💵</div>
        <div class="metric-content">
          <h3>Valor Total Estoque</h3>
          <p class="metric-value">R$ {{ formatCurrency(metrics.total_stock_value) }}</p>
        </div>
      </div>

      <div class="metric-card wide top-products">
        <h3>🏆 Top 5 Produtos Mais Vendidos</h3>
        <div class="products-list">
          <div
            v-for="product in metrics.top_selling_products"
            :key="product.id"
            class="product-item"
          >
            <span class="product-name">{{ product.name }}</span>
            <span class="product-quantity">{{ product.total_quantity }} vendidos</span>
          </div>
          <p v-if="metrics.top_selling_products.length === 0" class="no-data">
            Nenhuma venda registrada
          </p>
        </div>
      </div>

      <div class="metric-card wide low-stock">
        <h3>⚠️ Produtos com Estoque Baixo</h3>
        <div class="products-list">
          <div
            v-for="product in metrics.low_stock_products"
            :key="product.id"
            class="product-item"
          >
            <span class="product-name">{{ product.name }}</span>
            <span class="product-stock" :class="stockClass(product.stock)">
              {{ product.stock }} unidades
            </span>
          </div>
          <p v-if="metrics.low_stock_products.length === 0" class="no-data">
            ✅ Todos os produtos com estoque adequado
          </p>
        </div>
      </div>
    </div>

    <button @click="refreshMetrics" class="refresh-button" :disabled="loading">
      🔄 Atualizar Métricas
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useDashboardStore } from '../store/useDashboardStore'

const dashboardStore = useDashboardStore()

const metrics = computed(() => dashboardStore.metrics)
const loading = computed(() => dashboardStore.loading)
const error = computed(() => dashboardStore.error)

const comparisonClass = computed(() => {
  if (!metrics.value) return ''
  const change = metrics.value.sales_comparison.change_percentage
  return change >= 0 ? 'positive' : 'negative'
})

function formatCurrency(value: string): string {
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

function stockClass(stock: number): string {
  if (stock === 0) return 'critical'
  if (stock <= 5) return 'warning'
  return 'low'
}

async function refreshMetrics() {
  await dashboardStore.fetchMetrics()
}

onMounted(() => {
  dashboardStore.fetchMetrics()
})
</script>

<style scoped>
.dashboard {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.title {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #2c3e50;
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

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.metric-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.metric-card.wide {
  grid-column: span 2;
}

.metric-card.sales {
  border-left: 4px solid #27ae60;
}

.metric-card.profit {
  border-left: 4px solid #2ecc71;
}

.metric-card.comparison {
  border-left: 4px solid #3498db;
}

.metric-card.stock-value {
  border-left: 4px solid #9b59b6;
}

.metric-card.top-products {
  border-left: 4px solid #f39c12;
}

.metric-card.low-stock {
  border-left: 4px solid #e74c3c;
}

.metric-card h3 {
  font-size: 1rem;
  color: #7f8c8d;
  margin-bottom: 0.5rem;
}

.metric-icon {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.metric-content {
  flex: 1;
}

.metric-value {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin: 0.5rem 0;
}

.metric-subtitle {
  font-size: 0.875rem;
  color: #95a5a6;
  margin-top: 0.5rem;
}

.positive {
  color: #27ae60;
}

.negative {
  color: #e74c3c;
}

.products-list {
  margin-top: 1rem;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.product-name {
  font-weight: 500;
  color: #2c3e50;
}

.product-quantity {
  color: #7f8c8d;
  font-size: 0.875rem;
}

.product-stock {
  font-weight: 600;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
}

.product-stock.critical {
  background-color: #fee;
  color: #c33;
}

.product-stock.warning {
  background-color: #ffeaa7;
  color: #d35400;
}

.product-stock.low {
  background-color: #fff3cd;
  color: #856404;
}

.no-data {
  text-align: center;
  color: #95a5a6;
  padding: 1rem;
  font-style: italic;
}

.refresh-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
  display: block;
  margin: 0 auto;
}

.refresh-button:hover:not(:disabled) {
  background-color: #2980b9;
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .dashboard {
    padding: 1rem;
  }

  .metric-card.wide {
    grid-column: span 1;
  }

  .metrics-grid {
    grid-template-columns: 1fr;
  }

  .metric-value {
    font-size: 1.5rem;
  }
}
</style>
