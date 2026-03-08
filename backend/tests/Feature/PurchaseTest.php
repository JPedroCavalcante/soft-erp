<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

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
}
