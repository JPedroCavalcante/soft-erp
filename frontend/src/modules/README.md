# Módulos de Features

Esta pasta contém os módulos de negócio organizados por domínio.

## Estrutura de um Módulo

Cada módulo segue a seguinte estrutura:

```
modules/
└── products/                    # Nome do módulo (ex: products, purchases, sales)
    ├── types.ts                 # Tipos TypeScript do módulo
    ├── api.ts                   # Chamadas API específicas do módulo
    ├── routes.ts                # Rotas Vue Router do módulo
    ├── composables/             # Composables reutilizáveis
    │   └── useProducts.ts
    ├── views/                   # Páginas/Views do módulo
    │   ├── ProductList.vue
    │   ├── ProductForm.vue
    │   └── ProductView.vue
    └── components/              # Componentes internos do módulo
        └── ProductCard.vue
```

## Exemplo de Implementação

### types.ts
```typescript
export interface Product {
  id: number;
  name: string;
  purchase_price: number;
  sale_price: number;
  stock: number;
  created_at: string;
  updated_at: string;
}

export interface ProductFormData {
  name: string;
  purchase_price: number;
  sale_price: number;
  stock: number;
}
```

### api.ts
```typescript
import api from '@/core/api';
import type { ApiResponse } from '@/core/types';
import type { Product, ProductFormData } from './types';

export const productApi = {
  getAll: () =>
    api.get<ApiResponse<Product[]>>('/product/products'),

  getById: (id: number) =>
    api.get<ApiResponse<Product>>(`/product/products/${id}`),

  create: (data: ProductFormData) =>
    api.post<ApiResponse<Product>>('/product/products', data),

  update: (id: number, data: ProductFormData) =>
    api.put<ApiResponse<Product>>(`/product/products/${id}`, data),

  delete: (id: number) =>
    api.delete(`/product/products/${id}`),
};
```

### routes.ts
```typescript
import type { RouteRecordRaw } from 'vue-router';

export const productRoutes: RouteRecordRaw[] = [
  {
    path: '/products',
    name: 'ProductList',
    component: () => import('./views/ProductList.vue'),
  },
  {
    path: '/products/new',
    name: 'ProductCreate',
    component: () => import('./views/ProductForm.vue'),
  },
  {
    path: '/products/:id',
    name: 'ProductView',
    component: () => import('./views/ProductView.vue'),
  },
  {
    path: '/products/:id/edit',
    name: 'ProductEdit',
    component: () => import('./views/ProductForm.vue'),
  },
];
```

### composables/useProducts.ts
```typescript
import { ref, computed } from 'vue';
import { productApi } from '../api';
import type { Product, ProductFormData } from '../types';

export function useProducts() {
  const products = ref<Product[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const fetchProducts = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await productApi.getAll();
      products.value = response.data.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message;
    } finally {
      loading.value = false;
    }
  };

  const createProduct = async (data: ProductFormData) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await productApi.create(data);
      products.value.push(response.data.data);
      return response.data.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    products,
    loading,
    error,
    fetchProducts,
    createProduct,
  };
}
```

## Integração com Router Principal

No arquivo `src/router/index.ts`, importar e incluir as rotas do módulo:

```typescript
import { productRoutes } from '@/modules/products/routes';
import { purchaseRoutes } from '@/modules/purchases/routes';
import { saleRoutes } from '@/modules/sales/routes';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/products'
  },
  ...productRoutes,
  ...purchaseRoutes,
  ...saleRoutes,
];
```

## Princípios

1. **Encapsulamento**: Cada módulo é autocontido e independente
2. **Tipagem Forte**: Todos os tipos definidos no `types.ts` do módulo
3. **Composables**: Lógica reativa reutilizável em `composables/`
4. **Lazy Loading**: Views carregadas sob demanda via dynamic imports
5. **API Client**: Camada de API específica do módulo em `api.ts`
6. **Rotas Isoladas**: Rotas declaradas em `routes.ts` e importadas no router principal

## Módulos Planejados

- [ ] **products** - Gestão de produtos
- [ ] **purchases** - Gestão de compras
- [ ] **sales** - Gestão de vendas
