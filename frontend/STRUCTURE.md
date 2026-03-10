# Estrutura do Projeto Frontend

```
frontend/
│
├── 📄 Configuration Files
│   ├── package.json              # Dependências e scripts npm
│   ├── vite.config.ts            # Configuração Vite + alias @/
│   ├── tsconfig.json             # TypeScript strict mode + path mapping
│   ├── tsconfig.node.json        # TypeScript para arquivos de config
│   ├── index.html                # HTML principal
│   ├── .gitignore                # Arquivos ignorados pelo git
│   ├── Dockerfile                # Container Docker
│   └── entrypoint.sh             # Script de entrada Docker
│
├── 📚 Documentation
│   ├── README.md                 # Documentação principal do projeto
│   ├── VALIDATION.md             # Checklist de validação da configuração
│   └── STRUCTURE.md              # Este arquivo (estrutura visual)
│
└── 📁 src/                       # Código-fonte
    │
    ├── 🎯 Core (Infraestrutura)
    │   ├── core/
    │   │   ├── api.ts                    # ⚙️ Cliente Axios configurado
    │   │   │                             #    - baseURL: http://localhost:8000/api
    │   │   │                             #    - Interceptors de erro
    │   │   │                             #    - Headers padrão
    │   │   │
    │   │   ├── types.ts                  # 📦 Tipos TypeScript globais
    │   │   │                             #    - ApiResponse<T>
    │   │   │                             #    - ValidationError
    │   │   │                             #    - PaginatedResponse<T>
    │   │   │
    │   │   └── layouts/
    │   │       └── AppLayout.vue         # 🎨 Layout principal
    │   │                                 #    - Navbar responsiva
    │   │                                 #    - Links: Products, Purchases, Sales
    │   │                                 #    - Slot para router-view
    │   │
    │   ├── router/
    │   │   └── index.ts                  # 🗺️ Vue Router 4
    │   │                                 #    - History mode
    │   │                                 #    - Redirect / → /products
    │   │                                 #    - Rotas de teste
    │   │                                 #    - Preparado para módulos
    │   │
    │   ├── App.vue                       # 🏠 Componente raiz
    │   ├── main.ts                       # 🚀 Entry point
    │   └── env.d.ts                      # 🔧 Declarações TypeScript (.vue)
    │
    ├── 🧩 Modules (Features - Vazio, pronto para uso)
    │   ├── .gitkeep
    │   └── README.md                     # 📖 Documentação da estrutura de módulos
    │   │                                 #    - Exemplos de types.ts
    │   │                                 #    - Exemplos de api.ts
    │   │                                 #    - Exemplos de routes.ts
    │   │                                 #    - Exemplos de composables
    │   │                                 #    - Estrutura de views/components
    │   │
    │   └── (Futuros módulos aqui)
    │       ├── products/
    │       │   ├── types.ts              # Tipos do módulo
    │       │   ├── api.ts                # API calls do módulo
    │       │   ├── routes.ts             # Rotas do módulo
    │       │   ├── composables/          # Lógica reativa
    │       │   │   └── useProducts.ts
    │       │   ├── views/                # Páginas
    │       │   │   ├── ProductList.vue
    │       │   │   ├── ProductForm.vue
    │       │   │   └── ProductView.vue
    │       │   └── components/           # Componentes internos
    │       │       └── ProductCard.vue
    │       │
    │       ├── purchases/
    │       │   └── ...
    │       │
    │       └── sales/
    │           └── ...
    │
    └── 📄 Views (Views standalone)
        └── TestApiConnection.vue         # 🧪 Componente de teste
                                          #    - Testa GET /product/products
                                          #    - Exibe loading/error/success
                                          #    - Preview de produtos
```

## Fluxo de Dados

```
┌─────────────────────────────────────────────────────────────┐
│                      main.ts (Entry Point)                   │
│  - Cria app Vue                                             │
│  - Registra router                                          │
│  - Monta em #app                                            │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                         App.vue                              │
│  - Renderiza AppLayout                                      │
│  - Slot para router-view                                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                  core/layouts/AppLayout.vue                  │
│  - Navbar com navegação                                     │
│  - Links: Teste API, Products, Purchases, Sales             │
│  - <slot /> para conteúdo                                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                    <router-view />                           │
│  Renderiza componente baseado na rota:                      │
│  - /test-api → TestApiConnection.vue                        │
│  - /products → (futuro) ProductList.vue                     │
│  - /purchases → (futuro) PurchaseList.vue                   │
│  - /sales → (futuro) SaleList.vue                           │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              Componentes usam core/api.ts                    │
│  - Cliente Axios configurado                                │
│  - Chamadas à API Laravel (localhost:8000/api)              │
│  - Tipagem com core/types.ts                                │
└─────────────────────────────────────────────────────────────┘
```

## Arquitetura de Módulos (Futura)

```
Módulo Products (exemplo)
├── types.ts          → Define: Product, ProductFormData
├── api.ts            → Funções: getAll(), getById(), create(), update(), delete()
├── routes.ts         → Rotas: /products, /products/new, /products/:id, etc
├── composables/
│   └── useProducts   → Estado reativo: products[], loading, error
│                     → Métodos: fetchProducts(), createProduct(), etc
├── views/
│   ├── ProductList   → Lista de produtos (tabela/cards)
│   ├── ProductForm   → Criar/editar produto
│   └── ProductView   → Visualizar detalhes
└── components/
    └── ProductCard   → Card de produto (reutilizável)

Integração no router principal:
import { productRoutes } from '@/modules/products/routes';
routes.push(...productRoutes);
```

## Path Alias

O projeto usa `@/` como alias para `/src`:

```typescript
// ✅ Correto
import api from '@/core/api';
import AppLayout from '@/core/layouts/AppLayout.vue';
import { Product } from '@/modules/products/types';

// ❌ Evite
import api from '../../../core/api';
```

## TypeScript Strict Mode

Todas as verificações strict habilitadas:

```json
{
  "strict": true,                    // Modo strict geral
  "noUnusedLocals": true,            // Erro em variáveis não usadas
  "noUnusedParameters": true,        // Erro em parâmetros não usados
  "noFallthroughCasesInSwitch": true // Erro em switch sem break
}
```

## Scripts NPM

```bash
npm run dev      # Desenvolvimento (localhost:5173)
npm run build    # Build de produção (compila TS + otimiza)
npm run preview  # Preview do build
```

## Comandos de Validação

```bash
# Verificar TypeScript
npx vue-tsc --noEmit

# Build de teste
npx vite build

# Instalar dependências
npm install
```

## Status Atual

- ✅ Infraestrutura completa
- ✅ TypeScript strict configurado
- ✅ Router configurado
- ✅ API client pronto
- ✅ Layout responsivo
- ✅ Componente de teste
- ✅ Documentação completa
- ⏳ Módulos de features (próximo passo)

## Próximos Passos

1. Implementar `src/modules/products/`
2. Implementar `src/modules/purchases/`
3. Implementar `src/modules/sales/`
