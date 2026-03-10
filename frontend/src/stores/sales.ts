import { defineStore } from 'pinia';
import { SalesService } from '@/services/api';
import { CACHE_TIME } from '@/config';
import { useProductsStore } from './products';
import type { Sale, CreateSaleDTO } from '@/modules/sales/types';

interface SalesState {
  sales: Sale[];
  loading: boolean;
  error: string | null;
  lastFetch: number | null;
}

export const useSalesStore = defineStore('sales', {
  state: (): SalesState => ({
    sales: [],
    loading: false,
    error: null,
    lastFetch: null,
  }),

  getters: {
    saleById: (state) => (id: number) => {
      return state.sales.find(s => s.id === id);
    },

    salesByCustomer: (state) => (customer: string) => {
      return state.sales.filter(s => s.customer.toLowerCase().includes(customer.toLowerCase()));
    },

    salesCount: (state) => state.sales.length,

    totalSalesAmount: (state) => {
      return state.sales.reduce((sum, sale) => {
        return sum + parseFloat(sale.total_amount);
      }, 0);
    },

    totalProfit: (state) => {
      return state.sales.reduce((sum, sale) => {
        return sum + parseFloat(sale.total_profit);
      }, 0);
    },
  },

  actions: {
    async fetchSales(forceRefresh = false) {
      const now = Date.now();

      if (!forceRefresh && this.lastFetch && (now - this.lastFetch) < CACHE_TIME.MEDIUM) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await SalesService.getAll();
        this.sales = response.data;
        this.lastFetch = now;
      } catch (err: unknown) {
        this.error = err instanceof Error ? err.message : 'Erro ao buscar vendas';
        console.error('Error fetching sales:', err);
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createSale(data: CreateSaleDTO) {
      this.loading = true;
      this.error = null;

      try {
        const response = await SalesService.create(data);
        this.sales.unshift(response.data);

        const productsStore = useProductsStore();
        await productsStore.fetchProducts(true);

        return response.data;
      } catch (err: unknown) {
        this.error = 'Erro ao criar venda';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async cancelSale(id: number) {
      this.loading = true;
      this.error = null;

      try {
        const response = await SalesService.cancel(id);
        const canceledSale = response.data;

        const index = this.sales.findIndex(s => s.id === id);
        if (index !== -1) {
          this.sales[index] = canceledSale;
        }

        const productsStore = useProductsStore();
        await productsStore.fetchProducts(true);

        return canceledSale;
      } catch (err: unknown) {
        this.error = 'Erro ao cancelar venda';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deleteSale(id: number) {
      this.loading = true;
      this.error = null;

      try {
        await SalesService.delete(id);
        this.sales = this.sales.filter(s => s.id !== id);
      } catch (err: unknown) {
        this.error = 'Erro ao deletar venda';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
