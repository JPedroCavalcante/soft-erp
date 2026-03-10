# Módulo Purchase - Sistema ERP

## Descrição

Módulo completo de gerenciamento de compras do sistema ERP. Implementa o fluxo de entrada de produtos no estoque com cálculo automático de custo médio ponderado.

## Arquitetura

O módulo segue a arquitetura em camadas padrão do sistema:

```
Controller → FormRequest → Service → Repository → Model
```

## Estrutura de Diretórios

```
app/Modules/Purchase/
├── Database/
│   ├── Migrations/
│   │   ├── 2026_03_08_164758_create_purchases_table.php
│   │   └── 2026_03_08_164800_create_purchase_items_table.php
│   ├── Seeders/
│   │   └── PurchaseSeeder.php
│   └── Factories/
├── Http/
│   ├── Controllers/
│   │   └── PurchaseController.php
│   └── Requests/
│       └── StorePurchaseRequest.php
├── Services/
│   └── PurchaseService.php
├── Repositories/
│   └── PurchaseRepository.php
├── Resources/
│   ├── PurchaseResource.php
│   └── PurchaseItemResource.php
├── Models/
│   ├── Purchase.php
│   └── PurchaseItem.php
└── Routes/
    └── api.php
```

## Banco de Dados

### Tabela: purchases

| Campo        | Tipo           | Descrição                    |
|--------------|----------------|------------------------------|
| id           | bigint         | Chave primária               |
| supplier     | varchar(255)   | Nome do fornecedor           |
| total_amount | decimal(10,2)  | Valor total da compra        |
| created_at   | timestamp      | Data de criação              |
| updated_at   | timestamp      | Data de atualização          |

### Tabela: purchase_items

| Campo       | Tipo          | Descrição                          |
|-------------|---------------|------------------------------------|
| id          | bigint        | Chave primária                     |
| purchase_id | bigint        | FK para purchases (cascade)        |
| product_id  | bigint        | FK para products (cascade)         |
| quantity    | integer       | Quantidade comprada                |
| unit_price  | decimal(10,2) | Preço unitário de compra           |
| created_at  | timestamp     | Data de criação                    |
| updated_at  | timestamp     | Data de atualização                |

**Índice:** (purchase_id, product_id)

## Funcionalidades

### 1. Listar Compras
- **Endpoint:** `GET /api/purchases`
- **Descrição:** Lista todas as compras ordenadas por data (mais recentes primeiro)

### 2. Criar Compra
- **Endpoint:** `POST /api/purchases`
- **Descrição:** Cria uma nova compra e atualiza o estoque dos produtos
- **Body:**
```json
{
  "supplier": "Fornecedor ABC Ltda",
  "items": [
    {
      "product_id": 1,
      "quantity": 10,
      "unit_price": 150.50
    }
  ]
}
```

### 3. Exibir Compra
- **Endpoint:** `GET /api/purchases/{id}`
- **Descrição:** Exibe detalhes de uma compra específica com seus items

## Cálculo de Custo Médio Ponderado

A cada compra, o sistema recalcula automaticamente o custo médio do produto usando a fórmula:

```
Novo Custo Médio = (Custo Atual × Estoque Atual + Preço Novo × Quantidade Nova) / (Estoque Atual + Quantidade Nova)
```

**Exemplo:**
- Produto atual: Estoque = 20, Custo Médio = R$ 100,00
- Nova compra: Quantidade = 10, Preço Unitário = R$ 130,00
- Novo Custo Médio = (100 × 20 + 130 × 10) / (20 + 10) = R$ 110,00

## Validações

### StorePurchaseRequest

- **supplier:** obrigatório, string, mínimo 3 caracteres, máximo 255
- **items:** obrigatório, array, mínimo 1 item
- **items.*.product_id:** obrigatório, integer, deve existir na tabela products
- **items.*.quantity:** obrigatório, integer, mínimo 1
- **items.*.unit_price:** obrigatório, numérico, mínimo 0.01

## Models e Relacionamentos

### Purchase
```php
$purchase->items(); // HasMany PurchaseItem
```

### PurchaseItem
```php
$purchaseItem->purchase(); // BelongsTo Purchase
$purchaseItem->product();  // BelongsTo Product
```

## Resources

### PurchaseResource
Retorna dados da compra formatados com items incluídos (eager loading).

### PurchaseItemResource
Retorna dados do item com nome do produto quando disponível.

## Seeder

O `PurchaseSeeder` cria 5 compras de exemplo com:
- 2-3 items por compra
- Produtos entre ID 1-10
- Quantidades entre 5-20
- Preços unitários entre R$ 95,00 e R$ 490,00
- Atualização automática de estoque e custo médio

## Uso

### Executar Migrations
```bash
php artisan migrate
```

### Executar Seeder
```bash
php artisan db:seed --class=App\\Modules\\Purchase\\Database\\Seeders\\PurchaseSeeder
```

### Testar Endpoints

**Listar compras:**
```bash
curl http://localhost:8000/api/purchases
```

**Criar compra:**
```bash
curl -X POST http://localhost:8000/api/purchases \
  -H "Content-Type: application/json" \
  -d '{
    "supplier": "Fornecedor Teste",
    "items": [
      {
        "product_id": 1,
        "quantity": 5,
        "unit_price": 120.00
      }
    ]
  }'
```

**Visualizar compra:**
```bash
curl http://localhost:8000/api/purchases/1
```

## Padrões Seguidos

- PSR-12 para estilo de código
- Laravel 10+ conventions
- Repository Pattern
- Service Layer Pattern
- API Resources para transformação de dados
- Form Requests para validação
- Eager Loading para performance
- Database Transactions para integridade
- Array syntax em validações
- Documentação em português
- Type hints em todos os métodos
