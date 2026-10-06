<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    public function test_the_main_pages_and_product_details_render(): void
    {
        foreach (['/', '/shop', '/vinyl', '/merch', '/artists', '/genres', '/new-arrivals', '/pre-orders', '/crate-digging', '/about', '/contact', '/cart'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/records/afterimage')->assertOk()->assertSee('Afterimage');
        $this->get('/merch/33-studio-tee')->assertOk()->assertSee('Studio Tee');
        $this->get('/artists/mira-sol')->assertOk();
        $this->get('/genres/ambient')->assertOk();
    }

    public function test_catalog_filters_and_session_cart_work(): void
    {
        $this->get('/shop?genre=jazz')->assertOk()->assertSee('Blue Hour Notes')->assertDontSee('Afterimage');
        $product = Product::where('slug', 'afterimage')->firstOrFail();
        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();
        $this->get('/cart')->assertOk()->assertSee('Afterimage')->assertSee('$56.00');
        $this->patch(route('cart.update', $product), ['quantity' => 1])->assertRedirect();
        $this->get('/cart')->assertSee('$28.00');
        $this->delete(route('cart.remove', $product))->assertRedirect();
        $this->get('/cart')->assertSee('Your cart is empty');
    }
}
