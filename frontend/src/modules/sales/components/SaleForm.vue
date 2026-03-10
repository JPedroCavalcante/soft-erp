<template>
  <div class="sale-form-wrapper">
    <form @submit.prevent="handleSubmit" class="sale-form">
      <div class="form-group">
        <label for="customer">
          Cliente
          <span class="required">*</span>
        </label>
        <input
          id="customer"
          v-model="formData.customer"
          type="text"
          placeholder="Nome do cliente"
          :disabled="loading"
          :class="{ 'input-error': errors.customer }"
        />
        <span v-if="errors.customer" class="field-error">{{ errors.customer }}</span>
      </div>

      <div class="items-section">
        <div class="items-header">
          <h3>Itens da Venda</h3>
          <button type="button" @click="addItem" class="btn btn-secondary" :disabled="loading">
            + Adicionar Item
          </button>
        </div>

        <div v-for="(item, index) in formData.items" :key="index" class="item-row">
          <div class="form-group">
            <label>Produto *</label>
            <select v-model="item.product_id" :disabled="loading" required @change="updateItemPrice(index)">
              <option value="">Selecione um produto</option>
              <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                {{ product.name }} (Estoque: {{ product.stock }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Quantidade *</label>
            <input
              v-model.number="item.quantity"
              type="number"
              min="1"
              step="1"
              :disabled="loading"
              :max="getProductStock(item.product_id)"
              required
            />
          </div>

          <div class="form-group">
            <label>Preço de Venda *</label>
            <input
              v-model.number="item.unit_sale_price"
              type="number"
              min="0.01"
              step="0.01"
              :disabled="loading"
              required
            />
          </div>

          <button
            type="button"
            @click="removeItem(index)"
            class="btn btn-danger"
            :disabled="loading || formData.items.length === 1"
          >
            ✕
          </button>
        </div>
      </div>

      <div class="total-section">
        <strong>Total: {{ formatCurrency(calculateTotal()) }}</strong>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useProductsStore } from '@/stores/products';
import type { CreateSaleDTO } from '@/stores/sales';

interface Props {
  loading?: boolean;
}

interface Emits {
  (e: 'submit', data: CreateSaleDTO): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<Emits>();
const productsStore = useProductsStore();

const availableProducts = computed(() => productsStore.inStockProducts);

const formData = ref<CreateSaleDTO>({
  customer: '',
  items: [
    { product_id: 0, quantity: 1, unit_sale_price: 0 }
  ],
});

const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);
const errors = ref<Record<string, string>>({});

watch(
  () => formData.value.customer,
  (newCustomer: string) => {
    if (newCustomer && newCustomer.trim().length > 0 && newCustomer.trim().length < 3) {
      errors.value.customer = 'O cliente deve ter no mínimo 3 caracteres';
    } else {
      delete errors.value.customer;
    }
  }
);

const addItem = () => {
  formData.value.items.push({ product_id: 0, quantity: 1, unit_sale_price: 0 });
};

const removeItem = (index: number) => {
  if (formData.value.items.length > 1) {
    formData.value.items.splice(index, 1);
  }
};

const getProductStock = (productId: number): number => {
  const product = productsStore.products.find(p => p.id === productId);
  return product?.stock || 0;
};

const updateItemPrice = (index: number) => {
  const item = formData.value.items[index];
  const product = productsStore.products.find(p => p.id === item.product_id);
  if (product) {
    item.unit_sale_price = parseFloat(product.sale_price);
  }
};

const calculateTotal = () => {
  return formData.value.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_sale_price);
  }, 0);
};

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const handleSubmit = () => {
  error.value = null;
  successMessage.value = null;

  if (!formData.value.customer || formData.value.customer.length < 3) {
    error.value = 'O nome do cliente deve ter no mínimo 3 caracteres';
    return;
  }

  if (formData.value.items.length === 0) {
    error.value = 'Adicione pelo menos um item';
    return;
  }

  const invalidItem = formData.value.items.find(
    item => !item.product_id || item.quantity <= 0 || item.unit_sale_price <= 0
  );

  if (invalidItem) {
    error.value = 'Preencha todos os campos dos itens corretamente';
    return;
  }

  const stockError = formData.value.items.find(item => {
    const stock = getProductStock(item.product_id);
    return item.quantity > stock;
  });

  if (stockError) {
    error.value = 'Quantidade superior ao estoque disponível';
    return;
  }

  emit('submit', {
    customer: formData.value.customer,
    items: formData.value.items,
  });
};

const resetForm = () => {
  formData.value = {
    customer: '',
    items: [{ product_id: 0, quantity: 1, unit_sale_price: 0 }],
  };
  error.value = null;
};

const showSuccess = (message: string) => {
  successMessage.value = message;
  setTimeout(() => {
    successMessage.value = null;
  }, 3000);
};

const showError = (message: string) => {
  error.value = message;
};

const submitForm = () => {
  handleSubmit();
};

defineExpose({ resetForm, showSuccess, showError, submitForm });
</script>

<style scoped>
.sale-form-wrapper {
  padding: 0;
}

.sale-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #34495e;
}

.required {
  color: #e74c3c;
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  font-size: 1rem;
  border: 2px solid #ecf0f1;
  border-radius: 6px;
  transition: all 0.2s ease;
  background: #ffffff;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group input:disabled,
.form-group select:disabled {
  background: #f8f9fa;
  cursor: not-allowed;
  opacity: 0.6;
}

.form-group input.input-error,
.form-group select.input-error {
  border-color: var(--danger-500);
}

.form-group input.input-error:focus,
.form-group select.input-error:focus {
  border-color: var(--danger-600);
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.field-error {
  font-size: 13px;
  color: var(--danger-600);
  animation: slideIn 0.2s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.items-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 6px;
}

.items-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.items-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.item-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 1rem;
  align-items: end;
  margin-bottom: 1rem;
  padding: 1rem;
  background: #ffffff;
  border-radius: 6px;
}

.total-section {
  text-align: right;
  font-size: 1.25rem;
  color: #27ae60;
}

.alert {
  padding: 1rem;
  border-radius: 6px;
  font-size: 0.875rem;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.alert-error {
  background: #fee;
  color: #c33;
  border: 1px solid #fcc;
}

.alert-success {
  background: #efe;
  color: #2c7a2c;
  border: 1px solid #cfc;
}

.btn {
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #3498db;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-secondary {
  background: #95a5a6;
  color: #ffffff;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.btn-secondary:hover:not(:disabled) {
  background: #7f8c8d;
}

.btn-danger {
  background: #e74c3c;
  color: #ffffff;
  padding: 0.5rem 0.75rem;
  font-size: 1.125rem;
}

.btn-danger:hover:not(:disabled) {
  background: #c0392b;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .card {
    padding: 1.5rem;
  }

  .item-row {
    grid-template-columns: 1fr;
  }

  .btn {
    width: 100%;
  }
}
</style>
