import { defineStore } from 'pinia';
import { purchasesService } from '@/services/api';
import { CACHE_TIME } from '@/config';
import { useProductsStore } from './products';

export interface PurchaseItem {
  id?: number;
  product_id: number;
  product?: string;
  quantity: number;
  unit_price: string | number;
}

export interface Purchase {
  id: number;
  supplier: string;
  total_amount: string;
  items?: PurchaseItem[];
  created_at: string;
  updated_at: string;
}

export interface CreatePurchaseDTO {
  supplier: string;
  items: Array<{
    product_id: number;
    quantity: number;
    unit_price: number;
  }>;
}

interface PurchasesState {
  purchases: Purchase[];
  loading: boolean;
  error: string | null;
  lastFetch: number | null;
}

export const usePurchasesStore = defineStore('purchases', {
  state: (): PurchasesState => ({
    purchases: [],
    loading: false,
    error: null,
    lastFetch: null,
  }),

  getters: {
    purchaseById: (state) => (id: number) => {
      return state.purchases.find(p => p.id === id);
    },

    purchasesBySupplier: (state) => (supplier: string) => {
      return state.purchases.filter(p => p.supplier.toLowerCase().includes(supplier.toLowerCase()));
    },

    purchasesCount: (state) => state.purchases.length,

    totalPurchaseAmount: (state) => {
      return state.purchases.reduce((sum, purchase) => {
        return sum + parseFloat(purchase.total_amount);
      }, 0);
    },
  },

  actions: {
    async fetchPurchases(forceRefresh = false) {
      const now = Date.now();

      if (!forceRefresh && this.lastFetch && (now - this.lastFetch) < CACHE_TIME.MEDIUM) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await purchasesService.getAll();
        this.purchases = response.data.data;
        this.lastFetch = now;
      } catch (err: unknown) {
        this.error = err instanceof Error ? err.message : 'Erro ao buscar compras';
        console.error('Error fetching purchases:', err);
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createPurchase(data: CreatePurchaseDTO) {
      this.loading = true;
      this.error = null;

      try {
        const response = await purchasesService.create(data);
        this.purchases.unshift(response.data.data);

        const productsStore = useProductsStore();
        await productsStore.fetchProducts(true);

        return response.data.data;
      } catch (err: unknown) {
        this.error = 'Erro ao criar compra';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deletePurchase(id: number) {
      this.loading = true;
      this.error = null;

      try {
        await purchasesService.delete(id);
        this.purchases = this.purchases.filter(p => p.id !== id);
      } catch (err: unknown) {
        this.error = 'Erro ao deletar compra';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
