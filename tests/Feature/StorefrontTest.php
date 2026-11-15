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

    public function test_new_arrivals_lists_only_recent_releases_newest_first(): void
    {
        // Seeded: Afterimage (12 days old), Nothing Stays Still (40), Blue Hour Notes (75) are inside the window.
        $this->get('/new-arrivals')->assertOk()
            ->assertSeeInOrder(['Afterimage', 'Nothing Stays Still', 'Blue Hour Notes'])
            ->assertDontSee('Forms in Motion')   // released 120 days ago
            ->assertDontSee('Shoreline Radio')   // pre-order
            ->assertDontSee('Studio Tee');       // no release date

        $this->get('/new-arrivals?sort=price-high')->assertOk()
            ->assertSeeInOrder(['Nothing Stays Still', 'Blue Hour Notes', 'Afterimage']);
    }

    public function test_new_arrival_window_edges_and_badge(): void
    {
        $edge = Product::where('slug', 'afterimage')->firstOrFail();

        $edge->update(['release_date' => today()->subDays(Product::NEW_ARRIVAL_DAYS)]);
        $this->assertTrue($edge->isNewArrival());
        $this->assertTrue(Product::newArrivals()->whereKey($edge->id)->exists());

        $edge->update(['release_date' => today()->subDays(Product::NEW_ARRIVAL_DAYS + 1)]);
        $this->assertFalse($edge->isNewArrival());
        $this->assertFalse(Product::newArrivals()->whereKey($edge->id)->exists());

        $edge->update(['release_date' => today()->addDay()]);
        $this->assertFalse($edge->isNewArrival());
        $this->assertFalse(Product::newArrivals()->whereKey($edge->id)->exists());

        $this->get('/records/blue-hour-notes')->assertOk();
        $this->get('/vinyl')->assertSee('product-badge-new', false);
        $this->get('/pre-orders')->assertDontSee('product-badge-new', false);
    }

    public function test_home_hides_the_new_arrivals_section_when_nothing_is_new(): void
    {
        $this->get('/')->assertSee('Fresh on the shelf');
        Product::query()->update(['release_date' => today()->subYear()]);
        $this->get('/')->assertOk()->assertDontSee('Fresh on the shelf');
        $this->get('/new-arrivals')->assertOk()->assertSee('Nothing in this section yet');
    }
}
