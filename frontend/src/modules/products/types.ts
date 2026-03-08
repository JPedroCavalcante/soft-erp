export interface Product {
  id: number;
  name: string;
  sale_price: string;
  stock: number;
  average_cost: string;
  created_at: string;
  updated_at: string;
}

export interface CreateProductDTO {
  name: string;
  sale_price: number;
}
