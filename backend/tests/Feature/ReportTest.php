<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use App\Modules\Purchase\Models\Purchase;
use App\Modules\Purchase\Models\PurchaseItem;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Models\SaleItem;
use Tests\AuthenticatedTest;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use AuthenticatedTest;

    public function test_sales_report_with_date_filter(): void
    {
        $product = Product::create(['name' => 'Produto 1', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);

        Sale::create([
            'customer' => 'Cliente 1',
            'total_amount' => 1000,
            'total_profit' => 200,
            'created_at' => '2024-03-10',
        ]);

        Sale::create([
            'customer' => 'Cliente 2',
            'total_amount' => 2000,
            'total_profit' => 400,
            'created_at' => '2024-04-10',
        ]);

        $response = $this->getJson('/api/report/reports/sales?start_date=2024-03-01&end_date=2024-03-31');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Cliente 1', $response->json('data.0.customer'));
    }

    public function test_sales_report_with_customer_filter(): void
    {
        Sale::create([
            'customer' => 'João Silva',
            'total_amount' => 1000,
            'total_profit' => 200,
            'created_at' => now(),
        ]);

        Sale::create([
            'customer' => 'Maria Santos',
            'total_amount' => 2000,
            'total_profit' => 400,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/report/reports/sales?customer=João');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('João Silva', $response->json('data.0.customer'));
    }

    public function test_purchases_report_with_supplier_filter(): void
    {
        $product = Product::create(['name' => 'Produto 1', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);

        Purchase::create([
            'supplier' => 'Fornecedor A',
            'total_amount' => 5000,
            'created_at' => now(),
        ]);

        Purchase::create([
            'supplier' => 'Fornecedor B',
            'total_amount' => 3000,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/report/reports/purchases?supplier=Fornecedor A');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Fornecedor A', $response->json('data.0.supplier'));
    }

    public function test_profit_report_groups_by_product(): void
    {
        $product1 = Product::create(['name' => 'Produto Alto Lucro', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 50]);
        $product2 = Product::create(['name' => 'Produto Baixo Lucro', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 90]);

        $sale1 = Sale::create(['customer' => 'Cliente 1', 'total_amount' => 1000, 'total_profit' => 500, 'created_at' => now()]);
        $sale2 = Sale::create(['customer' => 'Cliente 2', 'total_amount' => 500, 'total_profit' => 50, 'created_at' => now()]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_sale_price' => 100,
            'historical_average_cost' => 50,
            'profit' => 500,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_sale_price' => 100,
            'historical_average_cost' => 90,
            'profit' => 50,
        ]);

        $response = $this->getJson('/api/report/reports/profit');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals('Produto Alto Lucro', $response->json('data.0.name'));
        $this->assertEquals('500.00', $response->json('data.0.total_profit'));
    }

    public function test_stock_report_shows_all_products(): void
    {
        Product::create(['name' => 'Produto Sem Estoque', 'sale_price' => 100, 'stock' => 0, 'average_cost' => 80]);
        Product::create(['name' => 'Produto Estoque Baixo', 'sale_price' => 100, 'stock' => 5, 'average_cost' => 80]);
        Product::create(['name' => 'Produto Estoque Alto', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);

        $response = $this->getJson('/api/report/reports/stock');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $this->assertEquals('Produto Sem Estoque', $response->json('data.0.name'));
        $this->assertEquals('SEM ESTOQUE', $response->json('data.0.stock_status'));
        $this->assertEquals('ESTOQUE BAIXO', $response->json('data.1.stock_status'));
        $this->assertEquals('ESTOQUE ALTO', $response->json('data.2.stock_status'));
    }

    public function test_invalid_date_range_returns_422(): void
    {
        $response = $this->getJson('/api/report/reports/sales?start_date=2024-12-31&end_date=2024-01-01');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date']);
    }

    public function test_profit_report_with_product_filter(): void
    {
        $product1 = Product::create(['name' => 'Produto 1', 'sale_price' => 100, 'stock' => 100, 'average_cost' => 80]);
        $product2 = Product::create(['name' => 'Produto 2', 'sale_price' => 200, 'stock' => 100, 'average_cost' => 150]);

        $sale = Sale::create(['customer' => 'Cliente', 'total_amount' => 1000, 'total_profit' => 200, 'created_at' => now()]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_sale_price' => 100,
            'historical_average_cost' => 80,
            'profit' => 200,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_sale_price' => 200,
            'historical_average_cost' => 150,
            'profit' => 250,
        ]);

        $response = $this->getJson("/api/report/reports/profit?product_id={$product1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Produto 1', $response->json('data.0.name'));
    }

    public function test_export_pdf_returns_file(): void
    {
        Product::create(['name' => 'Produto 1', 'sale_price' => 100, 'stock' => 10, 'average_cost' => 80]);

        $response = $this->postJson('/api/report/reports/export', [
            'type' => 'pdf',
            'report_name' => 'stock',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_excel_returns_file(): void
    {
        Sale::create([
            'customer' => 'Cliente Teste',
            'total_amount' => 1000,
            'total_profit' => 200,
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/report/reports/export', [
            'type' => 'excel',
            'report_name' => 'sales',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_requires_type_and_report_name(): void
    {
        $response = $this->postJson('/api/report/reports/export', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type', 'report_name']);
    }
}
