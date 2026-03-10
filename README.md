# 🏢 Soft ERP

Sistema ERP modular construído com Laravel 12 e Vue 3, implementando controle de estoque com custo médio ponderado e análise de lucros em tempo real.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript)
![Docker](https://img.shields.io/badge/Docker-✓-2496ED?logo=docker)
![Tests](https://img.shields.io/badge/Tests-18%20passed-success)

---

## 📋 Índice

- [Sobre](#sobre)
- [Tecnologias](#tecnologias)
- [Funcionalidades](#funcionalidades)
- [Arquitetura](#arquitetura)
- [Como Rodar](#como-rodar)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [API Documentation](#api-documentation)
- [Testes](#testes)
- [Decisões Técnicas](#decisões-técnicas)

---

## 🎯 Sobre

O **Soft ERP** é um sistema completo de gestão empresarial focado em:

- **Controle de Produtos** - CRUD completo com gestão de preços e estoque
- **Gestão de Compras** - Registro de compras com **cálculo automático de custo médio ponderado**
- **Gestão de Vendas** - Controle de vendas com **validação de estoque** e **cálculo de lucro em tempo real**

### Diferenciais

✅ **Custo Médio Ponderado** - Cálculo preciso do custo dos produtos
✅ **Validação em Camadas** - Frontend (UX) + Backend (Segurança)
✅ **Type Safety** - TypeScript strict + PHP 8.4
✅ **API-First** - Documentação automática com Scramble
✅ **Arquitetura Modular** - Escalável e manutenível
✅ **Testes Automatizados** - 18 testes cobrindo funcionalidades críticas

---

## 🚀 Tecnologias

### Backend
- **Laravel 12** - Framework PHP moderno
- **PHP 8.4** - Última versão com strict types
- **MySQL 8.0** - Banco de dados relacional
- **Scramble** - Documentação automática de API (OpenAPI/Swagger)
- **PHPUnit** - Testes automatizados

### Frontend
- **Vue 3** - Framework progressivo JavaScript
- **TypeScript 5** - Superset tipado do JavaScript
- **Vite 5.4** - Build tool ultra-rápido
- **Pinia** - State management oficial do Vue 3
- **Vue Router 4** - Roteamento SPA
- **Axios** - Cliente HTTP

### Infraestrutura
- **Docker** - Containerização
- **Docker Compose** - Orquestração de containers

---

## ⚙️ Funcionalidades

### 1. Gestão de Produtos
- ✅ Criar, listar, editar e excluir produtos
- ✅ Campos: nome, preço de venda, estoque, custo médio
- ✅ Validação: nome mínimo 3 caracteres, preço positivo
- ✅ Badge de estoque (OK, baixo, zerado)

### 2. Gestão de Compras
- ✅ Registrar compras de múltiplos produtos
- ✅ Campos: fornecedor, itens (produto, quantidade, preço unitário)
- ✅ **Cálculo automático de custo médio ponderado**
- ✅ Atualização automática de estoque
- ✅ Formulário dinâmico (adicionar/remover itens)

### 3. Gestão de Vendas
- ✅ Registrar vendas de múltiplos produtos
- ✅ Campos: cliente, itens (produto, quantidade, preço venda)
- ✅ **Validação de estoque disponível** (frontend + backend)
- ✅ **Cálculo automático de lucro**
- ✅ Suporte a lucro negativo (venda abaixo do custo)
- ✅ Decremento automático de estoque

---

## 🏗️ Arquitetura

### Backend - Modular Monolith

**Padrão de camadas:**
```
Request → Controller → Service → Repository → Model
                 ↓
              Resource
```

### Frontend - Feature-Based Modules

**Fluxo de dados:**
```
Component → Store → Service → API → Backend
```

---

## 🐳 Como Rodar

### Pré-requisitos

- Docker Desktop instalado
- Portas livres: `3306` (MySQL), `8000` (Backend), `5173` (Frontend)

### Passo a Passo

1. **Clone o repositório**

2. **Suba os containers:**
   ```bash
   docker-compose up -d
   ```

3. **Aguarde a inicialização** (30-60 segundos)

4. **Acesse a aplicação:**
   - **Frontend:** http://localhost:5173
   - **Backend API:** http://localhost:8000/api
   - **API Docs:** http://localhost:8000/docs/api

### Comandos Úteis

```bash
docker-compose down -v                              # Parar e remover volumes
docker-compose logs backend                         # Ver logs
docker-compose exec backend php artisan test        # Rodar testes
```

---

## 📚 API Documentation

A API está **100% documentada** usando **Scramble** (OpenAPI/Swagger).

**Acesse:** http://localhost:8000/docs/api

---

## 🧪 Testes

### Executar Testes

```bash
docker-compose exec backend php artisan test
```

### Resultado

```
✅ 18 testes PASSARAM
✅ 45 assertions
⏱️  1.09s
🐛 0 bugs críticos
```

**Ver relatório completo:** [docs/TEST_REPORT.md](docs/TEST_REPORT.md)

---

## 💡 Decisões Técnicas

### 1. Por que Arquitetura Modular?

✅ Código organizado por contexto
✅ Fácil localizar funcionalidades
✅ Escalável
✅ Testável

### 2. Por que Custo Médio Ponderado?

✅ Mais preciso para controle financeiro
✅ Aceito pela contabilidade brasileira
✅ Reflete valor real do estoque

### 3. Por que Pinia?

✅ Oficial do Vue 3
✅ TypeScript-first
✅ API mais simples

### 4. Por que Validação Dupla?

**Frontend:** UX + feedback imediato
**Backend:** Segurança + última linha de defesa

---

**Feito com ❤️ usando Laravel + Vue**
