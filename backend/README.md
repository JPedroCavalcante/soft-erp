# Soft ERP - Backend API

Sistema ERP modular construído com Laravel 12 e arquitetura modular.

## Stack
- **Laravel 12** (PHP 8.4)
- **MySQL 8.0**
- **Scramble** - Documentação automática da API

## Estrutura Modular

Módulos localizados em `app/Modules/{ModuleName}/`:

```
app/Modules/{ModuleName}/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Database/
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
└── Routes/
    └── api.php
```

### Como Criar um Novo Módulo

1. Crie a estrutura de diretórios em `app/Modules/{ModuleName}`
2. Crie o arquivo `Routes/api.php`:
```php
<?php
use Illuminate\Support\Facades\Route;

Route::prefix('modulename')->group(function () {
    // Suas rotas aqui
});
```

3. As rotas são carregadas automaticamente de `routes/api.php`
4. As migrations são descobertas automaticamente pelo `AppServiceProvider`

## Endpoints Disponíveis

- `GET /api/health` - Health check
- `GET /docs/api` - Documentação da API (Scramble)
- `GET /api/core/settings` - Módulo Core (exemplo)

## Configuração

### Variáveis de Ambiente
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=soft_erp
DB_USERNAME=soft_erp_user
DB_PASSWORD=soft_erp_password
```

### Comandos Úteis
```bash
# Migrations
php artisan migrate

# Seeders
php artisan db:seed

# Limpar cache
php artisan cache:clear

# Criar migration para módulo
php artisan make:migration create_table_name --path=app/Modules/ModuleName/Database/Migrations
```

## API Documentation
Acesse `http://localhost:8000/docs/api` para ver a documentação interativa gerada automaticamente pelo Scramble.
