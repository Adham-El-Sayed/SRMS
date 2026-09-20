<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order(): void
    {
        $table = RestaurantTable::create([
            'number' => 'TEST-1',
            'capacity' => 4,
            'status' => 'available',
            'qr_token' => 'test-qr-token-' . uniqid(),
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Burger',
            'description' => 'Test product',
            'price' => 135,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/orders', [
            'restaurant_table_id' => $table->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('order.status', 'pending')
            ->assertJsonPath('order.total', '270.00');

        $this->assertDatabaseHas('orders', [
            'restaurant_table_id' => $table->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 135,
            'subtotal' => 270,
        ]);
    }

    public function test_can_update_order_status(): void
    {
        $table = $this->createTable();

        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'status' => 'pending',
            'total' => 270,
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/status",
            [
                'status' => 'confirmed',
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'confirmed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_cannot_make_invalid_status_transition(): void
    {
        $order = Order::create([
            'restaurant_table_id' => $this->createTable()->id,
            'status' => 'preparing',
            'total' => 270,
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/status",
            [
                'status' => 'pending',
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot change order status from preparing to pending.',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'preparing',
        ]);
    }

    public function test_confirming_order_marks_table_as_occupied(): void
    {
        $table = $this->createTable();

        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'status' => 'pending',
            'total' => 270,
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/status",
            [
                'status' => 'confirmed',
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'confirmed')
            ->assertJsonPath('order.table.status', 'occupied');

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'status' => 'occupied',
        ]);
    }

    public function test_completing_order_marks_table_as_available(): void
    {
        $table = $this->createTable();

        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'status' => 'ready',
            'total' => 270,
        ]);

        $table->update([
            'status' => 'occupied',
        ]);

        $response = $this->patchJson(
            "/api/orders/{$order->id}/status",
            [
                'status' => 'completed',
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'completed')
            ->assertJsonPath('order.table.status', 'available');

        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'status' => 'available',
        ]);
    }
    public function test_table_remains_occupied_when_another_active_order_exists(): void
{
    $table = $this->createTable();

    // First order is still active
    $activeOrder = Order::create([
        'restaurant_table_id' => $table->id,
        'status' => 'preparing',
        'total' => 200,
    ]);

    // Second order will be completed
    $completedOrder = Order::create([
        'restaurant_table_id' => $table->id,
        'status' => 'ready',
        'total' => 150,
    ]);

    // Table is currently occupied
    $table->update([
        'status' => 'occupied',
    ]);

    // Complete the second order
    $response = $this->patchJson(
        "/api/orders/{$completedOrder->id}/status",
        [
            'status' => 'completed',
        ]
    );

    $response
        ->assertStatus(200)
        ->assertJsonPath('order.status', 'completed')
        ->assertJsonPath('order.table.status', 'occupied');

    // The table must remain occupied because another
    // active order still exists.
    $this->assertDatabaseHas('restaurant_tables', [
        'id' => $table->id,
        'status' => 'occupied',
    ]);
}
    public function test_cannot_create_order_for_occupied_table(): void
{
    $table = RestaurantTable::create([
        'number' => 'OCCUPIED-' . uniqid(),
        'capacity' => 4,
        'status' => 'occupied',
        'qr_token' => 'test-qr-token-' . uniqid(),
    ]);

    $category = Category::create([
        'name' => 'Test Category',
        'description' => 'Test category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test Burger',
        'description' => 'Test product',
        'price' => 135,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/orders', [
        'restaurant_table_id' => $table->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
            ],
        ],
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'This table is not available for new orders.',
        ]);

    $this->assertDatabaseMissing('orders', [
        'restaurant_table_id' => $table->id,
    ]);
}

    public function test_cannot_create_order_for_reserved_table(): void
{
    $table = RestaurantTable::create([
        'number' => 'RESERVED-' . uniqid(),
        'capacity' => 4,
        'status' => 'reserved',
        'qr_token' => 'test-qr-token-' . uniqid(),
    ]);

    $category = Category::create([
        'name' => 'Test Category',
        'description' => 'Test category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test Burger',
        'description' => 'Test product',
        'price' => 135,
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/orders', [
        'restaurant_table_id' => $table->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
            ],
        ],
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'This table is not available for new orders.',
        ]);

    $this->assertDatabaseMissing('orders', [
        'restaurant_table_id' => $table->id,
    ]);
}

    private function createTable(): RestaurantTable
    {
        return RestaurantTable::create([
            'number' => 'TEST-' . uniqid(),
            'capacity' => 4,
            'status' => 'available',
            'qr_token' => 'test-qr-token-' . uniqid(),
        ]);
    }

    public function test_cannot_create_order_with_inactive_product(): void
{
    $table = $this->createTable();

    $category = Category::create([
        'name' => 'Test Category',
        'description' => 'Test category',
        'is_active' => true,
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Inactive Burger',
        'description' => 'This product is inactive',
        'price' => 135,
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/orders', [
        'restaurant_table_id' => $table->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
            ],
        ],
    ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'One or more selected products are not available.',
        ]);

    $this->assertDatabaseCount('orders', 0);

    $this->assertDatabaseCount('order_items', 0);
}
}
