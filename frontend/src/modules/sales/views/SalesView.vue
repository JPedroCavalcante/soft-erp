<template>
  <div class="sales-view">
    <div class="header">
      <div class="header-content">
        <h1>Gestão de Vendas</h1>
        <p class="subtitle">{{ filteredSales.length }} {{ filteredSales.length === 1 ? 'venda' : 'vendas' }}</p>
      </div>
      <button @click="showCreateModal = true" class="btn btn-primary">
        <Icon name="plus" :size="20" />
        <span class="btn-text">Nova</span>
      </button>
    </div>

    <div v-if="sales.length > 0" class="filters">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Buscar por cliente..."
        class="search-input"
      />
      <select v-model="profitFilter" class="filter-select">
        <option value="all">Todas</option>
        <option value="profit">Com lucro</option>
        <option value="loss">Com prejuízo</option>
      </select>
      <select v-model="dateFilter" class="filter-select">
        <option value="all">Todas</option>
        <option value="today">Hoje</option>
        <option value="week">Última semana</option>
        <option value="month">Último mês</option>
      </select>
    </div>

    <div v-if="sales.length === 0 && !loading" class="empty-state">
      <Icon name="currency-dollar" :size="64" color="var(--gray-400)" />
      <h3>Nenhuma venda registrada</h3>
      <p>Comece registrando sua primeira venda</p>
      <button @click="showCreateModal = true" class="btn btn-primary">
        <Icon name="plus" :size="20" />
        Registrar Venda
      </button>
    </div>

    <div v-if="loading && sales.length === 0" class="loading-state">
      <div class="spinner"></div>
      <p>Carregando vendas...</p>
    </div>

    <div v-if="filteredSales.length > 0" class="sales-grid">
      <div v-for="sale in filteredSales" :key="sale.id" class="sale-card" :class="{ 'canceled': sale.is_canceled }">
        <div class="card-header">
          <div class="header-info">
            <div class="sale-id">#{{ sale.id }}</div>
            <div class="sale-date">{{ formatDate(sale.created_at) }}</div>
            <span v-if="sale.is_canceled" class="canceled-badge">CANCELADA</span>
          </div>
          <div v-if="!sale.is_canceled" class="card-actions">
            <button
              class="btn-icon btn-cancel"
              @click="openCancelModal(sale)"
              title="Cancelar venda"
            >
              <Icon name="x" :size="18" />
            </button>
            <button
              class="btn-icon btn-delete"
              @click="openDeleteModal(sale)"
              title="Excluir"
            >
              <Icon name="trash" :size="18" />
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="info-row">
            <span class="label">Cliente:</span>
            <span class="value customer">{{ sale.customer }}</span>
          </div>
          <div class="info-row">
            <span class="label">Total:</span>
            <span class="value total">{{ formatCurrency(sale.total_amount) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Lucro:</span>
            <span class="value" :class="getProfitClass(sale.total_profit)">
              {{ formatCurrency(sale.total_profit) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <Modal v-model="showCreateModal" title="Registrar Nova Venda" size="lg">
      <SaleForm
        ref="formRef"
        :loading="loading"
        @submit="handleCreateSale"
      />
      <template #footer>
        <button @click="showCreateModal = false" class="btn btn-secondary">
          Cancelar
        </button>
        <button @click="submitForm" class="btn btn-primary" :disabled="loading">
          <span v-if="loading" class="spinner-small"></span>
          {{ loading ? 'Registrando...' : 'Registrar' }}
        </button>
      </template>
    </Modal>

    <Modal v-model="showCancelModal" title="Cancelar Venda" size="sm">
      <div class="delete-confirmation">
        <div class="warning-icon">
          <Icon name="exclamation-triangle" :size="48" color="var(--primary-600)" />
        </div>
        <p class="confirmation-text">
          Tem certeza que deseja cancelar a venda <strong>#{{ cancelingSale?.id }}</strong>?
        </p>
        <p class="warning-text">
          O estoque dos produtos será revertido.
        </p>
      </div>
      <template #footer>
        <button class="btn btn-secondary" @click="showCancelModal = false">
          Voltar
        </button>
        <button class="btn btn-primary" :disabled="loading" @click="handleCancel">
          <span v-if="!loading">
            <Icon name="x" :size="18" />
            Cancelar Venda
          </span>
          <span v-else class="loading-text">
            <span class="spinner-small"></span>
            Cancelando...
          </span>
        </button>
      </template>
    </Modal>

    <Modal v-model="showDeleteModal" title="Confirmar Exclusão" size="sm">
      <div class="delete-confirmation">
        <div class="warning-icon">
          <Icon name="exclamation-triangle" :size="48" color="var(--danger-600)" />
        </div>
        <p class="confirmation-text">
          Tem certeza que deseja excluir a venda <strong>#{{ deletingSale?.id }}</strong>?
        </p>
        <p class="warning-text">
          Esta ação reverterá o estoque. Não é possível desfazer.
        </p>
      </div>
      <template #footer>
        <button class="btn btn-secondary" @click="showDeleteModal = false">
          Cancelar
        </button>
        <button class="btn btn-danger" :disabled="loading" @click="handleDelete">
          <span v-if="!loading">
            <Icon name="trash" :size="18" />
            Excluir
          </span>
          <span v-else class="loading-text">
            <span class="spinner-small"></span>
            Excluindo...
          </span>
        </button>
      </template>
    </Modal>

    <button @click="showCreateModal = true" class="fab">
      <Icon name="plus" :size="24" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useSalesStore } from '@/stores/sales';
import { useProductsStore } from '@/stores/products';
import { useToast } from '@/composables/useToast';
import Icon from '@/core/components/Icon.vue';
import Modal from '@/core/components/Modal.vue';
import SaleForm from '../components/SaleForm.vue';
import type { CreateSaleDTO, Sale } from '@/stores/sales';

const salesStore = useSalesStore();
const productsStore = useProductsStore();
const toast = useToast();

const formRef = ref<InstanceType<typeof SaleForm> | null>(null);
const showCreateModal = ref(false);
const showCancelModal = ref(false);
const showDeleteModal = ref(false);
const cancelingSale = ref<Sale | null>(null);
const deletingSale = ref<Sale | null>(null);
const searchQuery = ref('');
const profitFilter = ref('all');
const dateFilter = ref('all');

const sales = computed(() => salesStore.sales);
const loading = computed(() => salesStore.loading);

const filteredSales = computed(() => {
  let result = sales.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((s: any) => s.customer.toLowerCase().includes(query));
  }

  if (profitFilter.value !== 'all') {
    result = result.filter((s: any) => {
      const profit = typeof s.total_profit === 'string' ? parseFloat(s.total_profit) : s.total_profit;
      if (profitFilter.value === 'profit') {
        return profit >= 0;
      } else if (profitFilter.value === 'loss') {
        return profit < 0;
      }
      return true;
    });
  }

  if (dateFilter.value !== 'all') {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    result = result.filter((s: any) => {
      const saleDate = new Date(s.created_at);

      if (dateFilter.value === 'today') {
        return saleDate >= today;
      } else if (dateFilter.value === 'week') {
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 7);
        return saleDate >= weekAgo;
      } else if (dateFilter.value === 'month') {
        const monthAgo = new Date(today);
        monthAgo.setMonth(monthAgo.getMonth() - 1);
        return saleDate >= monthAgo;
      }
      return true;
    });
  }

  return result;
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

const getProfitClass = (profit: string | number): string => {
  const numValue = typeof profit === 'string' ? parseFloat(profit) : profit;
  return numValue >= 0 ? 'profit-positive' : 'profit-negative';
};

const handleCreateSale = async (data: CreateSaleDTO) => {
  try {
    await salesStore.createSale(data);
    showCreateModal.value = false;
    formRef.value?.resetForm();
    toast.success('Venda registrada com sucesso!');
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response;
      if (response?.data?.errors) {
        const errors = response.data.errors;
        const errorMessage = Object.values(errors).flat().join(', ');
        toast.error(errorMessage);
      } else {
        toast.error(response?.data?.message || 'Erro ao registrar venda');
      }
    } else {
      toast.error('Erro ao registrar venda');
    }
  }
};

const submitForm = () => {
  formRef.value?.submitForm();
};

const openCancelModal = (sale: Sale) => {
  cancelingSale.value = sale;
  showCancelModal.value = true;
};

const handleCancel = async () => {
  if (!cancelingSale.value) return;

  try {
    await salesStore.cancelSale(cancelingSale.value.id);
    toast.success('Venda cancelada com sucesso! Estoque revertido.');
    showCancelModal.value = false;
    cancelingSale.value = null;
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { message?: string } } }).response;
      toast.error(response?.data?.message || 'Erro ao cancelar venda');
    } else {
      toast.error('Erro ao cancelar venda');
    }
    console.error(err);
  }
};

