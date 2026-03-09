<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\AuthenticatedTest;


class ProductTest extends TestCase
{
    use DatabaseTransactions;
    use AuthenticatedTest;

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

    public function test_can_show_single_product(): void
    {
        $createResponse = $this->postJson('/api/product/products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
        ]);

        $productId = $createResponse->json('data.id');

        $response = $this->getJson("/api/product/products/{$productId}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $productId,
                    'name' => 'Notebook Dell XPS',
                    'sale_price' => '4500.00',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/product/products/9999');

        $response->assertStatus(404);
    }

    public function test_can_update_product(): void
    {
        $createResponse = $this->postJson('/api/product/products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
        ]);

        $productId = $createResponse->json('data.id');

        $response = $this->putJson("/api/product/products/{$productId}", [
            'name' => 'Notebook Dell XPS 15',
            'sale_price' => 5000.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Notebook Dell XPS 15',
                    'sale_price' => '5000.00',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Notebook Dell XPS 15',
            'sale_price' => 5000.00,
        ]);
    }

    public function test_can_update_product_partially(): void
    {
        $createResponse = $this->postJson('/api/product/products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
        ]);

        $productId = $createResponse->json('data.id');

        $response = $this->putJson("/api/product/products/{$productId}", [
            'sale_price' => 4800.00,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Notebook Dell XPS',
                    'sale_price' => '4800.00',
                ],
            ]);
    }

    public function test_update_returns_404_for_nonexistent_product(): void
    {
        $response = $this->putJson('/api/product/products/9999', [
            'name' => 'Test',
            'sale_price' => 100.00,
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_product(): void
    {
        $createResponse = $this->postJson('/api/product/products', [
            'name' => 'Notebook Dell XPS',
            'sale_price' => 4500.00,
        ]);

        $productId = $createResponse->json('data.id');

        $response = $this->deleteJson("/api/product/products/{$productId}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Produto removido com sucesso',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $productId,
        ]);
    }

    public function test_delete_returns_404_for_nonexistent_product(): void
    {
        $response = $this->deleteJson('/api/product/products/9999');

        $response->assertStatus(404);
    }

    public function test_cannot_create_product_without_name(): void
    {
        $response = $this->postJson('/api/product/products', [
            'sale_price' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_cannot_create_product_without_sale_price(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'Test Product',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    public function test_name_must_be_string(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 123,
            'sale_price' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_sale_price_must_be_numeric(): void
    {
        $response = $this->postJson('/api/product/products', [
            'name' => 'Test Product',
            'sale_price' => 'not-a-number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    public function test_products_are_ordered_by_latest(): void
    {
        $first = $this->postJson('/api/product/products', [
            'name' => 'First Product',
            'sale_price' => 100.00,
        ])->json('data.id');

        sleep(1);

        $second = $this->postJson('/api/product/products', [
            'name' => 'Second Product',
            'sale_price' => 200.00,
        ])->json('data.id');

        $response = $this->getJson('/api/product/products');

        $products = $response->json('data');
        $this->assertEquals($second, $products[0]['id']);
        $this->assertEquals($first, $products[1]['id']);
    }
}
