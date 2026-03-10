### 🏗️ Fase 1: Infraestrutura (Docker, Setup & Entrypoints)
- [ ] **Setup do Repositório:** Criar pastas `/backend` e `/frontend`.
- [ ] **Docker - Banco de Dados:** Configurar serviço do MySQL no `docker-compose.yml`.
- [ ] **Docker - Backend:** Criar `backend/Dockerfile` para PHP 8.2+ (Laravel 12).
- [ ] **Docker - Frontend:** Criar `frontend/Dockerfile` para Node/Vue.
- [ ] **Entrypoint Backend:** Criar `backend/entrypoint.sh` automatizando `composer install`, `php artisan key:generate`, `php artisan migrate --seed` e o start do servidor.
- [ ] **Entrypoint Frontend:** Criar `frontend/entrypoint.sh` para automatizar `npm install` e `npm run dev`.
- [ ] **Orquestração:** Escrever o `docker-compose.yml` conectando Front, Back e MySQL, utilizando os entrypoints criados.

### 🗄️ Fase 2: Backend Modular (Laravel 12) - Setup, Scramble e Módulo de Produtos
- [ ] **Setup Modular:** Configurar estrutura de módulos no Laravel (ex: pacote `nWidart/laravel-modules` ou diretório `app/Modules`).
- [ ] **Documentação da API:** Instalar e configurar o pacote `dedoc/scramble` para gerar o Swagger/OpenAPI dinamicamente sem necessidade de anotações pesadas.
- [ ] **Módulo `Product`:** Criar estrutura independente com suas próprias Rotas, Controllers, Models, Factories e Migrations.
- [ ] **Migration & Model:** Tabela `products` (`name`, `sale_price`, `stock`, `average_cost`).
- [ ] **Seeder:** Criar `DatabaseSeeder` chamando o `ProductSeeder` para popular produtos iniciais.
- [ ] **API:** Endpoints `POST /api/products` e `GET /api/products`.

### 🧠 Fase 3: Backend Modular - Módulos de Compras, Vendas e Seeders
- [ ] **Módulo `Purchase`:** Estrutura independente para compras.
- [ ] **Migrations & Models (Purchase):** Tabelas `purchases` e `purchase_items`.
- [ ] **Lógica de Entrada:** Endpoint `POST /api/purchases` com transaction para atualizar `stock` e calcular o novo `average_cost` (custo médio ponderado) no model de Product.
- [ ] **Seeder (Purchase):** Criar `PurchaseSeeder` para gerar histórico de compras fictício para os produtos.
- [ ] **Módulo `Sale`:** Estrutura independente para vendas.
- [ ] **Migrations & Models (Sale):** Tabelas `sales` e `sale_items`.
- [ ] **Lógica de Saída:** Endpoint `POST /api/sales` com transaction para validar `stock`, abater quantidade e calcular **lucro da venda** baseado no `average_cost` histórico.
- [ ] **Seeder (Sale):** Criar `SaleSeeder` para gerar histórico de vendas e demonstrar o cálculo de lucros.

### 💻 Fase 4: Frontend Modular (Vue 3 + TS)
- [ ] **Setup Base:** Configurar Vite + Vue + TS, Axios e Vue Router.
- [ ] **Estrutura Core:** Configurar `src/core` para serviços globais (ex: Axios base) e layouts genéricos.
- [ ] **Módulo `Products`:** Criar pasta `src/modules/products` contendo seus próprios `types.ts`, `composables.ts`, `routes.ts` e `views/`.
- [ ] **Módulo `Purchases`:** Criar pasta `src/modules/purchases` para gerenciar formulários de entrada de estoque.
- [ ] **Módulo `Sales`:** Criar pasta `src/modules/sales` para gerenciar saída de estoque e exibição de lucros.
- [ ] **Roteamento Dinâmico:** Importar as rotas de cada módulo no `router/index.ts` principal.

---

### 📋 Roteiro de Prompts para a IA (Arquitetura Modular + Scramble + Seeders)

#### 1. Prompt: Infraestrutura (Docker com Entrypoints)
> Atue como um DevOps Sênior. Preciso criar a estrutura Docker para um projeto Fullstack de ERP desacoplado.
>
> **Stack:** Laravel 12 (Backend), Vue 3 com TypeScript (Frontend), MySQL (Banco de Dados).
> **Estrutura:** `docker-compose.yml` na raiz, `/backend` e `/frontend` com seus respectivos `Dockerfile` e scripts de `entrypoint.sh`.
>
> **Tarefas:**
> 1. Crie o `docker-compose.yml` orquestrando o MySQL, o Backend (porta 8000) e o Frontend (porta 5173).
> 2. Crie o `backend/Dockerfile` compatível com PHP 8.2+ para Laravel 12, instalando extensões do MySQL.
> 3. Crie o `frontend/Dockerfile` usando imagem Node para rodar o Vite.
> 4. Crie o `backend/entrypoint.sh` para rodar automaticamente: `composer install`, cópia do `.env.example` para `.env`, `php artisan key:generate`, `php artisan migrate --seed` e o comando para iniciar o servidor PHP.
> 5. Crie o `frontend/entrypoint.sh` para rodar: `npm install` e `npm run dev -- --host`.
> 6. Certifique-se de que os Dockerfiles dão permissão de execução (`chmod +x`) aos scripts.
>
> Entregue apenas os códigos destes 5 arquivos de infraestrutura.

