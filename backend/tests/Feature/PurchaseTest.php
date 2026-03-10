<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use Tests\TestCase;
use Tests\AuthenticatedTest;


class PurchaseTest extends TestCase
{
    use AuthenticatedTest;

    public function test_purchase_updates_stock_and_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 0,
            'average_cost' => 0,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Dell Distribuidora',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 3000.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertEquals(10, $product->stock);
        $this->assertEquals('3000.00', number_format($product->average_cost, 2, '.', ''));
    }

    public function test_multiple_purchases_calculate_weighted_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 0,
            'average_cost' => 0,
        ]);

        $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Dell Distribuidora',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 3000.00,
                ],
            ],
        ]);

        $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Dell Brasil',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 3300.00,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(15, $product->stock);

        $expectedAverage = (3000 * 10 + 3300 * 5) / 15;
        $this->assertEquals(
            number_format($expectedAverage, 2, '.', ''),
            number_format($product->average_cost, 2, '.', '')
        );
    }

    public function test_cannot_create_purchase_with_empty_supplier(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => '',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['supplier']);
    }

    public function test_cannot_create_purchase_with_invalid_product_id(): void
    {
        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => 9999,
                    'quantity' => 1,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_purchase_with_zero_quantity(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_single_purchase(): void
    {
        $product = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
        ]);

        $createResponse = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Dell Distribuidora',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 3000.00,
                ],
            ],
        ]);

        $purchaseId = $createResponse->json('data.id');

        $response = $this->getJson("/api/purchase/purchases/{$purchaseId}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $purchaseId,
                    'supplier' => 'Dell Distribuidora',
                    'total_amount' => '30000.00',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => [
                            'product_id',
                            'quantity',
                            'unit_price',
                            'product',
                        ],
                    ],
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_purchase(): void
    {
        $response = $this->getJson('/api/purchase/purchases/9999');

        $response->assertStatus(404);
    }

    public function test_purchase_with_multiple_items_updates_all_products(): void
    {
        $product1 = Product::create([
            'name' => 'Notebook',
            'sale_price' => 4500.00,
            'stock' => 0,
            'average_cost' => 0,
        ]);

        $product2 = Product::create([
            'name' => 'Mouse',
            'sale_price' => 150.00,
            'stock' => 0,
            'average_cost' => 0,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Dell Distribuidora',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 10,
                    'unit_price' => 3000.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 50,
                    'unit_price' => 80.00,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(10, $product1->stock);
        $this->assertEquals('3000.00', number_format($product1->average_cost, 2, '.', ''));

        $this->assertEquals(50, $product2->stock);
        $this->assertEquals('80.00', number_format($product2->average_cost, 2, '.', ''));

        $expectedTotal = (3000 * 10) + (80 * 50);
        $this->assertEquals(
            number_format($expectedTotal, 2, '.', ''),
            number_format($response->json('data.total_amount'), 2, '.', '')
        );
    }

    public function test_cannot_create_purchase_with_negative_quantity(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => -5,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_purchase_with_negative_price(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => -50.00,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_purchase_with_zero_price(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 0,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_purchase_without_items(): void
    {
        $response = $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Test Supplier',
            'items' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_can_list_purchases(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Supplier 1',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $this->postJson('/api/purchase/purchases', [
            'supplier' => 'Supplier 2',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 45.00,
                ],
            ],
        ]);

        $response = $this->getJson('/api/purchase/purchases');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
