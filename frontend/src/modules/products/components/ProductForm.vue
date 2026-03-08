<template>
  <div class="card">
    <h2>Cadastrar Produto</h2>

    <form @submit.prevent="handleSubmit" class="product-form">
      <div class="form-group">
        <label for="product-name">
          Nome do Produto
          <span class="required">*</span>
        </label>
        <input
          id="product-name"
          v-model="formData.name"
          type="text"
          placeholder="Digite o nome do produto"
          aria-required="true"
          :disabled="loading"
        />
      </div>

      <div class="form-group">
        <label for="sale-price">
          Preço de Venda
          <span class="required">*</span>
        </label>
        <input
          id="sale-price"
          v-model.number="formData.sale_price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
          aria-required="true"
          :disabled="loading"
        />
      </div>

      <div v-if="error" class="alert alert-error" role="alert">
        {{ error }}
      </div>

      <div v-if="successMessage" class="alert alert-success" role="alert">
        {{ successMessage }}
      </div>

      <button
        type="submit"
        class="btn btn-primary"
        :disabled="loading"
        :aria-busy="loading"
      >
        <span v-if="loading" class="spinner"></span>
        {{ loading ? 'Cadastrando...' : 'Cadastrar Produto' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { CreateProductDTO } from '../types';

interface Props {
  loading?: boolean;
}

interface Emits {
  (e: 'submit', data: CreateProductDTO): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<Emits>();

const formData = ref({
  name: '',
  sale_price: 0,
});

const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const handleSubmit = () => {
  error.value = null;
  successMessage.value = null;

  if (!formData.value.name || formData.value.name.length < 3) {
    error.value = 'O nome deve ter no mínimo 3 caracteres';
    return;
  }

  if (!formData.value.sale_price || formData.value.sale_price <= 0) {
    error.value = 'O preço de venda deve ser maior que zero';
    return;
  }

  emit('submit', {
    name: formData.value.name,
    sale_price: formData.value.sale_price,
  });
};

const resetForm = () => {
  formData.value = { name: '', sale_price: 0 };
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

.product-form {
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

.form-group input {
  padding: 0.75rem;
  font-size: 1rem;
  border: 2px solid #ecf0f1;
  border-radius: 6px;
  transition: all 0.2s ease;
  background: #ffffff;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-group input:disabled {
  background: #f8f9fa;
  cursor: not-allowed;
  opacity: 0.6;
}

.form-group input::placeholder {
  color: #95a5a6;
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

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
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
    border-radius: 6px;
  }

  .card h2 {
    font-size: 1.25rem;
  }
}

@media (max-width: 480px) {
  .card {
    padding: 1rem;
  }

  .btn {
    width: 100%;
  }
}
</style>
