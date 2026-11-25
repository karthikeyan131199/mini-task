<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyStockProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_safety_when_only_one_unit_is_left(): void
    {
        $product = Product::factory()->create([
            'name' => 'Rare Collector Item',
            'code' => 'RARE-01',
            'price_per_unit' => 100.00,
            'tax_percentage' => 10.00,
            'stock_on_hand' => 1, // EXACTLY 1 UNIT LEFT
        ]);

        $orderService = app(OrderService::class);

        $customer1 = ['name' => 'Customer Alpha', 'email' => 'alpha@example.com'];
        $customer2 = ['name' => 'Customer Beta', 'email' => 'beta@example.com'];

        $itemPayload = [
            ['product_id' => $product->id, 'quantity' => 1]
        ];

        // Customer 1 attempts purchase
        $order1 = $orderService->createOrder($customer1, $itemPayload);
        $this->assertNotNull($order1);
        $this->assertEquals(0, $product->fresh()->stock_on_hand);

        // Customer 2 attempts purchase of the same product at nearly the same time
        $this->expectException(InsufficientStockException::class);
        $orderService->createOrder($customer2, $itemPayload);
    }
}
