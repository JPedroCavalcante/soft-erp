<?php

namespace Tests\Unit;

use App\Modules\Product\Models\Product;
use App\Modules\Purchase\Models\Purchase;
use App\Modules\Purchase\Services\PurchaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PurchaseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PurchaseService::class);
    }

    public function test_store_creates_purchase_with_items(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
        ]);

        $data = [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 50.00,
                ],
            ],
        ];

        $purchase = $this->service->store($data);

        $this->assertEquals('Test Supplier', $purchase->supplier);
        $this->assertEquals(500.00, $purchase->total_amount);
        $this->assertCount(1, $purchase->items);
    }

    public function test_store_calculates_total_amount_correctly(): void
    {
        $product1 = Product::create(['name' => 'Product 1', 'sale_price' => 100.00]);
        $product2 = Product::create(['name' => 'Product 2', 'sale_price' => 200.00]);

        $data = [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 5,
                    'unit_price' => 40.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 3,
                    'unit_price' => 120.00,
                ],
            ],
        ];

        $purchase = $this->service->store($data);

        $expectedTotal = (5 * 40) + (3 * 120);
        $this->assertEquals($expectedTotal, $purchase->total_amount);
    }

    public function test_store_updates_product_stock(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 100.00,
            'stock' => 5,
        ]);

        $data = [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 50.00,
                ],
            ],
        ];

        $this->service->store($data);

        $product->refresh();
        $this->assertEquals(15, $product->stock);
    }

    public function test_store_calculates_weighted_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'sale_price' => 200.00,
            'stock' => 10,
            'average_cost' => 80.00,
        ]);

        $data = [
            'supplier' => 'Test Supplier',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $this->service->store($data);

        $product->refresh();

        $expectedAverage = ((80 * 10) + (100 * 5)) / 15;
        $this->assertEquals(
            number_format($expectedAverage, 2, '.', ''),
            number_format($product->average_cost, 2, '.', '')
        );
    }

    public function test_show_returns_purchase_with_items(): void
    {
        $product = Product::create(['name' => 'Test Product', 'sale_price' => 100.00]);

        $purchase = Purchase::create([
            'supplier' => 'Test Supplier',
            'total_amount' => 500.00,
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 50.00,
        ]);

        $result = $this->service->show($purchase->id);

        $this->assertEquals($purchase->id, $result->id);
        $this->assertTrue($result->relationLoaded('items'));
        $this->assertCount(1, $result->items);
    }
}
