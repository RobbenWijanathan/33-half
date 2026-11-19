<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => 'admin'])->save();
        $this->product = Product::create(['name' => 'Test Record', 'slug' => 'test-record', 'description' => 'An original description.', 'product_type' => 'vinyl', 'price' => 200000, 'stock' => 2]);
    }

    private function cartAndToken(User $user, int $quantity = 1): string
    {
        $this->actingAs($user)->post(route('cart.add', $this->product), ['quantity' => $quantity])->assertSessionHasNoErrors();

        return $this->get('/checkout')->assertOk()->viewData('token');
    }

    public function test_pickup_checkout_is_idempotent_and_keeps_snapshots_and_persistent_cart(): void
    {
        $this->user->changeCredit(150000, 'adjustment', 'Opening balance', 'opening');
        $token = $this->cartAndToken($this->user, 2);
        $this->flushSession();
        $this->get('/cart')->assertOk()->assertSee('Test Record');
        $token = $this->get('/checkout')->viewData('token');
        $data = ['checkout_token' => $token, 'fulfillment' => 'pickup', 'credit_amount' => 100000, 'payment_status' => 'paid', 'total' => 1];
        $this->post('/checkout', $data)->assertSessionHasNoErrors()->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(400000, $order->subtotal);
        $this->assertSame(300000, $order->total);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(50000, $this->user->fresh()->store_credit);
        $this->assertSame(0, $this->product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $this->post('/checkout', $data)->assertRedirect(route('orders.show', $order));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('credit_transactions', 2);
        $this->product->update(['name' => 'Changed Record', 'price' => 999999]);
        $this->product->delete();
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Test Record')->assertSee('Rp 300.000')->assertDontSee('Changed Record');
        $this->get('/orders')->assertOk()->assertSee('Test Record');
        $this->actingAs(User::factory()->create())->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_delivery_uses_owned_address_snapshot_and_tracking_persists(): void
    {
        $address = $this->user->addresses()->create(['label' => 'Home', 'recipient' => 'Listener', 'phone' => '081234', 'street' => 'Old street', 'city' => 'Jakarta', 'province' => 'DKI', 'postal_code' => '12110']);
        $token = $this->cartAndToken($this->user);
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'delivery', 'credit_amount' => 0, 'address_id' => $address->id])->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertSame(225000, $order->total);
        $address->update(['street' => 'New street']);
        $this->assertSame('Old street', $order->fresh()->address_snapshot['street']);
        $this->actingAs($this->admin)->put(route('admin.orders.update', $order), ['status' => 'confirmed', 'note' => 'Confirm'])->assertSessionHasErrors('status');
        $this->post(route('admin.orders.payment', $order), ['note' => 'Checked demo payment REF-1'])->assertSessionHasNoErrors();
        $this->post(route('admin.orders.payment', $order), ['note' => 'Repeat'])->assertSessionHasErrors('payment');
        foreach (['confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered'] as $status) {
            $this->put(route('admin.orders.update', $order), ['status' => $status, 'note' => 'Status '.$status])->assertSessionHasNoErrors();
        }
        $this->patch(route('admin.orders.tracking', $order), ['carrier' => 'Courier', 'tracking_number' => 'ABC123', 'tracking_url' => 'https://example.com/track/ABC123'])->assertSessionHasNoErrors();
        $this->actingAs($this->user)->get(route('orders.show', $order))->assertOk()->assertSee('Old street')->assertSee('ABC123')->assertSee('Out For Delivery');
        $this->get('/delivery-tracking')->assertOk()->assertSee($order->number);
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertDatabaseCount('order_histories', 8);
    }

    public function test_last_stock_cannot_be_sold_to_two_carts_and_failed_checkout_rolls_back(): void
    {
        $this->product->update(['stock' => 1]);
        $other = User::factory()->create();
        $firstToken = $this->cartAndToken($this->user);
        $secondToken = $this->cartAndToken($other);
        $this->actingAs($this->user)->withSession(['checkout_token' => $firstToken])->post('/checkout', ['checkout_token' => $firstToken, 'fulfillment' => 'pickup', 'credit_amount' => 0])->assertSessionHasNoErrors();
        $this->actingAs($other)->withSession(['checkout_token' => $secondToken])->post('/checkout', ['checkout_token' => $secondToken, 'fulfillment' => 'pickup', 'credit_amount' => 0])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, $this->product->fresh()->stock);
        $this->assertSame(1, $other->cartItems()->count());
    }

    public function test_credit_overdraw_and_foreign_addresses_are_rejected_without_reserving_stock(): void
    {
        $token = $this->cartAndToken($this->user);
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'pickup', 'credit_amount' => 1])->assertSessionHasErrors('credit_amount');
        $other = User::factory()->create();
        $address = $other->addresses()->create(['label' => 'Home', 'recipient' => 'Other', 'phone' => '123', 'street' => 'Private', 'city' => 'Jakarta', 'province' => 'DKI', 'postal_code' => '1']);
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'delivery', 'credit_amount' => 0, 'address_id' => $address->id])->assertNotFound();
        $this->assertSame(2, $this->product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_refund_restores_stock_and_credit_once_and_fulfillment_cannot_skip_steps(): void
    {
        $this->user->changeCredit(100000, 'adjustment', 'Opening', 'opening');
        $token = $this->cartAndToken($this->user);
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'pickup', 'credit_amount' => 100000])->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->actingAs($this->admin)->post(route('admin.orders.payment', $order), ['note' => 'Payment checked'])->assertSessionHasNoErrors();
        $this->put(route('admin.orders.update', $order), ['status' => 'picked_up', 'note' => 'Skip'])->assertSessionHasErrors('status');
        $this->put(route('admin.orders.update', $order), ['status' => 'refunded', 'note' => 'Manual payment returned REF-2'])->assertSessionHasNoErrors();
        $this->assertSame(2, $this->product->fresh()->stock);
        $this->assertSame(100000, $this->user->fresh()->store_credit);
        $this->put(route('admin.orders.update', $order), ['status' => 'refunded', 'note' => 'Repeat'])->assertSessionHasErrors('status');
        $this->assertDatabaseCount('credit_transactions', 3);
        $this->assertSame(2, $this->product->fresh()->stock);
    }

    public function test_credit_only_payment_is_decided_on_the_server_and_pickup_completes(): void
    {
        $this->user->changeCredit(200000, 'adjustment', 'Opening', 'opening');
        $token = $this->cartAndToken($this->user);
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'pickup', 'credit_amount' => 200000])->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertSame(0, $order->total);
        $this->assertSame('paid', $order->payment_status);
        foreach (['confirmed', 'ready_for_pickup', 'picked_up'] as $status) {
            $this->actingAs($this->admin)->put(route('admin.orders.update', $order), ['status' => $status, 'note' => 'Pickup progress'])->assertSessionHasNoErrors();
        }
        $this->assertSame('picked_up', $order->fresh()->status);
    }

    public function test_merchandise_variant_uses_its_own_price_and_stock(): void
    {
        $this->product->update(['product_type' => 'merchandise']);
        $variant = $this->product->variants()->create(['name' => 'Large', 'sku' => 'TEST-L', 'price' => 250000, 'stock' => 1]);
        $this->actingAs($this->user)->post(route('cart.add', $this->product), ['quantity' => 1])->assertSessionHasErrors('variant_id');
        $this->post(route('cart.add', $this->product), ['quantity' => 1, 'variant_id' => $variant->id])->assertSessionHasNoErrors();
        $token = $this->get('/checkout')->viewData('token');
        $this->post('/checkout', ['checkout_token' => $token, 'fulfillment' => 'pickup', 'credit_amount' => 0])->assertSessionHasNoErrors();
        $this->assertSame(250000, Order::firstOrFail()->total);
        $this->assertSame(0, $variant->fresh()->stock);
        $this->assertSame(2, $this->product->fresh()->stock);
        $this->assertDatabaseHas('order_items', ['variant_name' => 'Large', 'unit_price' => 250000]);
    }
}
