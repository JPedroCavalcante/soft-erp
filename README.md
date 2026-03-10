# Soft ERP

Sistema de gerenciamento empresarial (ERP) com backend em Laravel 12 e frontend em Vue 3 + TypeScript, totalmente conteinerizado via Docker.

---

## Sumário

- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Usuários padrão](#usuários-padrão)
- [Arquitetura do Backend](#arquitetura-do-backend)
- [Arquitetura do Frontend](#arquitetura-do-frontend)
- [Testes](#testes)

---

## Requisitos

- Docker e Docker Compose instalados
- Portas `8000`, `5173` e `3306` disponíveis na máquina host

---

## Instalação

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd soft-erp
```

### 2. Subir os contêineres

```bash
docker compose up -d --build
```

O entrypoint do contêiner `backend` executa automaticamente na inicialização:
- Instalação das dependências PHP via Composer
- Criação e configuração do `.env` a partir do `.env.example`
- Geração do `APP_KEY`
- Execução das migrations e seeders (dados de teste incluídos)
- Limpeza de caches

Serviços iniciados:

| Serviço    | Descrição                        | Porta host |
|------------|----------------------------------|------------|
| `mysql`    | MySQL 8.0                        | 3306       |
| `backend`  | Laravel 12 (PHP 8.4)             | 8000       |
| `frontend` | Vite dev server (Vue 3 + TS)     | 5173       |

### 3. Acessar a aplicação

- Frontend: [http://localhost:5173](http://localhost:5173)
- API: [http://localhost:8000/api](http://localhost:8000/api)
- Documentação API (Scramble): [http://localhost:8000/docs/api](http://localhost:8000/docs/api)

---

## Usuários padrão

Após rodar os seeders, os seguintes usuários estarão disponíveis:

| E-mail                | Senha | Perfil        |
|-----------------------|-------|---------------|
| admin@softerp.com     | admin | Administrador |
| user@softerp.com      | admin | Usuário Teste |

**Dados de teste inseridos automaticamente:**
- 15 produtos (via factory)
- 5 compras com múltiplos itens
- 10 vendas (incluindo dados para métricas do dashboard)

---

## Arquitetura do Backend

### Tecnologias

- **PHP 8.4** / **Laravel 12**
- **MySQL 8.0**
- **Sanctum** para autenticação API
- **Scramble** para documentação automática da API
- **DomPDF** para exportação de relatórios em PDF
- **Maatwebsite/Excel** para exportação em Excel
- **PHPUnit** para testes

### Diferenças em relação ao Laravel padrão

A instalação padrão do Laravel assume uma aplicação web convencional com renderização server-side. Este projeto diverge em vários pontos deliberados:

**1. API-only — sem Blade, sem rotas web**

O arquivo `routes/web.php` não é utilizado. Toda a comunicação ocorre via `routes/api.php`, e todos os controladores retornam exclusivamente JSON. Views Blade são utilizadas apenas para templates de exportação PDF.

**2. Autenticação stateless com Sanctum**

O Laravel 12 utiliza autenticação baseada em sessão por padrão. Aqui isso é substituído por `laravel/sanctum`: o login retorna um token que o cliente armazena e envia no header `Authorization: Bearer <token>` em cada requisição. Não há cookies de sessão.

**3. Arquitetura modular**

Em vez da estrutura MVC tradicional em `app/Http/Controllers`, este projeto utiliza uma arquitetura modular em `app/Modules/`, onde cada módulo possui:
- Controllers
- Services
- Repositories
- Models
- Requests (Form Requests)
- Resources (API Resources)
- Routes
- Database (Migrations, Seeders, Factories)

**4. Form Requests sempre retornam JSON**

Por padrão, quando um Form Request falha, o Laravel redireciona o usuário de volta com os erros na sessão — comportamento adequado para apps web, mas inadequado para APIs. Todos os Form Requests utilizam sintaxe de array para validação e retornam automaticamente `422` em JSON.

**5. Repository Pattern para desacoplamento**

Todos os módulos implementam o Repository Pattern:

- **Services** (`app/Modules/*/Services/`): contêm a lógica de negócio. Os controladores delegam operações aos services.
- **Repositories** (`app/Modules/*/Repositories/`): encapsulam o acesso ao banco de dados. Todos estendem `BaseRepository` que implementa operações comuns (CRUD, paginação).

```
Controller -> Service -> Repository -> Model (Eloquent) -> Banco
```

**6. API Resources para formatação consistente**

Todas as respostas da API passam por `JsonResource` que padroniza o formato de saída e aplica transformações (ex: formatação de valores monetários, datas).

**7. Documentação automática com Scramble**

A documentação da API é gerada automaticamente a partir dos DocBlocks dos controladores e Form Requests, disponível em `/docs/api` com interface interativa.

### Princípios

O backend é uma **API REST pura**. Não há views Blade para frontend (apenas para templates PDF). Todas as respostas são em JSON.

### Estrutura de rotas

As rotas estão definidas em `backend/routes/api.php` e carregadas dinamicamente a partir de cada módulo:

```
POST   /api/login                           Autenticação (público)
POST   /api/logout                          Encerra sessão (autenticado)
GET    /api/user                            Dados do usuário logado (autenticado)

/api/product/*  (autenticado)
  GET|POST|PUT|DELETE  /products            CRUD de produtos

/api/purchase/*  (autenticado)
  GET|POST|DELETE      /purchases           Gerenciamento de compras
                                            (atualiza custo médio e estoque)

/api/sale/*  (autenticado)
  GET|POST|DELETE      /sales               Gerenciamento de vendas
  POST                 /sales/{id}/cancel   Cancelar venda (estorna estoque)

/api/dashboard/*  (autenticado)
  GET                  /metrics             Métricas em tempo real

/api/report/*  (autenticado)
  GET                  /reports/sales       Relatório de vendas
  GET                  /reports/purchases   Relatório de compras
  GET                  /reports/profit      Relatório de lucro por produto
  GET                  /reports/stock       Relatório de estoque
  POST                 /reports/export      Exportar (PDF ou Excel)
```

### Módulos da aplicação

```
backend/app/Modules/
  Product/
    Controllers/
    Services/           ProductService.php
    Repositories/       ProductRepository.php
    Models/             Product.php
    Requests/           StoreProductRequest.php, UpdateProductRequest.php
    Resources/          ProductResource.php
    Routes/             api.php
    Database/
      Migrations/
      Seeders/
      Factories/

  Purchase/             # Compras (atualiza custo médio ponderado)
  Sale/                 # Vendas (calcula lucro, decrementa estoque)
  Dashboard/            # Métricas e KPIs
  Report/               # Relatórios com exportação PDF/Excel
  Auth/                 # Autenticação
```

**Convenção de nomenclatura:** todos os campos de modelos, migrations e controladores utilizam nomes em inglês (`name`, `email`, `stock`, `average_cost`, `total_amount`, `total_profit`).

### Regras de Negócio Implementadas

#### Cálculo de Custo Médio Ponderado

```
novo_custo = (custo_atual × estoque_atual + preço_compra × qtd_comprada) / (estoque_atual + qtd_comprada)
```

- Executado em transação DB
- Atualiza produto imediatamente após adicionar item de compra
- Funciona corretamente com múltiplos itens em uma compra

#### Cálculo de Lucro em Vendas

```
lucro = (preço_venda - custo_médio_histórico) × quantidade
```

- Captura `historical_average_cost` do produto NO MOMENTO da venda
- Calcula lucro (pode ser negativo se venda abaixo do custo)
- Armazena histórico para relatórios precisos
- Decrementa `stock` do produto
- NÃO altera `average_cost` (só muda em compras)
- Valida estoque antes de processar

#### Cancelamento de Vendas

- Marca venda como cancelada (`is_canceled = true`)
- Estorna estoque dos produtos vendidos
- Mantém histórico da venda (soft cancel)

### Autenticação e autorização

- O login retorna um token Sanctum que deve ser enviado no header `Authorization: Bearer <token>` em todas as requisições autenticadas.
- Todas as rotas (exceto login) exigem autenticação via middleware `auth:sanctum`.

### Banco de dados

As tabelas são gerenciadas exclusivamente via migrations:

| Tabela            | Descrição                                      |
|-------------------|------------------------------------------------|
| `users`           | Usuários do sistema                            |
| `products`        | Produtos (com `stock` e `average_cost`)        |
| `purchases`       | Compras (com `total_amount`)                   |
| `purchase_items`  | Itens de compra (FK para purchases e products) |
| `sales`           | Vendas (com `total_amount`, `total_profit`)    |
| `sale_items`      | Itens de venda (com `historical_average_cost`) |

---

## Arquitetura do Frontend

### Tecnologias

- **Vue 3** com Composition API (`<script setup>`)
- **TypeScript** (strict mode)
- **Vite** como bundler
- **Vue Router 4** para navegação
- **Pinia** para gerenciamento de estado
- **Axios** para comunicação com a API
- **Chart.js** / **vue-chartjs** para visualizações (dashboard)

### Estrutura de diretórios

```
frontend/src/
  core/
    api.ts              # Instância Axios configurada
    types.ts            # Types globais (ApiResponse, ValidationError)
    components/         # Componentes reutilizáveis (Icon, etc.)
    layouts/
      AppLayout.vue     # Layout principal com navbar

  config/
    api.ts              # Endpoints e configurações da API
    app.ts              # Constantes da aplicação

  services/api/
    ProductsService.ts  # Classe com métodos estáticos
    PurchasesService.ts
    SalesService.ts

  stores/
    auth.ts             # Autenticação (Pinia)
    products.ts         # Estado de produtos
    purchases.ts        # Estado de compras
    sales.ts            # Estado de vendas

  modules/
    auth/
      views/
        LoginView.vue
      types.ts

    products/
      views/
        ProductsView.vue
      components/
        ProductForm.vue
        ProductTable.vue
      types.ts

    purchases/
      views/
        PurchasesView.vue
      components/
        PurchaseForm.vue
        PurchaseList.vue
      types.ts

    sales/
      views/
        SalesView.vue
      components/
        SaleForm.vue
        SaleList.vue
      types.ts

    dashboard/
      views/
        DashboardView.vue

    reports/
      views/
        ReportsView.vue

  router/
    index.ts            # Rotas com guards de autenticação

  composables/
    useForm.ts          # Gerenciamento de formulários
    useToast.ts         # Sistema de notificações
    useDebounce.ts      # Debounce para inputs
```

### Fluxo de dados

```
View/Component
    |
    v
Pinia Store (opcional, para cache)
    |
    v
Service (ex: ProductsService.ts)
    |
    v
api.ts  <-- instância Axios configurada com baseURL via .env
    |
    v
API REST (Laravel)
```

Cada módulo possui seu próprio arquivo de tipos (`types.ts`) que define interfaces TypeScript para os dados manipulados.

### Gerenciamento de estado

O Pinia é utilizado para estado global com cache de 60 segundos:

- **Auth Store**: Token, usuário logado, login/logout
- **Products Store**: Lista de produtos, getters (`productById`, `inStockProducts`)
- **Purchases Store**: Lista de compras
- **Sales Store**: Lista de vendas com validação de estoque

### Proteção de rotas

O Vue Router possui guards de navegação que verificam:

1. Se o usuário está autenticado (token presente no localStorage).
2. Redireciona para `/login` se não autenticado.
3. Redireciona para `/dashboard` após login bem-sucedido.

### Tratamento de erros

O interceptor Axios em `core/api.ts` trata automaticamente:
- **401**: Remove token e redireciona para login
- **403**: Exibe erro de permissão
- **404**: Exibe erro de recurso não encontrado
- **500**: Exibe erro interno do servidor
- **422**: Validação - tratado nos componentes

### Sistema de Notificações (Toast)

Componente `ToastContainer` com auto-dismiss e transições suaves:
- Success: 3s
- Error: 4s
- Warning: 3.5s
- Info: 3s

### Variáveis de Ambiente

O frontend utiliza arquivo `.env` para configurações:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

As tipagens TypeScript para variáveis de ambiente estão em `src/env.d.ts`.

---

## Testes

Os testes de backend utilizam PHPUnit com banco de dados em memória (`LazilyRefreshDatabase`).

Para executar:

```bash
docker compose exec backend php artisan test
```

### Suíte de testes (94 testes no total)

#### `AuthTest` — 11 testes

Cobre autenticação e endpoints públicos/protegidos.

| Teste | O que valida |
|---|---|
| Login com credenciais válidas | 200, token presente e dados do usuário |
| Login falha com credenciais inválidas | 401 + mensagem de erro |
| Login valida campos obrigatórios | 422 para `email` e `password` ausentes |
| Login valida formato de e-mail | 422 para e-mail malformado |
| Usuário autenticado pode fazer logout | 200 + token revogado |
| Logout exige autenticação | 401 sem token |
| `/user` retorna dados do usuário autenticado | Campos presentes |
| `/user` exige autenticação | 401 sem token |

---

#### `ProductTest` — 17 testes

Cobre o CRUD completo de produtos.

| Área | O que valida |
|---|---|
| Index | Autenticado 200 com dados; não autenticado 401 |
| Store | Cria produto 201 + DB; valida campos obrigatórios (`name`, `stock`, `average_cost`, `sale_price`); tipos numéricos; `description` opcional |
| Show | 200 com produto; 404 para ID inexistente |
| Update | 200 + DB atualizado; permite atualizações parciais com `sometimes`; valida tipos |
| Destroy | 204 + registro removido; 404 para ID inexistente |

---

#### `PurchaseTest` — 13 testes

Cobre compras e cálculo de custo médio ponderado.

| Área | O que valida |
|---|---|
| Index | 200 com lista de compras e itens |
| Store | Cria compra 201 + DB; valida `supplier` e `items` obrigatórios; valida estrutura de itens (`product_id`, `quantity`, `unit_price`) |
| Custo Médio | Calcula corretamente: `(3000×10 + 3300×5) / 15 = 3100`; funciona com múltiplos itens; atualiza estoque corretamente |
| Edge Cases | Compra sem itens rejeitada; `product_id` inexistente rejeitado |
| Destroy | 204 + registro removido |

---

#### `SaleTest` — 16 testes

Cobre vendas, cálculo de lucro e validação de estoque.

| Área | O que valida |
|---|---|
| Index | 200 com lista de vendas e itens |
| Store | Cria venda 201 + DB; valida `customer` e `items`; calcula lucro: `(4500-3100) × 3 = 4200`; calcula prejuízo: `(2500-3000) × 5 = -2500`; armazena `historical_average_cost`; decrementa estoque; NÃO altera `average_cost` |
| Validação Estoque | Rejeita venda sem estoque suficiente 422; mensagem de erro clara em português |
| Cancelamento | Cancela venda 200 + `is_canceled = true`; estorna estoque corretamente; venda já cancelada retorna erro |
| Edge Cases | Venda sem itens rejeitada; `product_id` inexistente rejeitado |

---

#### `DashboardTest` — 6 testes

Cobre métricas e agregações do dashboard.

| Teste | O que valida |
|---|---|
| Vendas do mês atual | Soma correta do `total_amount` |
| Lucro do mês atual | Soma correta do `total_profit` |
| Produtos com estoque baixo | Filtro correto (`stock <= 10`) |
| Top 5 produtos mais vendidos | Agregação com JOIN e GROUP BY |
| Comparação hoje vs ontem | Cálculo de percentual de mudança |
| Valor total do estoque | `SUM(stock × average_cost)` |

---

#### `ReportTest` — 10 testes

Cobre relatórios e exportação.

| Área | O que valida |
|---|---|
| Relatório de Vendas | Filtros por período e cliente; dados corretos |
| Relatório de Compras | Filtros por período e fornecedor |
| Relatório de Lucro | Filtros por período e produto; cálculo correto |
| Relatório de Estoque | Classificação por status (SEM ESTOQUE, BAIXO, NORMAL, ALTO) |
| Exportação PDF | Gera PDF válido com dados corretos; template blade renderizado |
| Exportação Excel | Gera XLSX válido; cabeçalhos corretos; dados formatados |

---

#### `Unit Tests` — 19 testes

Testes unitários isolados dos Services.

| Service | O que valida |
|---|---|
| ProductService | CRUD completo sem dependências de HTTP |
| PurchaseService | Cálculo de custo médio isolado |
| SaleService | Cálculo de lucro isolado; validação de estoque |

---

### Trait LazilyRefreshDatabase

- Usado no `TestCase` base para performance
- Roda migrations uma vez, rollback de transações entre testes
- Isolamento de dados entre testes garantido
- FormRequests precisam ter `created_at` no `$fillable` para testes com datas específicas

---

## Status do Projeto

### Funcionalidades Implementadas

- **Backend**: Laravel 12 com 5 módulos funcionais (Product, Purchase, Sale, Dashboard, Report)
- **Frontend**: Vue 3 + TypeScript + Pinia com 6 telas funcionais
- **Autenticação**: Sanctum com tokens Bearer
- **CRUD Completo**: Produtos, Compras, Vendas
- **Regras de Negócio**: Custo médio ponderado, cálculo de lucro, validação de estoque
- **Dashboard**: 6 métricas em tempo real
- **Relatórios**: 4 tipos com exportação PDF/Excel
- **Testes**: 94 testes automatizados (253 assertions) - 100% passando
- **Docker**: Multi-container setup com auto-seed
- **Documentação**: README.md, API docs (Scramble)

### Tecnologias Modernas

- **Backend**: PHP 8.4, Laravel 12, MySQL 8.0
- **Frontend**: Vue 3, TypeScript, Vite, Pinia
- **Qualidade**: 100% testes passando, 0 bugs conhecidos
- **Arquitetura**: Repository Pattern, Service Layer, API Resources
- **DevOps**: Docker Compose, health checks, auto-setup

---

## Licença

Este projeto é um sistema de demonstração para fins educacionais.
