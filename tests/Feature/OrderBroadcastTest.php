<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_order_is_persisted_and_broadcast(): void
    {
        Event::fake([OrderPlaced::class]);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct(stock: 8);

        $response = $this->actingAs($cashier)->postJson(route('kasir.transaksi.store'), [
            'status' => 'completed',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'payment_amount' => 15000,
        ]);

        $order = Order::query()->sole();

        $response
            ->assertCreated()
            ->assertJson([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => 'completed',
                'total' => 10000,
            ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 5000,
            'quantity' => 2,
            'subtotal' => 10000,
        ]);
        $this->assertSame($cashier->id, $order->user_id);
        $this->assertSame(6, $product->fresh()->stock);
        Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use ($order, $cashier): bool {
            return $event->order->is($order)
                && $event->user->is($cashier)
                && $event->broadcastOn()->name === 'private-user.'.$cashier->id
                && $event->broadcastWith() === [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => 10000,
                    'user_name' => $cashier->name,
                ];
        });
    }

    public function test_held_transaction_is_saved_without_stock_change_or_broadcast(): void
    {
        Event::fake([OrderPlaced::class]);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct(stock: 1);

        $response = $this->actingAs($cashier)->postJson(route('kasir.transaksi.store'), [
            'status' => 'held',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('status', 'held');
        $this->assertDatabaseHas('orders', ['status' => 'held', 'total' => 15000]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(1, $product->fresh()->stock);
        Event::assertNotDispatched(OrderPlaced::class);
    }

    public function test_cashier_dashboard_has_transaksi_route_and_notification_markup(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($cashier)->get(route('kasir.dashboard'));

        $response
            ->assertOk()
            ->assertSee('id="order-form"', false)
            ->assertSee(route('kasir.transaksi.store'), false)
            ->assertSee('data-auth-user-id="'.$cashier->id.'"', false)
            ->assertSee('id="order-notification"', false)
            ->assertSee('id="order-item-template"', false)
            ->assertSee('Tahan transaksi', false);
    }

    public function test_completed_transaction_requires_sufficient_payment(): void
    {
        Event::fake([OrderPlaced::class]);
        $cashier = User::factory()->create(['role' => 'kasir']);
        $product = $this->createProduct();

        $response = $this->actingAs($cashier)->postJson(route('kasir.transaksi.store'), [
            'status' => 'completed',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
            'payment_amount' => 4999,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('payment_amount');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);
        Event::assertNotDispatched(OrderPlaced::class);
    }

    private function createProduct(int $stock = 10): Product
    {
        $category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Air Mineral',
            'sku' => 'AIR-001',
            'price' => 5000,
            'stock' => $stock,
        ]);
    }
}
