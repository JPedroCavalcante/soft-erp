<template>
  <div class="sales-view">
    <div class="header">
      <h1>Gestão de Vendas</h1>
      <button @click="showCreateModal = true" class="btn btn-primary">
        <Icon name="plus" :size="20" />
        <span class="btn-text">Nova</span>
      </button>
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

    <div v-if="sales.length > 0" class="sales-grid">
      <div v-for="sale in sales" :key="sale.id" class="sale-card">
        <div class="card-header">
          <div class="sale-id">#{{ sale.id }}</div>
          <div class="sale-date">{{ formatDate(sale.created_at) }}</div>
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
import type { CreateSaleDTO } from '@/stores/sales';

const salesStore = useSalesStore();
const productsStore = useProductsStore();
const { showToast } = useToast();

const formRef = ref<InstanceType<typeof SaleForm> | null>(null);
const showCreateModal = ref(false);

const sales = computed(() => salesStore.sales);
const loading = computed(() => salesStore.loading);

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
    showToast('Venda registrada com sucesso!', 'success');
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response;
      if (response?.data?.errors) {
        const errors = response.data.errors;
        const errorMessage = Object.values(errors).flat().join(', ');
        showToast(errorMessage, 'error');
      } else {
        showToast(response?.data?.message || 'Erro ao registrar venda', 'error');
      }
    } else {
      showToast('Erro ao registrar venda', 'error');
    }
  }
};

const submitForm = () => {
  formRef.value?.submitForm();
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
  align-items: center;
  margin-bottom: 32px;
}

.header h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
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

.sale-card:hover {
  border-color: var(--primary-300);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: var(--gray-50);
  border-bottom: 1px solid var(--gray-200);
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
