<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Models\SaleItem;
use Tests\AuthenticatedTest;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use AuthenticatedTest;

    public function test_dashboard_metrics_returns_all_data(): void
    {
        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'sales_this_month',
                'profit_this_month',
                'low_stock_products',
                'top_selling_products',
                'sales_comparison' => ['today', 'yesterday', 'change_percentage'],
                'total_stock_value',
            ],
        ]);
    }

    public function test_sales_this_month_calculation(): void
    {
        Sale::create([
            'customer' => 'Cliente Teste',
            'total_amount' => 1000.00,
            'total_profit' => 200.00,
            'created_at' => now(),
        ]);

        Sale::create([
            'customer' => 'Cliente Teste 2',
            'total_amount' => 1500.00,
            'total_profit' => 300.00,
            'created_at' => now(),
        ]);

        Sale::create([
            'customer' => 'Cliente Mês Passado',
            'total_amount' => 5000.00,
            'total_profit' => 1000.00,
            'created_at' => now()->subMonth(),
        ]);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $this->assertEquals('2500.00', $response->json('data.sales_this_month'));
        $this->assertEquals('500.00', $response->json('data.profit_this_month'));
    }

    public function test_low_stock_products_filter(): void
    {
        Product::create(['name' => 'Produto Estoque Alto', 'sale_price' => 100, 'stock' => 50, 'average_cost' => 80]);
        Product::create(['name' => 'Produto Estoque Baixo 1', 'sale_price' => 100, 'stock' => 5, 'average_cost' => 80]);
        Product::create(['name' => 'Produto Estoque Baixo 2', 'sale_price' => 100, 'stock' => 3, 'average_cost' => 80]);
        Product::create(['name' => 'Produto Sem Estoque', 'sale_price' => 100, 'stock' => 0, 'average_cost' => 80]);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $lowStockProducts = $response->json('data.low_stock_products');

        $this->assertCount(3, $lowStockProducts);
        $this->assertEquals('Produto Sem Estoque', $lowStockProducts[0]['name']);
        $this->assertEquals(0, $lowStockProducts[0]['stock']);
    }

    public function test_top_selling_products_order(): void
    {
        $product1 = Product::create(['name' => 'Produto Mais Vendido', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);
        $product2 = Product::create(['name' => 'Produto Menos Vendido', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);

        $sale1 = Sale::create(['customer' => 'Cliente 1', 'total_amount' => 1000, 'total_profit' => 200]);
        $sale2 = Sale::create(['customer' => 'Cliente 2', 'total_amount' => 500, 'total_profit' => 100]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_sale_price' => 100,
            'historical_average_cost' => 80,
            'profit' => 200,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'unit_sale_price' => 100,
            'historical_average_cost' => 80,
            'profit' => 60,
        ]);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $topProducts = $response->json('data.top_selling_products');

        $this->assertCount(2, $topProducts);
        $this->assertEquals('Produto Mais Vendido', $topProducts[0]['name']);
        $this->assertEquals(10, $topProducts[0]['total_quantity']);
    }

    public function test_sales_comparison_calculation(): void
    {
        Sale::create([
            'customer' => 'Venda Hoje',
            'total_amount' => 1000.00,
            'total_profit' => 200.00,
            'created_at' => now(),
        ]);

        Sale::create([
            'customer' => 'Venda Ontem',
            'total_amount' => 800.00,
            'total_profit' => 150.00,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $comparison = $response->json('data.sales_comparison');

        $this->assertEquals('1000.00', $comparison['today']);
        $this->assertEquals('800.00', $comparison['yesterday']);
        $this->assertEquals(25.0, $comparison['change_percentage']);
    }

    public function test_total_stock_value_calculation(): void
    {
        Product::create(['name' => 'Produto 1', 'sale_price' => 100, 'stock' => 10, 'average_cost' => 80]);
        Product::create(['name' => 'Produto 2', 'sale_price' => 200, 'stock' => 5, 'average_cost' => 150]);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200);
        $expectedValue = 10 * 80 + 5 * 150;
        $this->assertEquals(number_format($expectedValue, 2, '.', ''), $response->json('data.total_stock_value'));
    }
}