#### 2. Prompt: Backend - Módulo de Produtos, Scramble e Seeder
> Atue como Arquiteto de Software Backend Laravel 12. O sistema é um ERP com **Arquitetura Modular**. O banco de dados deve ter tabelas em inglês.
>
> **Tarefas:**
> 1. **Documentação:** Forneça os comandos e configurações básicas para instalar o pacote `dedoc/scramble` no Laravel 12 para documentar a API.
> 2. **Módulo Product:** Crie a estrutura para este módulo (Migrations, Models, Controllers, Routes). Tabela `products` (id, name string, sale_price decimal, stock integer default 0, average_cost decimal default 0).
> 3. **Controller:** `ProductController` com `POST /api/products` (name min 3, sale_price positivo) e `GET /api/products`. O Scramble vai ler isso, então garanta FormRequests bem tipados.
> 4. **Seeder & Factory:** Crie um `ProductFactory` e um `ProductSeeder` para inserir 15 produtos fictícios no banco.
>
> Entregue o código focado na organização modular, seeders e configuração do Scramble.

#### 3. Prompt: Backend - Módulo de Compras e Seeder
> Continuando na arquitetura modular do ERP Laravel 12, crie o **Módulo `Purchase`**. Tabelas em inglês.
>
> **Tarefas:**
> 1. **Migrations e Models:** Para `purchases` (supplier string) e `purchase_items` (purchase_id, product_id, quantity integer, unit_price decimal).
> 2. **Controller:** `PurchaseController` e a rota `POST /api/purchases`.
> 3. **Regra de Negócio (Transaction):** Ao salvar, adicione a quantity ao `stock` do `Product` e atualize o `average_cost` (Custo Médio Ponderado). Tipagem estrita na FormRequest para o Scramble entender o payload.
> 4. **Seeder:** Crie um `PurchaseSeeder` que pegue alguns produtos existentes e simule uma compra inicial para já popularmos o `stock` e o `average_cost` deles.
>
> Forneça os arquivos (Migrations, Models, Controller, FormRequest, Seeder).

#### 4. Prompt: Backend - Módulo de Vendas e Seeder
> Agora vamos criar o **Módulo `Sale`** no backend Laravel 12. Tabelas em inglês.
>
> **Tarefas:**
> 1. **Migrations e Models:** Para `sales` (customer string, total_amount decimal, total_profit decimal) e `sale_items` (sale_id, product_id, quantity integer, unit_sale_price decimal, historical_average_cost decimal).
> 2. **Controller:** `SaleController` e rota `POST /api/sales`.
> 3. **Regras de Negócio (Transaction):** Valide `stock` suficiente (retorne erro 422 se faltar). Subtraia o `stock` do `Product`. Calcule o lucro: `(unit_sale_price - current_average_cost) * quantity` e salve o custo médio atual no `historical_average_cost`. Retorne totais.
> 4. **Seeder:** Crie um `SaleSeeder` que pegue produtos que tenham estoque (graças ao PurchaseSeeder) e registre vendas fictícias para termos dados de lucros para testar.
>
> Forneça os códigos deste módulo, incluindo FormRequests e o Seeder.

#### 5. Prompt: Frontend - Setup Core e Estrutura Modular
> Atue como Arquiteto Frontend Vue 3. Vamos configurar o cliente consumindo a API. A stack é Vue 3 (Composition API) + TypeScript + Vite. Usaremos uma **arquitetura orientada a features/módulos**.
>
> **Tarefas:**
> 1. Defina a estrutura de pastas. Teremos `src/core` (para configs globais) e `src/modules` (para as features).
> 2. Crie `src/core/api.ts` configurando o Axios para a porta 8000.
> 3. Crie `src/router/index.ts` preparado para importar as rotas de cada módulo de forma dinâmica.
> 4. Dentro de `src/modules/products`, crie a estrutura básica: `types.ts` (Interface Product em inglês), `routes.ts` e `useProducts.ts` (composable para chamadas à API).
>
> Entregue o código de configuração inicial e a estrutura do módulo de produtos.

#### 6. Prompt: Frontend - Componentes Modulares (Views)
> Para finalizar, crie os componentes de interface (Views) para cada módulo usando `<script setup lang="ts">`.
>
> **Crie as seguintes Views:**
> 1. `src/modules/products/views/ProductsView.vue`: Layout contendo formulário de cadastro e tabela listando produtos (mostrando average cost, sale price e stock).
> 2. `src/modules/purchases/views/PurchaseView.vue`: Formulário dinâmico para registrar compra (supplier + múltiplos itens com quantity e unit price). Deve acionar o endpoint de compras.
> 3. `src/modules/sales/views/SaleView.vue`: Formulário de venda. Permite selecionar produtos, avisa se houver "estoque insuficiente" tratado do erro da API, e exibe o lucro estimado ao concluir.
>
> Forneça os códigos focados na reatividade do Vue e tratamento de estados.