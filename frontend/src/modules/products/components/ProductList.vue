<template>
  <div class="card">
    <h2>Lista de Produtos</h2>

    <div v-if="loading && products.length === 0" class="loading-state">
      <div class="spinner-large"></div>
      <p>Carregando produtos...</p>
    </div>

    <div v-else-if="!loading && products.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <p>Nenhum produto cadastrado</p>
      <small>Cadastre seu primeiro produto usando o formulário acima</small>
    </div>

    <div v-else class="table-wrapper">
      <table class="products-table" role="table">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Nome</th>
            <th scope="col">Preço de Venda</th>
            <th scope="col">Estoque</th>
            <th scope="col">Custo Médio</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td data-label="ID">{{ product.id }}</td>
            <td data-label="Nome" class="product-name">{{ product.name }}</td>
            <td data-label="Preço de Venda" class="price">
              {{ formatCurrency(product.sale_price) }}
            </td>
            <td data-label="Estoque" class="stock">
              <span :class="['stock-badge', getStockClass(product.stock)]">
                {{ product.stock }}
              </span>
            </td>
            <td data-label="Custo Médio" class="price">
              {{ formatCurrency(product.average_cost) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '../types';
import { formatCurrency, getStockClass } from '../utils/format';

interface Props {
  products: Product[];
  loading?: boolean;
}

withDefaults(defineProps<Props>(), {
  loading: false,
});
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

.products-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.products-table thead {
  background: #f8f9fa;
}

.products-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #34495e;
  border-bottom: 2px solid #ecf0f1;
  white-space: nowrap;
}

.products-table tbody tr {
  border-bottom: 1px solid #ecf0f1;
  transition: background-color 0.2s ease;
}

.products-table tbody tr:nth-child(even) {
  background: #f8f9fa;
}

.products-table tbody tr:hover {
  background: #e8f4f8;
}

.products-table td {
  padding: 1rem;
  color: #2c3e50;
}

.product-name {
  font-weight: 500;
}

.price {
  font-weight: 600;
  color: #27ae60;
}

.stock-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-align: center;
  min-width: 40px;
}

.stock-ok {
  background: #d4edda;
  color: #155724;
}

.stock-low {
  background: #fff3cd;
  color: #856404;
}

.stock-empty {
  background: #f8d7da;
  color: #721c24;
}

@media (max-width: 768px) {
  .card {
    padding: 1.5rem;
    border-radius: 6px;
  }

  .card h2 {
    font-size: 1.25rem;
  }

  .products-table {
    font-size: 0.8125rem;
  }

  .products-table thead {
    display: none;
  }

  .products-table tbody tr {
    display: block;
    margin-bottom: 1rem;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
    overflow: hidden;
  }

  .products-table tbody tr:nth-child(even) {
    background: #ffffff;
  }

  .products-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #ecf0f1;
  }

  .products-table td:last-child {
    border-bottom: none;
  }

  .products-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #34495e;
  }

  .product-name,
  .price,
  .stock {
    justify-content: space-between;
  }
}

@media (max-width: 480px) {
  .card {
    padding: 1rem;
  }
}
</style>
