<template>
  <div class="sales-container">
    <div class="sales-header">
      <h1>Gestão de Vendas</h1>
    </div>

    <section class="form-section">
      <SaleForm
        ref="formRef"
        :loading="loading"
        @submit="handleCreateSale"
      />
    </section>

    <section class="list-section">
      <SaleList
        :sales="sales"
        :loading="loading"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useSalesStore } from '@/stores/sales';
import { useProductsStore } from '@/stores/products';
import SaleForm from '../components/SaleForm.vue';
import SaleList from '../components/SaleList.vue';
import type { CreateSaleDTO } from '@/stores/sales';

const salesStore = useSalesStore();
const productsStore = useProductsStore();
const formRef = ref<InstanceType<typeof SaleForm> | null>(null);

const sales = computed(() => salesStore.sales);
const loading = computed(() => salesStore.loading);

const handleCreateSale = async (data: CreateSaleDTO) => {
  try {
    await salesStore.createSale(data);
    formRef.value?.resetForm();
    formRef.value?.showSuccess('Venda registrada com sucesso!');
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response;
      if (response?.data?.errors) {
        const errors = response.data.errors;
        const errorMessage = Object.values(errors).flat().join(', ');
        formRef.value?.showError(errorMessage);
      } else {
        formRef.value?.showError(response?.data?.message || 'Erro ao registrar venda');
      }
    } else {
      formRef.value?.showError('Erro ao registrar venda');
    }
  }
};

onMounted(() => {
  productsStore.fetchProducts();
  salesStore.fetchSales();
});
</script>

<style scoped>
.sales-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.sales-header {
  margin-bottom: 2rem;
}

.sales-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

@media (max-width: 768px) {
  .sales-container {
    padding: 0.5rem;
  }

  .sales-header h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .sales-header h1 {
    font-size: 1.25rem;
  }
}
</style>
