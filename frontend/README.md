# Soft ERP - Frontend

Frontend modular do sistema ERP desenvolvido com Vue 3 + TypeScript + Vite.

## Tecnologias

- **Vue 3** - Framework JavaScript progressivo
- **TypeScript** - Tipagem estática em strict mode
- **Vite** - Build tool e dev server
- **Vue Router 4** - Navegação SPA
- **Axios** - Cliente HTTP para API REST

## Estrutura do Projeto

```
frontend/
├── src/
│   ├── core/                      # Camada core da aplicação
│   │   ├── api.ts                 # Cliente Axios configurado
│   │   ├── types.ts               # Tipos globais (ApiResponse, etc)
│   │   └── layouts/
│   │       └── AppLayout.vue      # Layout principal com navbar
│   ├── modules/                   # Módulos de features (Products, Purchases, Sales)
│   ├── router/
│   │   └── index.ts               # Configuração Vue Router
│   ├── views/
│   │   └── TestApiConnection.vue  # Componente de teste de API
│   ├── App.vue                    # Componente raiz
│   ├── main.ts                    # Entry point
│   └── env.d.ts                   # Declarações TypeScript
├── index.html                     # HTML principal
├── vite.config.ts                 # Configuração Vite
├── tsconfig.json                  # Configuração TypeScript
└── package.json
```

## Arquitetura

### Organização Modular

O projeto segue uma arquitetura **orientada a features** onde cada módulo de negócio (Products, Purchases, Sales) será organizado em `src/modules/`:

```
src/modules/
├── products/
│   ├── routes.ts
│   ├── types.ts
│   ├── api.ts
│   └── views/
└── purchases/
    └── ...
```

### Core Layer

A camada `src/core/` contém:

- **api.ts**: Cliente Axios configurado com interceptors globais
- **types.ts**: Tipos TypeScript compartilhados (ApiResponse, ValidationError, PaginatedResponse)
- **layouts/**: Layouts reutilizáveis (AppLayout com navbar)

### TypeScript Strict Mode

O projeto utiliza TypeScript em modo strict com todas as verificações habilitadas:
- `strict: true`
- `noUnusedLocals: true`
- `noUnusedParameters: true`
- `noFallthroughCasesInSwitch: true`

### Path Alias

Configurado alias `@/` para importações absolutas:

```typescript
import api from '@/core/api';
import AppLayout from '@/core/layouts/AppLayout.vue';
```

## Comandos

### Desenvolvimento

```bash
npm run dev
```

Inicia o servidor de desenvolvimento em http://localhost:5173

### Build de Produção

```bash
npm run build
```

Compila TypeScript e gera build otimizado em `/dist`

### Preview de Produção

```bash
npm run preview
```

Serve o build de produção localmente

## Integração com Backend

O frontend consome a API REST Laravel em `http://localhost:8000/api`.

### Teste de Conexão

Acesse `/test-api` para testar a conexão com a API:
- Verifica conectividade com `/product/products`
- Exibe produtos retornados
- Mostra erros de conexão/validação

### Cliente API

```typescript
// src/core/api.ts
import api from '@/core/api';

// GET request
const response = await api.get<ApiResponse<Product[]>>('/product/products');

// POST request
await api.post('/product/products', productData);
```

### Tipos de Response

```typescript
// Response padrão Laravel Resource
interface ApiResponse<T> {
  data: T;
  message?: string;
}

// Erro de validação (422)
interface ValidationError {
  message: string;
  errors: Record<string, string[]>;
}
```

## Docker Support

O projeto está configurado para rodar em Docker com hot-reload:

```yaml
# vite.config.ts
server: {
  host: true,
  port: 5173,
  watch: {
    usePolling: true, // Para HMR no Docker
  },
}
```

## Próximos Passos

1. Implementar módulo **Products** em `src/modules/products/`
2. Implementar módulo **Purchases** em `src/modules/purchases/`
3. Implementar módulo **Sales** em `src/modules/sales/`
4. Adicionar biblioteca de componentes UI (opcional)
5. Implementar tratamento de erros global
6. Adicionar loading states globais
7. Implementar testes unitários (Vitest)

## Convenções

- Componentes Vue em PascalCase (ex: `AppLayout.vue`)
- Composables com prefixo `use` (ex: `useProducts.ts`)
- Tipos em PascalCase (ex: `ApiResponse`, `Product`)
- Arquivos de configuração em kebab-case
- Strict TypeScript - sem `any` sem justificativa

## Status

- [x] Configuração base Vue 3 + TypeScript + Vite
- [x] Cliente API (Axios)
- [x] Tipos globais
- [x] Router base
- [x] Layout principal com navbar
- [x] Path alias `@/`
- [x] TypeScript strict mode
- [x] Componente de teste de API
- [ ] Módulo Products
- [ ] Módulo Purchases
- [ ] Módulo Sales