const openDeleteModal = (sale: Sale) => {
  deletingSale.value = sale;
  showDeleteModal.value = true;
};

const handleDelete = async () => {
  if (!deletingSale.value) return;

  try {
    await salesStore.deleteSale(deletingSale.value.id);
    toast.success('Venda excluída com sucesso!');
    showDeleteModal.value = false;
    deletingSale.value = null;
    productsStore.fetchProducts(true);
  } catch (err: unknown) {
    toast.error('Erro ao excluir venda');
    console.error(err);
  }
};

onMounted(() => {
  productsStore.fetchProducts();
  salesStore.fetchSales();
});
</script>

<style scoped>
.sales-view {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.header-content {
  flex: 1;
  min-width: 200px;
}

.header h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 4px 0;
}

.subtitle {
  font-size: 14px;
  color: var(--gray-600);
  margin: 0;
}

.filters {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 12px 16px;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  font-size: 14px;
  transition: all var(--transition-base);
  background: white;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: var(--primary-500);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-select {
  min-width: 140px;
  cursor: pointer;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: 15px;
  font-weight: 600;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: all var(--transition-base);
}

.btn-primary {
  background: var(--primary-600);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-700);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-secondary {
  background: var(--gray-200);
  color: var(--gray-700);
}

.btn-secondary:hover {
  background: var(--gray-300);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  color: var(--gray-700);
  margin: 24px 0 8px;
}

.empty-state p {
  color: var(--gray-500);
  margin-bottom: 24px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--gray-200);
  border-top-color: var(--primary-600);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.sales-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 20px;
}

