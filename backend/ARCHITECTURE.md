# Arquitetura em Camadas

Este projeto segue uma arquitetura em camadas para garantir separação de responsabilidades, facilitar testes e manutenção.

## Camadas da Aplicação

### 1. **Controller Layer** (Camada de Controle)
- **Responsabilidade**: Receber requisições HTTP e retornar respostas
- **Localização**: `app/Modules/{Module}/Http/Controllers/`
- **O que FAZ**: Orquestrar o fluxo, chamar o Service
- **O que NÃO FAZ**: Lógica de negócio, acesso direto ao banco

```php
public function index(): AnonymousResourceCollection
{
    $products = $this->service->getAllProducts();
    return ProductResource::collection($products);
}
```

### 2. **Form Request Layer** (Camada de Validação)
- **Responsabilidade**: Validar dados de entrada
- **Localização**: `app/Modules/{Module}/Http/Requests/`
- **O que FAZ**: Validar, autorizar, preparar dados
- **O que NÃO FAZ**: Lógica de negócio

```php
public function rules(): array
{
    return [
        'name' => 'required|string|min:3|max:255',
        'sale_price' => 'required|numeric|min:0.01',
    ];
}
```

### 3. **Service Layer** (Camada de Negócio)
- **Responsabilidade**: Regras de negócio e orquestração
- **Localização**: `app/Modules/{Module}/Services/`
- **O que FAZ**: Lógica de negócio, transações, chamar repositories
- **O que NÃO FAZ**: Acesso direto ao banco, validação

```php
public function createProduct(array $data)
{
    return $this->repository->create($data);
}
```

### 4. **Repository Layer** (Camada de Dados)
- **Responsabilidade**: Comunicação com banco de dados
- **Localização**: `app/Modules/{Module}/Repositories/`
- **O que FAZ**: CRUD, queries, persistência
- **O que NÃO FAZ**: Lógica de negócio

```php
class ProductRepository extends BaseRepository
{
    // Herda métodos: all(), find(), create(), update(), delete()
}
```

### 5. **Resource Layer** (Camada de Apresentação)
- **Responsabilidade**: Formatar dados para resposta
- **Localização**: `app/Modules/{Module}/Resources/`
- **O que FAZ**: Transformar model em JSON, formatar dados
- **O que NÃO FAZ**: Lógica de negócio

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'sale_price' => number_format((float) $this->sale_price, 2, '.', ''),
    ];
}
```

## Estrutura de um Módulo

```
app/Modules/{ModuleName}/
├── Http/
│   ├── Controllers/
│   │   └── {Module}Controller.php       # Orquestração
│   └── Requests/
│       ├── Store{Module}Request.php     # Validação de criação
│       └── Update{Module}Request.php    # Validação de atualização
├── Services/
│   └── {Module}Service.php              # Regras de negócio
├── Repositories/
│   └── {Module}Repository.php           # Acesso ao banco
├── Resources/
│   └── {Module}Resource.php             # Formatação de resposta
├── Models/
│   └── {Module}.php                     # Eloquent Model
├── Database/
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
└── Routes/
    └── api.php
```

## Base Repository

Todos os repositories estendem `BaseRepository` para evitar código duplicado:

```php
// app/Core/Repositories/BaseRepository.php
abstract class BaseRepository implements RepositoryInterface
{
    public function all() { }           // Buscar todos
    public function find(int $id) { }   // Buscar por ID
    public function create(array $data) { }   // Criar
    public function update(int $id, array $data) { } // Atualizar
    public function delete(int $id) { } // Deletar
}
```

## Fluxo de uma Requisição

```
HTTP Request
    ↓
1. Controller (recebe request)
    ↓
2. FormRequest (valida dados)
    ↓
3. Service (aplica regras de negócio)
    ↓
4. Repository (persiste no banco)
    ↓
5. Model (Eloquent)
    ↓
6. Resource (formata resposta)
    ↓
HTTP Response (JSON)
```

## Exemplo Completo

### Controller
```php
public function store(StoreProductRequest $request): ProductResource
{
    $product = $this->service->createProduct($request->validated());
    return new ProductResource($product);
}
```

### Service
```php
public function createProduct(array $data)
{
    // Aqui entrariam regras de negócio complexas
    return $this->repository->create($data);
}
```

### Repository
```php
public function create(array $data)
{
    return $this->model->create($data);
}
```

### Resource
```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'sale_price' => number_format((float) $this->sale_price, 2, '.', ''),
    ];
}
```

## Vantagens

✅ **Separação de Responsabilidades**: Cada camada tem um papel claro
✅ **Testabilidade**: Fácil de mockar e testar isoladamente
✅ **Manutenibilidade**: Mudanças localizadas em uma camada
✅ **Reutilização**: BaseRepository elimina código duplicado
✅ **Escalabilidade**: Fácil adicionar novos módulos

## Próximos Módulos

Ao criar novos módulos (Purchase, Sale), siga a mesma estrutura:
1. Controller → Service → Repository → Resource
2. Extends BaseRepository para métodos comuns
3. Service contém todas as regras de negócio
