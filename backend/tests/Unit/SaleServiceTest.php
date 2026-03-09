<?php

namespace Tests\Unit;

use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Services\SaleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SaleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SaleService::class);
    }

    public function test_store_creates_sale_with_items(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 50.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $sale = $this->service->store($data);

        $this->assertEquals('Test Customer', $sale->customer);
        $this->assertEquals(500.00, $sale->total_amount);
        $this->assertCount(1, $sale->items);
    }

    public function test_store_calculates_total_amount_correctly(): void
    {
        $product1 = Product::create([
            'name' => 'Product 1',
            'sale_price' => 100.00,
            'stock' => 50,
            'average_cost' => 40.00,
        ]);

        $product2 = Product::create([
            'name' => 'Product 2',
            'sale_price' => 200.00,
            'stock' => 30,
            'average_cost' => 120.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 3,
                    'unit_sale_price' => 200.00,
                ],
            ],
        ];

        $sale = $this->service->store($data);

        $expectedTotal = (5 * 100) + (3 * 200);
        $this->assertEquals($expectedTotal, $sale->total_amount);
    }

    public function test_store_decreases_product_stock(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 50.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $this->service->store($data);

        $product->refresh();
        $this->assertEquals(15, $product->stock);
    }

    public function test_store_calculates_profit_correctly(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 60.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $sale = $this->service->store($data);

        $expectedProfit = (100 - 60) * 5;
        $this->assertEquals($expectedProfit, $sale->total_profit);
    }

    public function test_store_does_not_change_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 60.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $this->service->store($data);

        $product->refresh();
        $this->assertEquals(60.00, $product->average_cost);
    }

    public function test_store_throws_exception_on_insufficient_stock(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 3,
            'average_cost' => 50.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Estoque insuficiente');

        $this->service->store($data);
    }

    public function test_store_stores_historical_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 60.00,
        ]);

        $data = [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ];

        $sale = $this->service->store($data);

        $saleItem = $sale->items->first();
        $this->assertEquals(60.00, $saleItem->historical_average_cost);
    }

    public function test_show_returns_sale_with_items(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 20,
            'average_cost' => 50.00,
        ]);

        $sale = Sale::create([
            'customer' => 'Test Customer',
            'total_amount' => 500.00,
            'total_profit' => 250.00,
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_sale_price' => 100.00,
            'historical_average_cost' => 50.00,
            'profit' => 250.00,
        ]);

        $result = $this->service->show($sale->id);

        $this->assertEquals($sale->id, $result->id);
        $this->assertTrue($result->relationLoaded('items'));
        $this->assertCount(1, $result->items);
    }
}
