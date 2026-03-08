<template>
  <div class="card">
    <h2>Lista de Compras</h2>

    <div v-if="loading && purchases.length === 0" class="loading-state">
      <div class="spinner-large"></div>
      <p>Carregando compras...</p>
    </div>

    <div v-else-if="!loading && purchases.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
      </svg>
      <p>Nenhuma compra registrada</p>
      <small>Registre sua primeira compra usando o formulário acima</small>
    </div>

    <div v-else class="table-wrapper">
      <table class="purchases-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fornecedor</th>
            <th>Total</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="purchase in purchases" :key="purchase.id">
            <td data-label="ID">{{ purchase.id }}</td>
            <td data-label="Fornecedor" class="supplier-name">{{ purchase.supplier }}</td>
            <td data-label="Total" class="price">{{ formatCurrency(purchase.total_amount) }}</td>
            <td data-label="Data">{{ formatDate(purchase.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Purchase } from '@/stores/purchases';

interface Props {
  purchases: Purchase[];
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

.purchases-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.purchases-table thead {
  background: #f8f9fa;
}

.purchases-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #34495e;
  border-bottom: 2px solid #ecf0f1;
  white-space: nowrap;
}

.purchases-table tbody tr {
  border-bottom: 1px solid #ecf0f1;
  transition: background-color 0.2s ease;
}

.purchases-table tbody tr:nth-child(even) {
  background: #f8f9fa;
}

.purchases-table tbody tr:hover {
  background: #e8f4f8;
}

.purchases-table td {
  padding: 1rem;
  color: #2c3e50;
}

.supplier-name {
  font-weight: 500;
}

.price {
  font-weight: 600;
  color: #27ae60;
}

@media (max-width: 768px) {
  .card {
    padding: 1.5rem;
  }

  .purchases-table thead {
    display: none;
  }

  .purchases-table tbody tr {
    display: block;
    margin-bottom: 1rem;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
  }

  .purchases-table tbody tr:nth-child(even) {
    background: #ffffff;
  }

  .purchases-table td {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    border-bottom: 1px solid #ecf0f1;
  }

  .purchases-table td:last-child {
    border-bottom: none;
  }

  .purchases-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #34495e;
  }
}
</style>
