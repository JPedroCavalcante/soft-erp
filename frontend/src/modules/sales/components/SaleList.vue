<template>
  <div class="card">
    <h2>Lista de Vendas</h2>

    <div v-if="loading && sales.length === 0" class="loading-state">
      <div class="spinner-large"></div>
      <p>Carregando vendas...</p>
    </div>

    <div v-else-if="!loading && sales.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <p>Nenhuma venda registrada</p>
      <small>Registre sua primeira venda usando o formulário acima</small>
    </div>

    <div v-else class="table-wrapper">
      <table class="sales-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Lucro</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sale in sales" :key="sale.id">
            <td data-label="ID">{{ sale.id }}</td>
            <td data-label="Cliente" class="customer-name">{{ sale.customer }}</td>
            <td data-label="Total" class="price">{{ formatCurrency(sale.total_amount) }}</td>
            <td data-label="Lucro" :class="['profit', getProfitClass(sale.total_profit)]">
              {{ formatCurrency(sale.total_profit) }}
            </td>
            <td data-label="Data">{{ formatDate(sale.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Sale } from '@/stores/sales';

interface Props {
  sales: Sale[];
  loading?: boolean;
}

withDefaults(defineProps<Props>(), {
  loading: false,
});

const formatCurrency = (value: string | number): string => {
  const numValue = typeof value === 'string' ? parseFloat(value) : value;
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numValue);
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('pt-BR');
};

const getProfitClass = (profit: string): string => {
  const numProfit = parseFloat(profit);
  if (numProfit > 0) return 'profit-positive';
  if (numProfit < 0) return 'profit-negative';
  return 'profit-zero';
};
</script>

<style scoped>
.card {
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 2rem;
}

.card h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 1.5rem 0;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #ecf0f1;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: #7f8c8d;
  text-align: center;
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid #ecf0f1;
  border-top-color: #3498db;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state svg {
  color: #bdc3c7;
  margin-bottom: 1rem;
}

.empty-state p {
  font-size: 1.125rem;
  font-weight: 500;
  margin: 0 0 0.5rem 0;
  color: #7f8c8d;
}

.empty-state small {
  font-size: 0.875rem;
  color: #95a5a6;
}

.table-wrapper {
  overflow-x: auto;
  margin: -0.5rem;
  padding: 0.5rem;
}

.sales-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.sales-table thead {
  background: #f8f9fa;
}

.sales-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #34495e;
  border-bottom: 2px solid #ecf0f1;
  white-space: nowrap;
}

.sales-table tbody tr {
  border-bottom: 1px solid #ecf0f1;
  transition: background-color 0.2s ease;
}

.sales-table tbody tr:nth-child(even) {
  background: #f8f9fa;
}

.sales-table tbody tr:hover {
  background: #e8f4f8;
}

.sales-table td {
  padding: 1rem;
  color: #2c3e50;
}

.customer-name {
  font-weight: 500;
}

.price {
  font-weight: 600;
  color: #27ae60;
}

.profit {
  font-weight: 600;
}

.profit-positive {
  color: #27ae60;
}

.profit-negative {
  color: #e74c3c;
}

.profit-zero {
  color: #95a5a6;
}

@media (max-width: 768px) {
  .card {
    padding: 1.5rem;
  }

  .sales-table thead {
    display: none;
  }

  .sales-table tbody tr {
    display: block;
    margin-bottom: 1rem;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
  }

  .sales-table tbody tr:nth-child(even) {
    background: #ffffff;
  }

  .sales-table td {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    border-bottom: 1px solid #ecf0f1;
  }

  .sales-table td:last-child {
    border-bottom: none;
  }

  .sales-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #34495e;
  }
}
</style>
