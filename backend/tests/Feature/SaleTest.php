<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\AuthenticatedTest;


class SaleTest extends TestCase
{
    use DatabaseTransactions;
    use AuthenticatedTest;

    public function test_sale_decreases_stock_and_calculates_profit(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 15,
            'average_cost' => 3100.00,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Empresa ABC Ltda',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_sale_price' => 4500.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertEquals(12, $product->stock);
        $this->assertEquals('3100.00', number_format($product->average_cost, 2, '.', ''));

        $expectedProfit = (4500 - 3100) * 3;
        $sale = $response->json('data');
        $this->assertEquals(
            number_format($expectedProfit, 2, '.', ''),
            number_format($sale['total_profit'], 2, '.', '')
        );
    }

    public function test_sale_fails_with_insufficient_stock(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 5,
            'average_cost' => 3000.00,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_sale_price' => 4500.00,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $product->refresh();
        $this->assertEquals(5, $product->stock);
    }

    public function test_sale_calculates_negative_profit(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 10,
            'average_cost' => 3000.00,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 2500.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $expectedProfit = (2500 - 3000) * 5;
        $sale = $response->json('data');
        $this->assertEquals(
            number_format($expectedProfit, 2, '.', ''),
            number_format($sale['total_profit'], 2, '.', '')
        );
    }

    public function test_cannot_create_sale_with_empty_customer(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => '',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer']);
    }

    public function test_cannot_create_sale_with_zero_quantity(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_sale_does_not_change_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 20,
            'average_cost' => 3000.00,
        ]);

        $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 4500.00,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(15, $product->stock);
        $this->assertEquals('3000.00', number_format($product->average_cost, 2, '.', ''));
    }

    public function test_can_show_single_sale(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 20,
            'average_cost' => 3000.00,
        ]);

        $createResponse = $this->postJson('/api/sale/sales', [
            'customer' => 'Empresa ABC Ltda',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_sale_price' => 4500.00,
                ],
            ],
        ]);

        $saleId = $createResponse->json('data.id');

        $response = $this->getJson("/api/sale/sales/{$saleId}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $saleId,
                    'customer' => 'Empresa ABC Ltda',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'product_id',
                            'quantity',
                            'unit_sale_price',
                            'historical_average_cost',
                            'profit',
                            'product',
                        ],
                    ],
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->getJson('/api/sale/sales/9999');

        $response->assertStatus(404);
    }

    public function test_sale_with_multiple_items_updates_all_products(): void
    {
        $product1 = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 20,
            'average_cost' => 3000.00,
        ]);

        $product2 = Product::create([
            'name' => 'Mouse',
            'sale_price' => 150.00,
            'stock' => 50,
            'average_cost' => 80.00,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Empresa ABC Ltda',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 3,
                    'unit_sale_price' => 4500.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 10,
                    'unit_sale_price' => 150.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(17, $product1->stock);
        $this->assertEquals('3000.00', number_format($product1->average_cost, 2, '.', ''));

        $this->assertEquals(40, $product2->stock);
        $this->assertEquals('80.00', number_format($product2->average_cost, 2, '.', ''));

        $expectedProfit1 = (4500 - 3000) * 3;
        $expectedProfit2 = (150 - 80) * 10;
        $expectedTotalProfit = $expectedProfit1 + $expectedProfit2;

        $this->assertEquals(
            number_format($expectedTotalProfit, 2, '.', ''),
            number_format($response->json('data.total_profit'), 2, '.', '')
        );
    }

    public function test_cannot_create_sale_with_negative_quantity(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => -5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_sale_with_negative_price(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => -100.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_sale_with_zero_price(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 0,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_sale_without_items(): void
    {
        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_create_sale_with_invalid_product_id(): void
    {
        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => 9999,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_sales(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 100,
            'average_cost' => 50.00,
        ]);

        $this->postJson('/api/sale/sales', [
            'customer' => 'Customer 1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $this->postJson('/api/sale/sales', [
            'customer' => 'Customer 2',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_sale_price' => 100.00,
                ],
            ],
        ]);

        $response = $this->getJson('/api/sale/sales');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_sale_stores_historical_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 20,
            'average_cost' => 3000.00,
        ]);

        $response = $this->postJson('/api/sale/sales', [
            'customer' => 'Test Customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit_sale_price' => 4500.00,
                ],
            ],
        ]);

        $saleItem = $response->json('data.items.0');
        $this->assertEquals('3000.00', number_format($saleItem['historical_average_cost'], 2, '.', ''));
    }
}
