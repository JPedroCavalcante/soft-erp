<?php

namespace Tests\Feature;

use App\Modules\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

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
}
