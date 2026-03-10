<template>
  <div class="products-view">
    <!-- Header com botão de adicionar -->
    <div class="view-header">
      <div class="header-content">
        <h1 class="view-title">Produtos</h1>
        <p class="view-subtitle">{{ products.length }} {{ products.length === 1 ? 'produto cadastrado' : 'produtos cadastrados' }}</p>
      </div>
      <button class="btn-add" @click="openCreateModal">
        <Icon name="plus" :size="20" />
        <span class="btn-text">Novo</span>
      </button>
    </div>

    <!-- Lista de produtos -->
    <div v-if="loading && products.length === 0" class="loading-state">
      <div class="spinner-large"></div>
      <p>Carregando produtos...</p>
    </div>

    <div v-else-if="products.length === 0" class="empty-state">
      <Icon name="package" :size="64" color="var(--gray-400)" />
      <h3>Nenhum produto cadastrado</h3>
      <p>Comece adicionando seu primeiro produto</p>
      <button class="btn-primary" @click="openCreateModal">
        <Icon name="plus" :size="20" />
        Adicionar Produto
      </button>
    </div>

    <div v-else class="products-grid">
      <div v-for="product in products" :key="product.id" class="product-card">
        <div class="card-header">
          <h3 class="product-name">{{ product.name }}</h3>
          <div class="card-actions">
            <button
              class="btn-icon btn-edit"
              @click="openEditModal(product)"
              title="Editar"
            >
              <Icon name="edit" :size="18" />
            </button>
            <button
              class="btn-icon btn-delete"
              @click="openDeleteModal(product)"
              title="Excluir"
            >
              <Icon name="trash" :size="18" />
            </button>
          </div>
        </div>

        <div class="card-body">
          <div class="info-row">
            <span class="label">Preço de Venda</span>
            <span class="value price">{{ formatCurrency(product.sale_price) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Custo Médio</span>
            <span class="value">{{ formatCurrency(product.average_cost) }}</span>
          </div>
          <div class="info-row">
            <span class="label">Estoque</span>
            <span class="value">
              <span class="badge" :class="getStockBadgeClass(product.stock)">
                {{ product.stock }} {{ product.stock === 1 ? 'unidade' : 'unidades' }}
              </span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Criar -->
    <Modal
      v-model="showCreateModal"
      title="Novo Produto"
      size="md"
    >
      <ProductForm
        ref="createFormRef"
        :loading="loading"
        @submit="handleCreate"
      />
      <template #footer>
        <button class="btn-secondary" @click="showCreateModal = false">
          Cancelar
        </button>
        <button
          class="btn-primary"
          :disabled="loading"
          @click="submitCreateForm"
        >
          <span v-if="!loading">Criar Produto</span>
          <span v-else class="loading-text">
            <span class="spinner"></span>
            Criando...
          </span>
        </button>
      </template>
    </Modal>

    <!-- Modal de Editar -->
    <Modal
      v-model="showEditModal"
      title="Editar Produto"
      size="md"
    >
      <ProductForm
        v-if="editingProduct"
        ref="editFormRef"
        :loading="loading"
        :initial-data="editingProduct"
        @submit="handleUpdate"
      />
      <template #footer>
        <button class="btn-secondary" @click="showEditModal = false">
          Cancelar
        </button>
        <button
          class="btn-primary"
          :disabled="loading"
          @click="submitEditForm"
        >
          <span v-if="!loading">Salvar Alterações</span>
          <span v-else class="loading-text">
            <span class="spinner"></span>
            Salvando...
          </span>
        </button>
      </template>
    </Modal>

    <!-- Modal de Confirmação de Exclusão -->
    <Modal
      v-model="showDeleteModal"
      title="Confirmar Exclusão"
      size="sm"
    >
      <div class="delete-confirmation">
        <div class="warning-icon">
          <Icon name="exclamation-triangle" :size="48" color="var(--danger-600)" />
        </div>
        <p class="confirmation-text">
          Tem certeza que deseja excluir o produto <strong>{{ deletingProduct?.name }}</strong>?
        </p>
        <p class="warning-text">
          Esta ação não pode ser desfeita.
        </p>
      </div>
      <template #footer>
        <button class="btn-secondary" @click="showDeleteModal = false">
          Cancelar
        </button>
        <button
          class="btn-danger"
          :disabled="loading"
          @click="handleDelete"
        >
          <span v-if="!loading">
            <Icon name="trash" :size="18" />
            Excluir
          </span>
          <span v-else class="loading-text">
            <span class="spinner"></span>
            Excluindo...
          </span>
        </button>
      </template>
    </Modal>

    <!-- Floating Action Button (Mobile) -->
    <button class="fab" @click="openCreateModal" title="Adicionar Produto">
      <Icon name="plus" :size="24" />
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useProductsStore } from '@/stores/products';
import { useToast } from '@/composables/useToast';
import Modal from '@/core/components/Modal.vue';
import Icon from '@/core/components/Icon.vue';
import ProductForm from '../components/ProductForm.vue';
import type { Product, CreateProductDTO } from '../types';

const productsStore = useProductsStore();
const { showSuccess, showError } = useToast();

const products = computed(() => productsStore.products);
const loading = computed(() => productsStore.loading);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const createFormRef = ref<InstanceType<typeof ProductForm> | null>(null);
const editFormRef = ref<InstanceType<typeof ProductForm> | null>(null);

const editingProduct = ref<Product | null>(null);
const deletingProduct = ref<Product | null>(null);

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const getStockBadgeClass = (stock: number) => {
  if (stock === 0) return 'badge-danger';
  if (stock < 10) return 'badge-warning';
  return 'badge-success';
};

const openCreateModal = () => {
  showCreateModal.value = true;
};

const openEditModal = (product: Product) => {
  editingProduct.value = product;
  showEditModal.value = true;
};

const openDeleteModal = (product: Product) => {
  deletingProduct.value = product;
  showDeleteModal.value = true;
};

const submitCreateForm = () => {
  createFormRef.value?.submitForm();
};

const submitEditForm = () => {
  editFormRef.value?.submitForm();
};

const handleCreate = async (data: CreateProductDTO) => {
  try {
    await productsStore.createProduct(data);
    showSuccess('Produto criado com sucesso!');
    showCreateModal.value = false;
  } catch (err: unknown) {
    showError('Erro ao criar produto');
    console.error(err);
  }
};

const handleUpdate = async (data: CreateProductDTO) => {
  if (!editingProduct.value) return;

  try {
    await productsStore.updateProduct(editingProduct.value.id, data);
    showSuccess('Produto atualizado com sucesso!');
    showEditModal.value = false;
    editingProduct.value = null;
  } catch (err: unknown) {
    showError('Erro ao atualizar produto');
    console.error(err);
  }
};

const handleDelete = async () => {
  if (!deletingProduct.value) return;

  try {
    await productsStore.deleteProduct(deletingProduct.value.id);
    showSuccess('Produto excluído com sucesso!');
    showDeleteModal.value = false;
    deletingProduct.value = null;
  } catch (err: unknown) {
    showError('Erro ao excluir produto');
    console.error(err);
  }
};

onMounted(() => {
  productsStore.fetchProducts();
});
</script>

<style scoped>
.products-view {
  position: relative;
  min-height: calc(100vh - 120px);
}

.view-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.header-content {
  flex: 1;
  min-width: 200px;
}

.view-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 4px 0;
}

