<?php

namespace App\Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Report\Http\Requests\ProfitReportRequest;
use App\Modules\Report\Http\Requests\PurchasesReportRequest;
use App\Modules\Report\Http\Requests\SalesReportRequest;
use App\Modules\Report\Resources\ProfitReportResource;
use App\Modules\Report\Resources\PurchasesReportResource;
use App\Modules\Report\Resources\SalesReportResource;
use App\Modules\Report\Resources\StockReportResource;
use App\Modules\Report\Services\ReportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $service
    ) {}

    /**
     * Relatório de Vendas
     *
     * Retorna relatório de vendas com filtros opcionais por período e cliente.
     *
     * @group Módulo de Relatórios
     *
     * @queryParam start_date string Data inicial (formato: Y-m-d). Example: 2024-01-01
     * @queryParam end_date string Data final (formato: Y-m-d). Example: 2024-12-31
     * @queryParam customer string Nome do cliente (busca parcial). Example: João
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "customer": "Cliente Teste",
     *       "total_amount": "5000.00",
     *       "total_profit": "1200.00",
     *       "items_count": 3,
     *       "created_at": "2024-03-10T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function sales(SalesReportRequest $request): AnonymousResourceCollection
    {
        $data = $this->service->getSalesReport($request->validated());
        return SalesReportResource::collection($data);
    }

    /**
     * Relatório de Compras
     *
     * Retorna relatório de compras com filtros opcionais por período e fornecedor.
     *
     * @group Módulo de Relatórios
     *
     * @queryParam start_date string Data inicial (formato: Y-m-d). Example: 2024-01-01
     * @queryParam end_date string Data final (formato: Y-m-d). Example: 2024-12-31
     * @queryParam supplier string Nome do fornecedor (busca parcial). Example: Fornecedor X
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "supplier": "Fornecedor Teste",
     *       "total_amount": "8000.00",
     *       "items_count": 5,
     *       "created_at": "2024-03-10T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function purchases(PurchasesReportRequest $request): AnonymousResourceCollection
    {
        $data = $this->service->getPurchasesReport($request->validated());
        return PurchasesReportResource::collection($data);
    }

    /**
     * Relatório de Lucro por Produto
     *
     * Retorna análise de lucro por produto com filtros opcionais.
     *
     * @group Módulo de Relatórios
     *
     * @queryParam start_date string Data inicial (formato: Y-m-d). Example: 2024-01-01
     * @queryParam end_date string Data final (formato: Y-m-d). Example: 2024-12-31
     * @queryParam product_id integer ID do produto. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Produto Teste",
     *       "total_quantity_sold": 150,
     *       "total_profit": "3200.00",
     *       "avg_sale_price": "450.00",
     *       "avg_cost": "320.00"
     *     }
     *   ]
     * }
     */
    public function profit(ProfitReportRequest $request): AnonymousResourceCollection
    {
        $data = $this->service->getProfitReport($request->validated());
        return ProfitReportResource::collection($data);
    }

    /**
     * Relatório de Estoque
     *
     * Retorna situação completa do estoque de todos os produtos.
     *
     * @group Módulo de Relatórios
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Produto Teste",
     *       "stock": 25,
     *       "average_cost": "150.00",
     *       "sale_price": "200.00",
     *       "stock_value": "3750.00",
     *       "stock_status": "ESTOQUE NORMAL"
     *     }
     *   ]
     * }
     */
    public function stock(): AnonymousResourceCollection
    {
        $data = $this->service->getStockReport();
        return StockReportResource::collection($data);
    }
}
