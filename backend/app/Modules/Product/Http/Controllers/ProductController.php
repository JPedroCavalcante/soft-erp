<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\StoreProductRequest;
use App\Modules\Product\Http\Requests\UpdateProductRequest;
use App\Modules\Product\Resources\ProductResource;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de Produtos
 *
 * Gerencia o catálogo de produtos e inventário.
 *
 * @group Módulo de Produtos
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service
    ) {}

    /**
     * Listar todos os produtos
     *
     * Retorna a lista completa de produtos ordenados por data de criação (mais recentes primeiro).
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Notebook Dell Inspiron",
     *       "sale_price": "2499.90",
     *       "stock": 15,
     *       "average_cost": "1800.00",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(): AnonymousResourceCollection
    {
        $products = $this->service->index();
        return ProductResource::collection($products);
    }

    /**
     * Criar novo produto
     *
     * Cadastra um novo produto no sistema. O estoque e custo médio são iniciados em zero.
     *
     * @bodyParam name string required Nome do produto. Mínimo 3 caracteres. Example: Notebook Dell Inspiron
     * @bodyParam sale_price numeric required Preço de venda do produto. Deve ser maior que zero. Example: 2499.90
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Notebook Dell Inspiron",
     *     "sale_price": "2499.90",
     *     "stock": 0,
     *     "average_cost": "0.00",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Os dados fornecidos são inválidos.",
     *   "errors": {
     *     "name": ["O campo nome é obrigatório."],
     *     "sale_price": ["O campo preço de venda deve ser maior que 0."]
     *   }
     * }
     */
    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->service->store($request->validated());
        return new ProductResource($product);
    }

    /**
     * Exibir produto específico
     *
     * Retorna os detalhes de um produto específico pelo ID.
     *
     * @urlParam id integer required ID do produto. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Notebook Dell Inspiron",
     *     "sale_price": "2499.90",
     *     "stock": 15,
     *     "average_cost": "1800.00",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Produto não encontrado."
     * }
     */
    public function show(int $id): ProductResource
    {
        $product = $this->service->show($id);
        return new ProductResource($product);
    }

    /**
     * Atualizar produto
     *
     * Atualiza as informações de um produto existente.
     *
     * @urlParam id integer required ID do produto. Example: 1
     * @bodyParam name string required Nome do produto. Mínimo 3 caracteres. Example: Notebook Dell Inspiron 15
     * @bodyParam sale_price numeric required Preço de venda do produto. Example: 2599.90
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Notebook Dell Inspiron 15",
     *     "sale_price": "2599.90",
     *     "stock": 15,
     *     "average_cost": "1800.00",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-02T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Produto não encontrado."
     * }
     *
     * @response 422 {
     *   "message": "Os dados fornecidos são inválidos.",
     *   "errors": {
     *     "name": ["O campo nome deve ter no mínimo 3 caracteres."]
     *   }
     * }
     */
    public function update(int $id, UpdateProductRequest $request): ProductResource
    {
        $product = $this->service->update($id, $request->validated());
        return new ProductResource($product);
    }

    /**
     * Remover produto
     *
     * Remove um produto do sistema.
     *
     * @urlParam id integer required ID do produto. Example: 1
     *
     * @response 200 {
     *   "message": "Produto removido com sucesso"
     * }
     *
     * @response 404 {
     *   "message": "Produto não encontrado."
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Produto removido com sucesso'], 200);
    }
}