@media (min-width: 768px) {
  .view-title {
    font-size: 28px;
  }
}

.view-subtitle {
  font-size: 14px;
  color: var(--gray-600);
  margin: 0;
}

.btn-add {
  display: none;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-base);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

@media (min-width: 768px) {
  .btn-add {
    display: flex;
  }
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.btn-text {
  font-size: 15px;
}

/* States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  text-align: center;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
}

.spinner-large {
  width: 48px;
  height: 48px;
  border: 4px solid var(--gray-200);
  border-top-color: var(--primary-600);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}

.empty-state h3 {
  margin: 16px 0 8px 0;
  font-size: 20px;
  font-weight: 600;
  color: var(--gray-900);
}

.empty-state p {
  margin: 0 0 24px 0;
  font-size: 15px;
  color: var(--gray-600);
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

@media (min-width: 640px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.product-card {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: all var(--transition-base);
}

.product-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.card-header {
  padding: 16px;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-900);
  margin: 0;
  flex: 1;
  min-width: 0;
  word-break: break-word;
}

.card-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
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

.btn-edit {
  background: var(--primary-50);
  color: var(--primary-600);
}

.btn-edit:hover {
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
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.label {
  font-size: 13px;
  color: var(--gray-600);
  font-weight: 500;
}

.value {
  font-size: 14px;
  color: var(--gray-900);
  font-weight: 600;
}

.value.price {
  color: var(--primary-600);
  font-size: 16px;
}

.badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-success {
  background: var(--success-100);
  color: var(--success-700);
}

.badge-warning {
  background: var(--warning-100);
  color: var(--warning-700);
}

.badge-danger {
  background: var(--danger-100);
  color: var(--danger-700);
}

/* Modal Contents */
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

/* Buttons */
.btn-primary,
.btn-secondary,
.btn-danger {
  padding: 12px 24px;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all var(--transition-base);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.btn-secondary {
  background: var(--gray-100);
  color: var(--gray-700);
}

.btn-secondary:hover {
  background: var(--gray-200);
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

.btn-primary:disabled,
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

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* FAB (Mobile Only) */
.fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
  color: white;
  border: none;
  border-radius: 50%;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-base);
  z-index: 100;
}

@media (min-width: 768px) {
  .fab {
    display: none;
  }
}

.fab:hover {
  transform: scale(1.1);
  box-shadow: 0 12px 32px rgba(99, 102, 241, 0.5);
}

.fab:active {
  transform: scale(0.95);
}
</style>
