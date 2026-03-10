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
