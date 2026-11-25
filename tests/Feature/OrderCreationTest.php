<?php

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_successfully_and_deduct_stock_and_dispatch_job(): void
    {
        Queue::fake();

        $product1 = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'code' => 'CLG-101',
            'price_per_unit' => 50.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 10,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Parle-G Biscuit',
            'code' => 'PRL-102',
            'price_per_unit' => 10.00,
            'tax_percentage' => 8.00,
            'stock_on_hand' => 20,
        ]);

        $payload = [
            'customer_name' => 'Thomas Mathew',
            'customer_email' => 'thomas@example.com',
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 5],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully.',
            ])
            ->assertJsonPath('data.customer.email', 'thomas@example.com')
            // Line 1: 50 * 2 = 100 subtotal, 8 tax = 108
            // Line 2: 10 * 5 = 50 subtotal, 4 tax = 54
            // Subtotal = 150, Tax = 12, Grand total = 162
            ->assertJsonPath('data.subtotal', '150.00')
            ->assertJsonPath('data.tax_amount', '12.00')
            ->assertJsonPath('data.grand_total', '162.00');

        // Check stock was deducted accurately
        $this->assertEquals(8, $product1->fresh()->stock_on_hand);
        $this->assertEquals(15, $product2->fresh()->stock_on_hand);

        // Check customer was created
        $this->assertDatabaseHas('customers', [
            'email' => 'thomas@example.com',
            'name' => 'Thomas Mathew',
        ]);

        // Check queued job was dispatched
        Queue::assertPushed(SendOrderConfirmationEmail::class, 1);
    }

    public function test_fails_cleanly_when_insufficient_stock(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'name' => 'Bread',
            'code' => 'BRD-201',
            'price_per_unit' => 40.00,
            'stock_on_hand' => 2,
        ]);

        $payload = [
            'customer_name' => 'Thomas Mathew',
            'customer_email' => 'thomas@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5], // requesting 5 when only 2 available
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'message' => "Insufficient stock for 'Bread' (Code: BRD-201). Available: 2, Requested: 5.",
            ]);

        // Stock should remain unchanged
        $this->assertEquals(2, $product->fresh()->stock_on_hand);

        // No orders created in DB
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);

        // No job dispatched
        Queue::assertNotPushed(SendOrderConfirmationEmail::class);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name', 'customer_email', 'items']);
    }

    public function test_customer_order_history_endpoint(): void
    {
        $customer = Customer::factory()->create(['email' => 'ananya@example.com']);
        $product = Product::factory()->create(['stock_on_hand' => 20]);

        $this->postJson('/api/v1/orders', [
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/customers/orders?email=ananya@example.com');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'found' => true,
            ])
            ->assertJsonPath('customer.email', 'ananya@example.com')
            ->assertJsonCount(1, 'orders');
    }

    public function test_low_stock_products_endpoint(): void
    {
        Product::factory()->create(['name' => 'Low Stock Item', 'stock_on_hand' => 3]);
        Product::factory()->create(['name' => 'High Stock Item', 'stock_on_hand' => 50]);

        $response = $this->getJson('/api/v1/products/low-stock?threshold=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'threshold' => 5,
            ])
            ->assertJsonFragment(['name' => 'Low Stock Item'])
            ->assertJsonMissing(['name' => 'High Stock Item']);
    }
}
