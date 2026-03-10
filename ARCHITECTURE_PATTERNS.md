# Padrões de Arquitetura - Soft ERP Frontend

**Data:** 10/03/2026
**Autor:** Claude Code + Time de Desenvolvimento

---

## 📋 Sumário

1. [Padronização de Services](#padronização-de-services)
2. [Organização de Interfaces/Types](#organização-de-interfacestypes)
3. [Boas Práticas Estabelecidas](#boas-práticas-estabelecidas)

---

## 1. Padronização de Services

### ❌ ANTES: Padrão Inconsistente

Tínhamos **dois padrões diferentes**:

#### Padrão 1: Object Export
```typescript
// ProductsService.ts, PurchasesService.ts, SalesService.ts
export const productsService = {
  getAll() {
    return api.get<ApiResponse<Product[]>>('/api/products');
  },
  create(data: CreateProductDTO) {
    return api.post<ApiResponse<Product>>('/api/products', data);
  }
};
```

#### Padrão 2: Class com Static Methods
```typescript
// DashboardService.ts, ReportsService.ts
export class DashboardService {
  static async getMetrics(): Promise<ApiResponse<DashboardMetrics>> {
    const response = await api.get<ApiResponse<DashboardMetrics>>('/dashboard/metrics');
    return response.data;
  }
}
```

### ✅ DEPOIS: Padrão Unificado (Classes)

**Todos os services agora usam classes com métodos estáticos:**

```typescript
// ProductsService.ts (refatorado)
export class ProductsService {
  static async getAll(): Promise<ApiResponse<Product[]>> {
    const response = await api.get<ApiResponse<Product[]>>(API_ENDPOINTS.PRODUCTS);
    return response.data;
  }

  static async create(data: CreateProductDTO): Promise<ApiResponse<Product>> {
    const response = await api.post<ApiResponse<Product>>(API_ENDPOINTS.PRODUCTS, data);
    return response.data;
  }

  static async delete(id: number): Promise<void> {
    await api.delete(`${API_ENDPOINTS.PRODUCTS}/${id}`);
  }
}
```

### Por Que Classes?

#### Vantagens do Padrão de Classes

1. **Escalabilidade** ✅
   - Fácil adicionar métodos privados no futuro
   - Pode adicionar propriedades estáticas (cache, config)
   ```typescript
   export class ProductsService {
     private static readonly cache = new Map();

     private static getCached(key: string) {
       return this.cache.get(key);
     }

     static async getAll() { ... }
   }
   ```

2. **Tipagem Forte** ✅
   - TypeScript infere tipos melhor
   - Autocomplete funciona melhor na IDE
   - Errors detectados em tempo de compilação

3. **Padrão Enterprise** ✅
   - Usado em projetos grandes (Angular, NestJS)
   - Familiar para desenvolvedores Java/C#
   - Facilita onboarding de novos devs

4. **Testabilidade** ✅
   - Mais fácil criar mocks
   - Pode usar spies em métodos específicos
   ```typescript
   jest.spyOn(ProductsService, 'getAll');
   ```

5. **Namespace Natural** ✅
   - Agrupa métodos relacionados logicamente
   - Evita poluir namespace global
   - `ProductsService.getAll()` é mais claro que `getProducts()`

#### Comparação com Object Export

| Característica | Object Export | Class Static | Vencedor |
|----------------|---------------|--------------|----------|
| **Simplicidade inicial** | ⭐⭐⭐ | ⭐⭐ | Object |
| **Escalabilidade** | ⭐ | ⭐⭐⭐ | Class |
| **Tipagem TypeScript** | ⭐⭐ | ⭐⭐⭐ | Class |
| **Testes/Mocks** | ⭐⭐ | ⭐⭐⭐ | Class |
| **Métodos privados** | ❌ | ✅ | Class |
| **Tree-shaking** | ⭐⭐⭐ | ⭐⭐⭐ | Empate |
| **Padrão industry** | ⭐⭐ | ⭐⭐⭐ | Class |

### Mudanças nas Stores

**ANTES:**
```typescript
import { productsService } from '@/services/api';

const response = await productsService.getAll();
this.products = response.data.data; // ❌ Duplo .data
```

**DEPOIS:**
```typescript
import { ProductsService } from '@/services/api';

const response = await ProductsService.getAll();
this.products = response.data; // ✅ Service já retorna response.data
```

### Benefícios da Mudança

1. **DRY (Don't Repeat Yourself)**
   - Service retorna diretamente `response.data`
   - Store não precisa acessar `.data.data`

2. **Tipagem Melhor**
   - Tipo de retorno explícito: `Promise<ApiResponse<Product[]>>`
   - IDE mostra autocomplete correto

3. **Async/Await Consistente**
   - Todos os métodos são `async`
   - Facilita tratamento de erros com try/catch

---

## 2. Organização de Interfaces/Types

### 📂 Regra Geral: Onde Colocar Interfaces?

#### Opção 1: Co-location (Junto com o Código) ✅ RECOMENDADO

**Quando usar:** Quando a interface é **específica** daquele módulo/arquivo.

```typescript
// ✅ CORRETO: Interface no mesmo arquivo do Service
// ReportsService.ts
export interface SalesReport {
  id: number;
  customer: string;
  total_amount: string;
}

export interface ReportFilters {
  start_date?: string;
  end_date?: string;
}

export class ReportsService {
  static async getSalesReport(filters: ReportFilters): Promise<ApiResponse<SalesReport[]>> {
    // ...
  }
}
```

**Vantagens:**
- ✅ Contexto imediato (vê interface e uso juntos)
- ✅ Facilita manutenção (tudo em um lugar)
- ✅ Melhor para refactoring (mover arquivo move tudo)
- ✅ Evita imports desnecessários

#### Opção 2: Arquivo Separado de Types

**Quando usar:** Quando a interface é **compartilhada** entre múltiplos arquivos.

```typescript
// ✅ CORRETO: Interfaces compartilhadas em arquivo dedicado
// @/modules/products/types.ts
export interface Product {
  id: number;
  name: string;
  stock: number;
}

export interface CreateProductDTO {
  name: string;
  stock: number;
}

// Usado em: ProductsService.ts, ProductsStore.ts, ProductsView.vue
```

**Vantagens:**
- ✅ Evita importações circulares
- ✅ Single source of truth
- ✅ Facilita encontrar definições
- ✅ Melhor para grandes projetos

### 📋 Decisão para o Soft ERP

Seguimos esta estrutura:

```
src/
├── modules/
│   └── products/
│       ├── types.ts              # ✅ Interfaces compartilhadas (Product, CreateProductDTO)
│       ├── views/
│       ├── components/
│       └── routes.ts
│
├── services/
│   ├── DashboardService.ts       # ✅ Interfaces específicas aqui (DashboardMetrics)
│   ├── ReportsService.ts         # ✅ Interfaces específicas aqui (ReportFilters)
│   └── api/
│       ├── ProductsService.ts    # ✅ Importa de @/modules/products/types
│       ├── PurchasesService.ts   # ❌ Interfaces em stores/purchases.ts (a mover)
│       └── SalesService.ts       # ❌ Interfaces em stores/sales.ts (a mover)
│
└── stores/
    ├── products.ts               # ✅ Importa tipos de @/modules/products/types
    ├── purchases.ts              # ⚠️ TEM Purchase, CreatePurchaseDTO (deveria mover)
    └── sales.ts                  # ⚠️ TEM Sale, CreateSaleDTO (deveria mover)
```

### ⚠️ Problema Atual: Interfaces em Stores

**SITUAÇÃO ATUAL (não ideal):**
```typescript
// stores/purchases.ts
export interface Purchase { ... }          // ❌ Deveria estar em módulo
export interface CreatePurchaseDTO { ... } // ❌ Deveria estar em módulo

// services/api/PurchasesService.ts
import { Purchase, CreatePurchaseDTO } from '@/stores/purchases'; // ❌ Service importando de Store
```

**SOLUÇÃO RECOMENDADA:**
```typescript
// modules/purchases/types.ts (criar novo arquivo)
export interface Purchase { ... }
export interface PurchaseItem { ... }
export interface CreatePurchaseDTO { ... }

// stores/purchases.ts
import type { Purchase, CreatePurchaseDTO } from '@/modules/purchases/types'; // ✅

// services/api/PurchasesService.ts
import type { Purchase, CreatePurchaseDTO } from '@/modules/purchases/types'; // ✅
```

### 🎯 Regra de Ouro

```
Se uma interface é usada em 2+ lugares → Arquivo separado de types
Se uma interface é usada em 1 lugar → Co-location (mesmo arquivo)
```

### Exemplo Prático: DashboardService

**POR QUE está correto ter interfaces no DashboardService.ts?**

```typescript
// DashboardService.ts
export interface DashboardMetrics {
  sales_this_month: string;
  profit_this_month: string;
  // ...
}

export class DashboardService {
  static async getMetrics(): Promise<ApiResponse<DashboardMetrics>> {
    // ...
  }
}
```

✅ **DashboardMetrics** só é usada:
1. DashboardService.ts (retorno do método)
2. DashboardStore.ts (salva no state)
3. DashboardView.vue (renderiza)

Como é **específica do domínio Dashboard** e não é reutilizada em outros módulos, fica junto com o Service.

### Exemplo Prático: Product

**POR QUE Product está em arquivo separado?**

```typescript
// @/modules/products/types.ts
export interface Product {
  id: number;
  name: string;
  stock: number;
  // ...
}
```

✅ **Product** é usada em:
1. ProductsService.ts
2. ProductsStore.ts
3. ProductsView.vue
4. ProductForm.vue
5. **PurchasesView.vue** (lista produtos para selecionar)
6. **SalesView.vue** (lista produtos para selecionar)
7. PurchasesStore.ts (referencia produtos)
8. SalesStore.ts (referencia produtos)

Como é **compartilhada entre múltiplos módulos**, merece arquivo próprio.

---

## 3. Boas Práticas Estabelecidas

### ✅ Padrões a Seguir

#### 3.1 Nomenclatura de Services

```typescript
// ✅ CORRETO
export class ProductsService { }
export class DashboardService { }
export class ReportsService { }

// ❌ ERRADO
export const ProductService = { } // Sem plural
export class ProductsApi { }      // Api no nome é redundante
export const products = { }       // Sem sufixo Service
```

#### 3.2 Métodos Async

```typescript
// ✅ CORRETO - Todos os métodos são async
export class ProductsService {
  static async getAll(): Promise<ApiResponse<Product[]>> {
    const response = await api.get(...);
    return response.data;
  }

  static async delete(id: number): Promise<void> {
    await api.delete(...);
  }
}

// ❌ ERRADO - Retornar Promise direto
export class ProductsService {
  static getAll(): Promise<AxiosResponse<...>> {
    return api.get(...); // ❌ Vaza implementação do Axios
  }
}
```

#### 3.3 Tipos de Retorno

```typescript
// ✅ CORRETO - Retorna nosso tipo ApiResponse
static async getAll(): Promise<ApiResponse<Product[]>> {
  const response = await api.get<ApiResponse<Product[]>>(...);
  return response.data; // Retorna data do Axios
}

// ❌ ERRADO - Retorna tipo do Axios
static async getAll(): Promise<AxiosResponse<...>> {
  return api.get(...); // Vaza abstração
}
```

#### 3.4 Uso de Constantes

```typescript
// ✅ CORRETO
import { API_ENDPOINTS } from '@/config/api';

static async getAll() {
  return api.get(API_ENDPOINTS.PRODUCTS); // ✅ Centralizado
}

// ❌ ERRADO
static async getAll() {
  return api.get('/product/products'); // ❌ Hardcoded
}
```

#### 3.5 Estrutura de Pastas

```
src/
├── modules/              # Domínios de negócio
│   ├── products/
│   │   ├── types.ts     # Interfaces compartilhadas
│   │   ├── views/
│   │   ├── components/
│   │   └── routes.ts
│   │
│   ├── purchases/
│   └── sales/
│
├── services/             # Camada de serviços
│   ├── api/             # Services de API
│   │   ├── ProductsService.ts
│   │   ├── PurchasesService.ts
│   │   └── SalesService.ts
│   │
│   ├── DashboardService.ts  # Services de domínio
│   └── ReportsService.ts
│
├── stores/               # State management
│   ├── products.ts
│   ├── purchases.ts
│   └── sales.ts
│
└── config/               # Configurações
    ├── api.ts           # API_ENDPOINTS, API_CONFIG
    └── index.ts
```

---

## 4. Checklist de Refatoração

### ✅ Concluído

- [x] Padronizar todos os services para usar classes
- [x] Adicionar tipos de retorno explícitos em todos os métodos
- [x] Retornar `response.data` nos services (evitar `.data.data`)
- [x] Atualizar stores para usar novos services
- [x] Usar `API_ENDPOINTS` em todos os services
- [x] Adicionar `async/await` em todos os métodos

### 🔄 Próximos Passos Recomendados

- [ ] Mover interfaces de stores/purchases.ts para modules/purchases/types.ts
- [ ] Mover interfaces de stores/sales.ts para modules/sales/types.ts
- [ ] Criar módulo de autenticação com types próprio
- [ ] Adicionar JSDoc nos services principais
- [ ] Implementar cache layer nos services (opcional)

---

## 5. Exemplos de Uso

### Como Usar os Services Refatorados

#### Em Stores
```typescript
import { ProductsService } from '@/services/api';

export const useProductsStore = defineStore('products', {
  actions: {
    async fetchProducts() {
      this.loading = true;
      try {
        const response = await ProductsService.getAll();
        this.products = response.data; // ✅ response.data (não .data.data)
      } catch (error) {
        this.error = 'Erro ao buscar produtos';
      } finally {
        this.loading = false;
      }
    }
  }
});
```

#### Em Components
```vue
<script setup lang="ts">
import { ProductsService } from '@/services/api';
import { ref } from 'vue';

const products = ref<Product[]>([]);

async function loadProducts() {
  const response = await ProductsService.getAll();
  products.value = response.data;
}
</script>
```

#### Em Testes
```typescript
import { ProductsService } from '@/services/api';

describe('ProductsService', () => {
  it('should fetch all products', async () => {
    const spy = jest.spyOn(ProductsService, 'getAll');

    await ProductsService.getAll();

    expect(spy).toHaveBeenCalled();
  });
});
```

---

## 6. Referências

### Documentação Oficial
- [TypeScript Classes](https://www.typescriptlang.org/docs/handbook/2/classes.html)
- [Vue 3 Composition API](https://vuejs.org/guide/typescript/composition-api.html)
- [Pinia with TypeScript](https://pinia.vuejs.org/core-concepts/#typescript)

### Padrões de Projeto
- Service Layer Pattern
- Repository Pattern
- Dependency Injection

### Ferramentas
- ESLint rules para consistência
- Prettier para formatação
- TypeScript strict mode

---

**Última atualização:** 10/03/2026
**Versão:** 2.0
**Mantido por:** Time de Desenvolvimento Soft ERP
