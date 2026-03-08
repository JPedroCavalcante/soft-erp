<template>
  <div class="card">
    <h2>Registrar Compra</h2>

    <form @submit.prevent="handleSubmit" class="purchase-form">
      <div class="form-group">
        <label for="supplier">
          Fornecedor
          <span class="required">*</span>
        </label>
        <input
          id="supplier"
          v-model="formData.supplier"
          type="text"
          placeholder="Nome do fornecedor"
          :disabled="loading"
        />
      </div>

      <div class="items-section">
        <div class="items-header">
          <h3>Itens da Compra</h3>
          <button type="button" @click="addItem" class="btn btn-secondary" :disabled="loading">
            + Adicionar Item
          </button>
        </div>

        <div v-for="(item, index) in formData.items" :key="index" class="item-row">
          <div class="form-group">
            <label>Produto *</label>
            <select v-model="item.product_id" :disabled="loading" required>
              <option value="">Selecione um produto</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
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
              required
            />
          </div>

          <div class="form-group">
            <label>Preço Unitário *</label>
            <input
              v-model.number="item.unit_price"
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

      <button type="submit" class="btn btn-primary" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Registrando...' : 'Registrar Compra' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useProductsStore } from '@/stores/products';
import type { CreatePurchaseDTO } from '@/stores/purchases';

interface Props {
  loading?: boolean;
}

interface Emits {
  (e: 'submit', data: CreatePurchaseDTO): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<Emits>();
const productsStore = useProductsStore();
const products = computed(() => productsStore.products);

const formData = ref<CreatePurchaseDTO>({
  supplier: '',
  items: [
    { product_id: 0, quantity: 1, unit_price: 0 }
  ],
});

const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const addItem = () => {
  formData.value.items.push({ product_id: 0, quantity: 1, unit_price: 0 });
};

const removeItem = (index: number) => {
  if (formData.value.items.length > 1) {
    formData.value.items.splice(index, 1);
  }
};

const calculateTotal = () => {
  return formData.value.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_price);
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

  if (!formData.value.supplier || formData.value.supplier.length < 3) {
    error.value = 'O nome do fornecedor deve ter no mínimo 3 caracteres';
    return;
  }

  if (formData.value.items.length === 0) {
    error.value = 'Adicione pelo menos um item';
    return;
  }

  const invalidItem = formData.value.items.find(
    item => !item.product_id || item.quantity <= 0 || item.unit_price <= 0
  );

  if (invalidItem) {
    error.value = 'Preencha todos os campos dos itens corretamente';
    return;
  }

  emit('submit', {
    supplier: formData.value.supplier,
    items: formData.value.items,
  });
};

const resetForm = () => {
  formData.value = {
    supplier: '',
    items: [{ product_id: 0, quantity: 1, unit_price: 0 }],
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

defineExpose({ resetForm, showSuccess, showError });
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

.purchase-form {
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
