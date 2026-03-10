import { defineStore } from 'pinia';
import { salesService } from '@/services/api';
import { CACHE_TIME } from '@/config';
import { useProductsStore } from './products';

export interface SaleItem {
  id?: number;
  product_id: number;
  product?: string;
  quantity: number;
  unit_sale_price: string | number;
  historical_average_cost?: string;
  profit?: string;
}

export interface Sale {
  id: number;
  customer: string;
  total_amount: string;
  total_profit: string;
  is_canceled: boolean;
  canceled_at: string | null;
  items?: SaleItem[];
  created_at: string;
  updated_at: string;
}

export interface CreateSaleDTO {
  customer: string;
  items: Array<{
    product_id: number;
    quantity: number;
    unit_sale_price: number;
  }>;
}

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
        const response = await salesService.getAll();
        this.sales = response.data.data;
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
        const response = await salesService.create(data);
        this.sales.unshift(response.data.data);

        const productsStore = useProductsStore();
        await productsStore.fetchProducts(true);

        return response.data.data;
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
        const response = await salesService.cancel(id);
        const canceledSale = response.data.data;

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
        await salesService.delete(id);
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
