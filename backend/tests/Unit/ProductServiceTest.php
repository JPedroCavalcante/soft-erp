<?php

namespace Tests\Unit;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepository;
use App\Modules\Product\Services\ProductService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductService::class);
    }

    public function test_index_returns_all_products(): void
    {
        Product::create(['name' => 'Product 1', 'sale_price' => 100.00]);
        Product::create(['name' => 'Product 2', 'sale_price' => 200.00]);
        Product::create(['name' => 'Product 3', 'sale_price' => 300.00]);

        $products = $this->service->index();

        $this->assertCount(3, $products);
    }

    public function test_store_creates_product_with_zero_stock_and_cost(): void
    {
        $data = [
            'name' => 'New Product',
            'sale_price' => 250.00,
        ];

        $product = $this->service->store($data);

        $this->assertEquals('New Product', $product->name);
        $this->assertEquals(250.00, $product->sale_price);
        $this->assertEquals(0, $product->stock);
        $this->assertEquals(0, $product->average_cost);
    }

    public function test_show_returns_product(): void
    {
        $created = Product::create([
            'name' => 'Test Product',
            'sale_price' => 150.00,
        ]);

        $product = $this->service->show($created->id);

        $this->assertEquals($created->id, $product->id);
        $this->assertEquals('Test Product', $product->name);
    }

    public function test_update_modifies_product(): void
    {
        $product = Product::create([
            'name' => 'Old Name',
            'sale_price' => 100.00,
        ]);

        $updated = $this->service->update($product->id, [
            'name' => 'New Name',
            'sale_price' => 150.00,
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals(150.00, $updated->sale_price);
    }

    public function test_delete_removes_product(): void
    {
        $product = Product::create([
            'name' => 'To Delete',
            'sale_price' => 100.00,
        ]);

        $this->service->delete($product->id);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_products_are_ordered_by_latest(): void
    {
        $first = Product::create(['name' => 'First', 'sale_price' => 100.00]);
        sleep(1);
        $second = Product::create(['name' => 'Second', 'sale_price' => 200.00]);

        $products = $this->service->index();

        $this->assertEquals($second->id, $products->first()->id);
        $this->assertEquals($first->id, $products->last()->id);
    }
}
