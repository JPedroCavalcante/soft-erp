<template>
  <div class="products-container">
    <div class="products-header">
      <h1>Gestão de Produtos</h1>
    </div>

    <section class="form-section">
      <ProductForm
        ref="formRef"
        :loading="loading"
        @submit="handleCreateProduct"
      />
    </section>

    <section class="list-section">
      <ProductList
        :products="products"
        :loading="loading"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useProductsStore } from '@/stores/products';
import ProductForm from '../components/ProductForm.vue';
import ProductList from '../components/ProductList.vue';
import type { CreateProductDTO } from '../types';

const productsStore = useProductsStore();
const formRef = ref<InstanceType<typeof ProductForm> | null>(null);

const products = computed(() => productsStore.products);
const loading = computed(() => productsStore.loading);

const handleCreateProduct = async (data: CreateProductDTO) => {
  try {
    await productsStore.createProduct(data);
    formRef.value?.resetForm();
    formRef.value?.showSuccess('Produto criado com sucesso!');
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response;
      if (response?.data?.errors) {
        const errors = response.data.errors;
        const errorMessage = Object.values(errors).flat().join(', ');
        formRef.value?.showError(errorMessage);
      } else {
        formRef.value?.showError(response?.data?.message || 'Erro ao criar produto');
      }
    } else {
      formRef.value?.showError('Erro ao criar produto');
    }
  }
};

onMounted(() => {
  productsStore.fetchProducts();
});
</script>

<style scoped>
.products-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.products-header {
  margin-bottom: 2rem;
}

.products-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

@media (max-width: 768px) {
  .products-container {
    padding: 0.5rem;
  }

  .products-header h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .products-header h1 {
    font-size: 1.25rem;
  }
}
</style>
