<?php

namespace App\Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sale\Http\Requests\StoreSaleRequest;
use App\Modules\Sale\Resources\SaleResource;
use App\Modules\Sale\Services\SaleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de Vendas
 *
 * Gerencia o processo de vendas, controle de estoque e cálculo de lucros.
 *
 * @group Módulo de Vendas
 */
class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $service
    ) {}

    /**
     * Listar todas as vendas
     *
     * Retorna a lista completa de vendas ordenadas por data de criação (mais recentes primeiro).
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "customer": "Maria Silva Santos",
     *       "total_amount": "3500.00",
     *       "total_profit": "850.00",
     *       "items": [
     *         {
     *           "id": 1,
     *           "product_id": 1,
     *           "product": "Notebook Dell Inspiron",
     *           "quantity": 2,
     *           "unit_sale_price": "1750.00",
     *           "historical_average_cost": "1500.00",
     *           "profit": "500.00"
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
        $sales = $this->service->index();
        return SaleResource::collection($sales);
    }

    /**
     * Criar nova venda
     *
     * Cadastra uma nova venda no sistema. Valida e decrementa o estoque dos produtos vendidos.
     * Calcula automaticamente o lucro com base no custo médio histórico de cada produto.
     * O lucro pode ser negativo caso o preço de venda seja inferior ao custo médio.
     *
     * @bodyParam customer string required Nome do cliente. Mínimo 3 caracteres. Example: Maria Silva Santos
     * @bodyParam items array required Lista de items da venda. Mínimo 1 item.
     * @bodyParam items[].product_id integer required ID do produto. Example: 1
     * @bodyParam items[].quantity integer required Quantidade vendida. Mínimo 1. Example: 2
     * @bodyParam items[].unit_sale_price numeric required Preço de venda unitário. Deve ser maior que zero. Example: 1750.00
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "customer": "Maria Silva Santos",
     *     "total_amount": "3500.00",
     *     "total_profit": "500.00",
     *     "items": [
     *       {
     *         "id": 1,
     *         "product_id": 1,
     *         "product": "Notebook Dell Inspiron",
     *         "quantity": 2,
     *         "unit_sale_price": "1750.00",
     *         "historical_average_cost": "1500.00",
     *         "profit": "500.00"
     *       }
     *     ],
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Estoque insuficiente para Notebook Dell Inspiron. Disponível: 1, solicitado: 2"
     * }
     *
     * @response 422 {
     *   "message": "Os dados fornecidos são inválidos.",
     *   "errors": {
     *     "customer": ["O nome do cliente é obrigatório."],
     *     "items": ["É necessário incluir ao menos um item na venda."]
     *   }
     * }
     */
    public function store(StoreSaleRequest $request): SaleResource|JsonResponse
    {
        try {
            $sale = $this->service->store($request->validated());
            return new SaleResource($sale);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Exibir venda específica
     *
     * Retorna os detalhes de uma venda específica pelo ID, incluindo seus items e informações de lucro.
     *
     * @urlParam id integer required ID da venda. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "customer": "Maria Silva Santos",
     *     "total_amount": "3500.00",
     *     "total_profit": "500.00",
     *     "items": [
     *       {
     *         "id": 1,
     *         "product_id": 1,
     *         "product": "Notebook Dell Inspiron",
     *         "quantity": 2,
     *         "unit_sale_price": "1750.00",
     *         "historical_average_cost": "1500.00",
     *         "profit": "500.00"
     *       }
     *     ],
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Venda não encontrada."
     * }
     */
    public function show(int $id): SaleResource
    {
        $sale = $this->service->show($id);
        return new SaleResource($sale);
    }

    /**
     * Cancelar venda
     *
     * Cancela uma venda existente e reverte o estoque dos produtos vendidos.
     * A venda não é excluída do banco de dados, apenas marcada como cancelada.
     *
     * @urlParam id integer required ID da venda. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "customer": "Maria Silva Santos",
     *     "total_amount": "3500.00",
     *     "total_profit": "500.00",
     *     "canceled_at": "2024-01-02T10:30:00.000000Z",
     *     "items": [
     *       {
     *         "id": 1,
     *         "product_id": 1,
     *         "product": "Notebook Dell Inspiron",
     *         "quantity": 2,
     *         "unit_sale_price": "1750.00",
     *         "historical_average_cost": "1500.00",
     *         "profit": "500.00"
     *       }
     *     ],
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-02T10:30:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Esta venda já foi cancelada."
     * }
     *
     * @response 404 {
     *   "message": "Venda não encontrada."
     * }
     */
    public function cancel(int $id): SaleResource|JsonResponse
    {
        try {
            $sale = $this->service->cancel($id);
            return new SaleResource($sale);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Venda excluída com sucesso'], 200);
    }
}
