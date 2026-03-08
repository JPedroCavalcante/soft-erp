<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sale_price',
                    'stock',
                    'average_cost',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
            'stock' => 0,
            'average_cost' => 0,
        ]);
    }

    public function test_cannot_create_product_with_invalid_name(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'AB',
            'sale_price' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_product_with_negative_price(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'Test Product',
            'sale_price' => -10.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    public function test_cannot_create_product_with_zero_price(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'Test Product',
            'sale_price' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    public function test_can_list_products(): void
    {
        $this->postJson('/api/product/products', [
            'name' => 'Product 1',
            'sale_price' => 100.00,
        ]);

        $this->postJson('/api/product/products', [
            'name' => 'Product 2',
            'sale_price' => 200.00,
        ]);

        $response = $this->getJson('/api/product/products');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
