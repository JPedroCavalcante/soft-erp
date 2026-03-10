<template>
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
        required
        :disabled="loading"
        :class="{ 'input-error': errors.name }"
        autocomplete="off"
      />
      <span v-if="errors.name" class="field-error">{{ errors.name }}</span>
    </div>

    <div class="form-group">
      <label for="sale-price">
        Preço de Venda
        <span class="required">*</span>
      </label>
      <div class="input-currency">
        <span class="currency-symbol">R$</span>
        <input
          id="sale-price"
          v-model.number="formData.sale_price"
          type="number"
          step="0.01"
          min="0.01"
          placeholder="0,00"
          required
          :disabled="loading"
          :class="{ 'input-error': errors.sale_price }"
        />
      </div>
      <span v-if="errors.sale_price" class="field-error">{{ errors.sale_price }}</span>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { CreateProductDTO, Product } from '../types';

interface Props {
  loading?: boolean;
  initialData?: Product | null;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  initialData: null,
});

const emit = defineEmits<{
  submit: [data: CreateProductDTO];
}>();

const formData = ref<CreateProductDTO>({
  name: '',
  sale_price: 0,
});

const errors = ref<Record<string, string>>({});

watch(
  () => props.initialData,
  (data: Product | null) => {
    if (data) {
      formData.value = {
        name: data.name,
        sale_price: data.sale_price,
      };
    }
  },
  { immediate: true }
);

watch(
  () => formData.value.name,
  (newName: string) => {
    if (newName && newName.trim().length > 0 && newName.trim().length < 3) {
      errors.value.name = 'O nome deve ter no mínimo 3 caracteres';
    } else {
      delete errors.value.name;
    }
  }
);

watch(
  () => formData.value.sale_price,
  (newPrice: number) => {
    if (newPrice && newPrice <= 0) {
      errors.value.sale_price = 'O preço deve ser maior que zero';
    } else {
      delete errors.value.sale_price;
    }
  }
);

const validateForm = (): boolean => {
  errors.value = {};
  let isValid = true;

  if (!formData.value.name || formData.value.name.trim().length < 3) {
    errors.value.name = 'O nome deve ter no mínimo 3 caracteres';
    isValid = false;
  }

  if (!formData.value.sale_price || formData.value.sale_price <= 0) {
    errors.value.sale_price = 'O preço deve ser maior que zero';
    isValid = false;
  }

  return isValid;
};

const handleSubmit = () => {
  if (!validateForm()) return;

  emit('submit', {
    name: formData.value.name.trim(),
    sale_price: formData.value.sale_price,
  });
};

const resetForm = () => {
  formData.value = { name: '', sale_price: 0 };
  errors.value = {};
};

const submitForm = () => {
  handleSubmit();
};

defineExpose({ resetForm, submitForm });
</script>

<style scoped>
.product-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: var(--gray-700);
}

.required {
  color: var(--danger-600);
}

.form-group input {
  padding: 12px 14px;
  font-size: 15px;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  transition: all var(--transition-base);
  background: white;
  font-family: inherit;
}

.form-group input:hover:not(:disabled) {
  border-color: var(--gray-300);
}

.form-group input:focus {
  outline: none;
  border-color: var(--primary-600);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.form-group input:disabled {
  background: var(--gray-50);
  cursor: not-allowed;
  opacity: 0.7;
}

.form-group input.input-error {
  border-color: var(--danger-500);
}

.form-group input.input-error:focus {
  border-color: var(--danger-600);
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.input-currency {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 14px;
  font-size: 15px;
  font-weight: 600;
  color: var(--gray-600);
  pointer-events: none;
}

.input-currency input {
  padding-left: 48px;
  width: 100%;
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

@media (max-width: 480px) {
  .form-group input {
    font-size: 16px; /* Previne zoom no iOS */
  }
}
</style>
