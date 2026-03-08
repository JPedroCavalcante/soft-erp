<template>
  <div class="purchases-container">
    <div class="purchases-header">
      <h1>Gestão de Compras</h1>
    </div>

    <section class="form-section">
      <PurchaseForm
        ref="formRef"
        :loading="loading"
        @submit="handleCreatePurchase"
      />
    </section>

    <section class="list-section">
      <PurchaseList
        :purchases="purchases"
        :loading="loading"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { usePurchasesStore } from '@/stores/purchases';
import { useProductsStore } from '@/stores/products';
import PurchaseForm from '../components/PurchaseForm.vue';
import PurchaseList from '../components/PurchaseList.vue';
import type { CreatePurchaseDTO } from '@/stores/purchases';

const purchasesStore = usePurchasesStore();
const productsStore = useProductsStore();
const formRef = ref<InstanceType<typeof PurchaseForm> | null>(null);

const purchases = computed(() => purchasesStore.purchases);
const loading = computed(() => purchasesStore.loading);

const handleCreatePurchase = async (data: CreatePurchaseDTO) => {
  try {
    await purchasesStore.createPurchase(data);
    formRef.value?.resetForm();
    formRef.value?.showSuccess('Compra registrada com sucesso!');
  } catch (err: unknown) {
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response;
      if (response?.data?.errors) {
        const errors = response.data.errors;
        const errorMessage = Object.values(errors).flat().join(', ');
        formRef.value?.showError(errorMessage);
      } else {
        formRef.value?.showError(response?.data?.message || 'Erro ao registrar compra');
      }
    } else {
      formRef.value?.showError('Erro ao registrar compra');
    }
  }
};

onMounted(() => {
  productsStore.fetchProducts();
  purchasesStore.fetchPurchases();
});
</script>

<style scoped>
.purchases-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.purchases-header {
  margin-bottom: 2rem;
}

.purchases-header h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

@media (max-width: 768px) {
  .purchases-container {
    padding: 0.5rem;
  }

  .purchases-header h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .purchases-header h1 {
    font-size: 1.25rem;
  }
}
</style>