.sale-card {
  background: white;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  overflow: hidden;
  transition: all var(--transition-base);
}

.sale-card.canceled {
  opacity: 0.7;
  border-color: var(--gray-300);
  background: var(--gray-50);
}

.sale-card:hover {
  border-color: var(--primary-300);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.sale-card.canceled:hover {
  transform: none;
  box-shadow: none;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: var(--gray-50);
  border-bottom: 1px solid var(--gray-200);
}

.header-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.sale-id {
  font-weight: 700;
  color: var(--primary-600);
  font-size: 16px;
}

.sale-date {
  font-size: 13px;
  color: var(--gray-600);
}

.canceled-badge {
  display: inline-block;
  padding: 4px 8px;
  background: var(--danger-100);
  color: var(--danger-700);
  font-size: 11px;
  font-weight: 700;
  border-radius: 4px;
  margin-top: 4px;
  width: fit-content;
}

.card-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-base);
}

.btn-cancel {
  background: var(--primary-50);
  color: var(--primary-600);
}

.btn-cancel:hover {
  background: var(--primary-100);
  color: var(--primary-700);
}

.btn-delete {
  background: var(--danger-50);
  color: var(--danger-600);
}

.btn-delete:hover {
  background: var(--danger-100);
  color: var(--danger-700);
}

.card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.label {
  font-size: 14px;
  color: var(--gray-600);
}

.value {
  font-size: 15px;
  font-weight: 600;
  color: var(--gray-900);
}

.customer {
  color: var(--primary-600);
}

.total {
  color: var(--success-600);
  font-size: 18px;
}

.profit-positive {
  color: var(--success-600);
  font-weight: 700;
}

.profit-negative {
  color: var(--danger-600);
  font-weight: 700;
}

.delete-confirmation {
  text-align: center;
  padding: 20px;
}

.warning-icon {
  margin-bottom: 20px;
}

.confirmation-text {
  font-size: 15px;
  color: var(--gray-900);
  margin: 0 0 12px 0;
}

.warning-text {
  font-size: 13px;
  color: var(--gray-600);
  margin: 0;
}

.btn-danger {
  background: linear-gradient(135deg, var(--danger-600) 0%, var(--danger-700) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-danger:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.btn-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.loading-text {
  display: flex;
  align-items: center;
  gap: 8px;
}

.fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--primary-600);
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
  cursor: pointer;
  display: none;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-base);
  z-index: 100;
}

.fab:hover {
  background: var(--primary-700);
  transform: scale(1.1);
}

@media (max-width: 768px) {
  .sales-view {
    padding: 16px;
  }

  .header h1 {
    font-size: 24px;
  }

  .btn-text {
    display: none;
  }

  .sales-grid {
    grid-template-columns: 1fr;
  }

  .fab {
    display: flex;
  }
}

@media (max-width: 480px) {
  .header h1 {
    font-size: 20px;
  }

  .empty-state {
    padding: 40px 20px;
  }
}
</style>
