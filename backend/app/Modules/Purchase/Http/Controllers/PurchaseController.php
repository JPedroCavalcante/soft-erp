<?php

namespace App\Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Purchase\Http\Requests\StorePurchaseRequest;
use App\Modules\Purchase\Resources\PurchaseResource;
use App\Modules\Purchase\Services\PurchaseService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controlador de Compras
 *
 * Gerencia o processo de compras e entrada de produtos no estoque.
 *
 * @group Módulo de Compras
 */
class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $service
    ) {}

    /**
     * Listar todas as compras
     *
     * Retorna a lista completa de compras ordenadas por data de criação (mais recentes primeiro).
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "supplier": "Fornecedor ABC Ltda",
     *       "total_amount": "15000.00",
     *       "items": [
     *         {
     *           "id": 1,
     *           "product_id": 1,
     *           "product": "Notebook Dell Inspiron",
     *           "quantity": 10,
     *           "unit_price": "1500.00"
     *         }
     *       ],
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(): AnonymousResourceCollection
    {
        $purchases = $this->service->index();
        return PurchaseResource::collection($purchases);
    }

    /**
     * Criar nova compra
     *
     * Cadastra uma nova compra no sistema. Atualiza automaticamente o estoque e o custo médio dos produtos.
     * Utiliza o método de custo médio ponderado para calcular o novo custo do produto.
     *
     * @bodyParam supplier string required Nome do fornecedor. Mínimo 3 caracteres. Example: Fornecedor ABC Ltda
     * @bodyParam items array required Lista de items da compra. Mínimo 1 item.
     * @bodyParam items[].product_id integer required ID do produto. Example: 1
     * @bodyParam items[].quantity integer required Quantidade comprada. Mínimo 1. Example: 10
     * @bodyParam items[].unit_price numeric required Preço unitário de compra. Deve ser maior que zero. Example: 1500.00
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "supplier": "Fornecedor ABC Ltda",
     *     "total_amount": "15000.00",
     *     "items": [
     *       {
     *         "id": 1,
     *         "product_id": 1,
     *         "product": "Notebook Dell Inspiron",
     *         "quantity": 10,
     *         "unit_price": "1500.00"
     *       }
     *     ],
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Os dados fornecidos são inválidos.",
     *   "errors": {
     *     "supplier": ["O nome do fornecedor é obrigatório."],
     *     "items": ["É necessário incluir ao menos um item na compra."]
     *   }
     * }
     */
    public function store(StorePurchaseRequest $request): PurchaseResource
    {
        $purchase = $this->service->store($request->validated());
        return new PurchaseResource($purchase);
    }

    /**
     * Exibir compra específica
     *
     * Retorna os detalhes de uma compra específica pelo ID, incluindo seus items.
     *
     * @urlParam id integer required ID da compra. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "supplier": "Fornecedor ABC Ltda",
     *     "total_amount": "15000.00",
     *     "items": [
     *       {
     *         "id": 1,
     *         "product_id": 1,
     *         "product": "Notebook Dell Inspiron",
     *         "quantity": 10,
     *         "unit_price": "1500.00"
     *       }
     *     ],
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Compra não encontrada."
     * }
     */
    public function show(int $id): PurchaseResource
    {
        $purchase = $this->service->show($id);
        return new PurchaseResource($purchase);
    }
}
