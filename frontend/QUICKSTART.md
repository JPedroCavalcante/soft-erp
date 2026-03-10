# Quick Start - Frontend Soft ERP

Guia rápido para rodar o frontend Vue 3 + TypeScript.

## Pré-requisitos

- Node.js 18+ instalado
- Backend Laravel rodando em `http://localhost:8000`
- NPM ou Yarn

## Instalação

```bash
cd /Users/jpcavalcante/Work/testes/soft-erp/frontend

# Instalar dependências
npm install
```

## Desenvolvimento

```bash
# Rodar servidor de desenvolvimento
npm run dev
```

Acesse: http://localhost:5173

## Testar Integração com Backend

1. Certifique-se de que o backend Laravel está rodando em `localhost:8000`
2. Acesse http://localhost:5173/test-api
3. Clique em "Testar Conexão com /product/products"
4. Deve exibir a lista de produtos da API

## Verificar TypeScript

```bash
# Compilar TypeScript sem gerar output
npx vue-tsc --noEmit
```

Deve compilar sem erros.

## Build de Produção

```bash
# Gerar build otimizado
npm run build

# Preview do build
npm run preview
```

Build gerado em `/dist`.

## Estrutura Básica

```
src/
├── core/               # Infraestrutura
│   ├── api.ts         # Cliente Axios
│   ├── types.ts       # Tipos globais
│   └── layouts/       # Layouts
├── modules/           # Módulos de features (vazio)
├── router/            # Vue Router
├── views/             # Views standalone
├── App.vue            # Componente raiz
└── main.ts            # Entry point
```

## Navegação

- **/** → Redireciona para `/products`
- **/test-api** → Teste de conexão API
- **/products** → (futuro) Lista de produtos
- **/purchases** → (futuro) Lista de compras
- **/sales** → (futuro) Lista de vendas

## Tecnologias

- Vue 3.4.21 (Composition API)
- TypeScript 5.4.2 (Strict Mode)
- Vue Router 4.3.0
- Axios 1.6.7
- Vite 5.1.5

## Troubleshooting

### Porta 5173 em uso

```bash
# Matar processo na porta
lsof -ti:5173 | xargs kill -9

# Ou alterar porta em vite.config.ts
server: {
  port: 3000  // Nova porta
}
```

### Erro de conexão com API

1. Verifique se o backend está rodando: `curl http://localhost:8000/api/product/products`
2. Verifique o CORS no backend Laravel
3. Verifique a baseURL em `src/core/api.ts`

### TypeScript errors

```bash
# Limpar cache do TypeScript
rm -rf node_modules/.vite
rm -rf dist

# Reinstalar
npm install
```

### Hot Module Replacement não funciona (Docker)

Já configurado em `vite.config.ts`:
```typescript
watch: {
  usePolling: true  // Habilitado para Docker
}
```

## Próximos Passos

1. Implementar módulo Products
2. Implementar módulo Purchases
3. Implementar módulo Sales
4. Adicionar biblioteca de componentes UI (opcional)
5. Implementar autenticação (futuro)

## Documentação Adicional

- `README.md` - Documentação completa
- `VALIDATION.md` - Checklist de validação
- `STRUCTURE.md` - Estrutura visual do projeto
- `src/modules/README.md` - Guia de criação de módulos

## Comandos Úteis

```bash
# Desenvolvimento
npm run dev

# Build
npm run build

# Preview build
npm run preview

# Type check
npx vue-tsc --noEmit

# Limpar node_modules e reinstalar
rm -rf node_modules package-lock.json && npm install
```

## Status

✅ Projeto configurado e pronto para desenvolvimento!
