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
        foreach (['/', '/shop', '/vinyl', '/merch', '/artists', '/genres', '/new-arrivals', '/pre-orders', '/crate-digging', '/about', '/contact', '/cart', '/crates', '/trade-in', '/your-quotes'] as $url) {
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

    public function test_condition_filters_and_currency_safe_cart_work(): void
    {
        $this->get('/vinyl?condition=used')->assertOk()
            ->assertSee('Illmatic')->assertDontSee('Vespertine');

        $rupiahRecord = Product::where('slug', 'illmatic')->firstOrFail();
        $dollarRecord = Product::where('slug', 'afterimage')->firstOrFail();
        $this->post(route('cart.add', $rupiahRecord), ['quantity' => 2])->assertRedirect();
        $this->get('/cart')->assertSee('Rp 700.000');
        $this->post(route('cart.add', $dollarRecord), ['quantity' => 1])
            ->assertSessionHas('notice');
        $this->assertArrayNotHasKey($dollarRecord->id, session('cart'));
    }

    public function test_crate_directories_and_carousel_collections_work(): void
    {
        $this->get('/crates')->assertOk()->assertSee('Artist A–Z')->assertSee('crate-stack', false);
        $this->get('/crates?type=genres')->assertOk()->assertDontSee('artist-heading', false);
        $this->get('/crates?type=artists&sort=count')->assertOk()->assertDontSee('genre-heading', false);
        $this->get('/crates/artists/A')->assertOk()
            ->assertSee('Selected Ambient Works')->assertDontSee('Illmatic');
        $this->get('/crate-digging?sort=title')->assertOk()
            ->assertSee('data-record-carousel', false)->assertSee('data-carousel-caption', false)
            ->assertSeeInOrder(['Focus Afterimage', 'Focus Blue Hour Notes']);
        $this->get('/genres/electronic?sort=newest')->assertOk()
            ->assertSee('Vespertine')->assertDontSee('Illmatic');
        $this->get('/crates/artists/Z')->assertOk()->assertSee('This crate is empty');
    }
}
