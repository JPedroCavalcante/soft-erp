import { defineStore } from 'pinia';
import { productsService } from '@/services/api';
import { CACHE_TIME } from '@/config';
import type { Product, CreateProductDTO } from '@/modules/products/types';

interface ProductsState {
  products: Product[];
  loading: boolean;
  error: string | null;
  lastFetch: number | null;
}

export const useProductsStore = defineStore('products', {
  state: (): ProductsState => ({
    products: [],
    loading: false,
    error: null,
    lastFetch: null,
  }),

  getters: {
    productById: (state) => (id: number) => {
      return state.products.find(p => p.id === id);
    },

    inStockProducts: (state) => {
      return state.products.filter(p => p.stock > 0);
    },

    outOfStockProducts: (state) => {
      return state.products.filter(p => p.stock === 0);
    },

    lowStockProducts: (state) => {
      return state.products.filter(p => p.stock > 0 && p.stock < 10);
    },

    productsCount: (state) => state.products.length,
  },

  actions: {
    async fetchProducts(forceRefresh = false) {
      const now = Date.now();

      if (!forceRefresh && this.lastFetch && (now - this.lastFetch) < CACHE_TIME.MEDIUM) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await productsService.getAll();
        this.products = response.data.data;
        this.lastFetch = now;
      } catch (err: unknown) {
        this.error = err instanceof Error ? err.message : 'Erro ao buscar produtos';
        console.error('Error fetching products:', err);
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createProduct(data: CreateProductDTO) {
      this.loading = true;
      this.error = null;

      try {
        const response = await productsService.create(data);
        this.products.push(response.data.data);
        return response.data.data;
      } catch (err: unknown) {
        this.error = 'Erro ao criar produto';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateProduct(id: number, data: Partial<CreateProductDTO>) {
      this.loading = true;
      this.error = null;

      try {
        const response = await productsService.update(id, data);
        const index = this.products.findIndex(p => p.id === id);
        if (index !== -1) {
          this.products[index] = response.data.data;
        }
        return response.data.data;
      } catch (err: unknown) {
        this.error = 'Erro ao atualizar produto';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deleteProduct(id: number) {
      this.loading = true;
      this.error = null;

      try {
        await productsService.delete(id);
        this.products = this.products.filter(p => p.id !== id);
      } catch (err: unknown) {
        this.error = 'Erro ao deletar produto';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    updateProductStock(productId: number, newStock: number) {
      const product = this.products.find(p => p.id === productId);
      if (product) {
        product.stock = newStock;
      }
    },

    updateProductAverageCost(productId: number, newCost: string) {
      const product = this.products.find(p => p.id === productId);
      if (product) {
        product.average_cost = newCost;
      }
    },
  },
});
