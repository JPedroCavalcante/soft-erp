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
