# 🚀 Roadmap de Implementação: Sênior Level

Plano de ação para elevar o projeto de **57%** para **85%+** do nível Sênior.

---

## 📋 Fase 1: Correções Críticas (Estimativa: 2-3 horas)

### 1.1 Pessimistic Locking (30 min)
**Por quê:** Evitar race conditions no estoque

**Arquivos a modificar:**
- `backend/app/Modules/Sale/Services/SaleService.php`
- `backend/app/Modules/Purchase/Services/PurchaseService.php`

**Mudança:**
```php
// ANTES (linha 36):
$product = Product::findOrFail($item['product_id']);

// DEPOIS:
$product = Product::where('id', $item['product_id'])
    ->lockForUpdate()
    ->firstOrFail();
```

**Teste:**
```php
// Criar teste de concorrência
public function test_concurrent_sales_do_not_create_negative_stock()
{
    // Simular 2 vendas simultâneas do último item
}
```

---

### 1.2 Paginação em Todas as Listagens (45 min)

**Arquivos a modificar:**
- `backend/app/Core/Repositories/BaseRepository.php`
- Todos os Controllers que usam `index()`

**Mudança:**
```php
// BaseRepository.php
public function all()
{
    return $this->model->latest()->paginate(50);
}
```

**Controllers:**
```php
public function index(): JsonResource
{
    $products = $this->service->index();
    return ProductResource::collection($products); // Laravel detecta paginação
}
```

**Frontend (ajustar stores):**
```typescript
// products.ts
async fetchProducts(page = 1) {
  const response = await productsService.getAll(page);
  this.products = response.data.data;
  this.pagination = response.data.meta; // Laravel pagination meta
}
```

---

### 1.3 PHPStan Level 6+ (1 hora)

**Instalação:**
```bash
composer require --dev phpstan/phpstan:^1.10
composer require --dev phpstan/phpstan-laravel:^1.0
```

**Configuração (`phpstan.neon`):**
```neon
includes:
    - vendor/phpstan/phpstan-laravel/extension.neon

parameters:
    level: 6
    paths:
        - app
    excludePaths:
        - app/Console/Kernel.php
```

**Adicionar ao CI:**
```bash
composer phpstan
```

**Corrigir erros encontrados** (estimativa: 30-45 min)

---

## 📋 Fase 2: Performance (Estimativa: 2-3 horas)

### 2.1 Redis Cache (1 hora)

**Instalação:**
```bash
composer require predis/predis
```

**Configuração (`.env`):**
```env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Docker Compose:**
```yaml
redis:
  image: redis:7-alpine
  ports:
    - "6379:6379"
  volumes:
    - redis_data:/data
```

**Implementação:**
```php
// ProductService.php
use Illuminate\Support\Facades\Cache;

public function index(): Collection
{
    return Cache::remember('products.all', 300, function () {
        return $this->repository->all();
    });
}

public function store(array $data): Product
{
    $product = $this->repository->create($data);
    Cache::forget('products.all'); // Invalidar cache
    return $product;
}
```

**Testes:**
```php
public function test_products_are_cached()
{
    Cache::shouldReceive('remember')
        ->once()
        ->andReturn(collect([]));
    
    $this->service->index();
}
```

---

### 2.2 N+1 Prevention Sistemático (45 min)

**Middleware de monitoramento:**
```php
// app/Http/Middleware/QueryCountMiddleware.php
public function handle($request, Closure $next)
{
    if (app()->environment('local')) {
        DB::enableQueryLog();
    }
    
    $response = $next($request);
    
    if (app()->environment('local')) {
        $queries = DB::getQueryLog();
        Log::info('Query Count', ['count' => count($queries)]);
    }
    
    return $response;
}
```

**Garantir eager loading:**
```php
// SaleRepository.php
public function all()
{
    return $this->model->with(['items.product'])->latest()->paginate(50);
}
```

---

### 2.3 API Resources Optimization (30 min)

**Criar Resources condicionais:**
```php
// ProductResource.php
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'sale_price' => number_format($this->sale_price, 2, '.', ''),
        'stock' => $this->stock,
        
        // Apenas se requisitado
        $this->mergeWhen($request->include_costs, [
            'average_cost' => number_format($this->average_cost, 2, '.', ''),
        ]),
        
        // Apenas para show, não para index
        $this->mergeWhen($request->is('*/products/*'), [
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]),
    ];
}
```

---

## 📋 Fase 3: Code Quality (Estimativa: 1-2 horas)

### 3.1 Eliminar 'any' do TypeScript (1 hora)

**Arquivos a corrigir:**

1. **useForm.ts:**
```typescript
// ANTES:
export function useForm<T extends Record<string, any>>(...)

// DEPOIS:
export function useForm<T extends Record<string, unknown>>(...)
```

2. **useDebounceFn.ts:**
```typescript
// ANTES:
export function useDebounceFn<T extends (...args: any[]) => any>(...)

// DEPOIS:
export function useDebounceFn<T extends (...args: unknown[]) => unknown>(...)
```

3. **auth.ts:**
```typescript
// ANTES:
} catch (err: any) {

// DEPOIS:
} catch (err: unknown) {
  this.error = err instanceof Error ? err.message : 'Erro desconhecido';
}
```

4. **api.ts:**
```typescript
// ANTES:
(response: any) => response,

// DEPOIS:
(response: AxiosResponse) => response,
```

---

### 3.2 Cobertura de Testes 80%+ (30 min)

**Configurar Xdebug:**
```yaml
# docker-compose.yml
backend:
  environment:
    XDEBUG_MODE: coverage
```

**phpunit.xml:**
```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">app</directory>
    </include>
    <exclude>
        <directory>app/Console</directory>
    </exclude>
    <report>
        <html outputDirectory="coverage-report"/>
        <text outputFile="php://stdout" showUncoveredFiles="false"/>
    </report>
</coverage>
```

**Executar:**
```bash
php artisan test --coverage --min=80
```

---

## 📋 Fase 4: DevOps (Estimativa: 1-2 horas)

### 4.1 Multi-stage Docker Build (1 hora)

**Backend Dockerfile:**
```dockerfile
# Stage 1: Dependencies
FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Stage 2: Production
FROM php:8.4-fpm-alpine
WORKDIR /var/www/html

COPY --from=deps /app/vendor ./vendor
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
```

**Frontend Dockerfile:**
```dockerfile
# Stage 1: Build
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production
FROM nginx:alpine
COPY --from=builder /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
```

**Benefício:** Imagem final 300MB → 80MB

---

### 4.2 Logs Estruturados (30 min)

**config/logging.php:**
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily', 'structured'],
],

'structured' => [
    'driver' => 'monolog',
    'handler' => StreamHandler::class,
    'formatter' => JsonFormatter::class,
    'with' => [
        'stream' => 'php://stdout',
    ],
],
```

**Uso:**
```php
Log::channel('structured')->info('sale.created', [
    'sale_id' => $sale->id,
    'total_amount' => $sale->total_amount,
    'user_id' => auth()->id(),
]);
```

---

## 📊 Cronograma de Execução

| Fase | Tempo | Prioridade | Impacto |
|------|-------|------------|---------|
| **Fase 1 (Crítico)** | 2-3h | 🔴 Alta | 🚀 Alto |
| **Fase 2 (Performance)** | 2-3h | 🟡 Média | 🚀 Alto |
| **Fase 3 (Quality)** | 1-2h | 🟡 Média | 📊 Médio |
| **Fase 4 (DevOps)** | 1-2h | 🟢 Baixa | 📦 Médio |
| **TOTAL** | **6-10h** | - | - |

---

## ✅ Checklist de Validação

Após implementar, validar:

- [ ] Teste de concorrência passa (2 vendas simultâneas)
- [ ] Listagem com 1000+ produtos não estoura memória
- [ ] PHPStan level 6 sem erros
- [ ] Cache Redis funcionando (verificar com `redis-cli`)
- [ ] Zero 'any' no frontend (verificar com `tsc --noEmit`)
- [ ] Cobertura de testes ≥ 80%
- [ ] Imagem Docker < 100MB
- [ ] Logs em JSON estruturado

---

## 🎯 Meta Final

Após completar todas as fases:

**Score esperado:** **85%+** do nível Sênior

**Tempo total:** 6-10 horas de desenvolvimento focado
