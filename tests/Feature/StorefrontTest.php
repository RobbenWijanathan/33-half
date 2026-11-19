<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
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
        foreach (['/', '/shop', '/vinyl', '/merch', '/artists', '/genres', '/pre-orders', '/crate-digging', '/about', '/contact', '/crates', '/trade-in'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/')->assertSee('<title>33½ Records - Home</title>', false);
        $this->get('/new-arrivals')->assertRedirect('/vinyl');
        $this->get('/records/ok-computer')->assertOk()->assertSee('<title>33½ Records - OK Computer</title>', false);
        $this->get('/records/illmatic')->assertOk()
            ->assertSee('Very Good Plus (VG+)')->assertSee('Very Good (VG)')
            ->assertSee('The sleeve grade describes the outer cover.');
        $this->get('/merch/33-studio-tee')->assertOk()->assertSee('Studio Tee');
        $this->get('/artists/radiohead')->assertOk();
        $this->get('/genres/ambient')->assertOk();
    }

    public function test_catalog_filters_and_session_cart_work(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/shop?genre=jazz')->assertOk()->assertSee('A Love Supreme')->assertDontSee('Abbey Road');
        $product = Product::where('slug', 'ok-computer')->firstOrFail();
        $this->post(route('cart.add', $product), ['quantity' => 2])->assertRedirect();
        $this->get('/cart')->assertOk()->assertSee('OK Computer')->assertSee('Rp 1.290.000');
        $this->patch(route('cart.update', $product), ['quantity' => 1])->assertRedirect();
        $this->get('/cart')->assertSee('Rp 645.000');
        $this->delete(route('cart.remove', $product))->assertRedirect();
        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_album_seed_and_merchandise_filters(): void
    {
        $this->assertSame(90, Product::vinyl()->count());
        $this->assertDatabaseHas('products', ['slug' => 'imaginal-disk', 'product_type' => 'vinyl']);
        $this->assertFalse(Product::vinyl()->has('genres', '>', 3)->exists());
        $this->get('/vinyl?condition=used')->assertOk()
            ->assertSee('Illmatic')->assertDontSee('Vespertine');
        $this->get('/merch?genre=jazz&sort=price-low')->assertOk()
            ->assertDontSee('name="genre"', false)
            ->assertSeeInOrder(['Side B Poster artwork', 'Listening Room Tote artwork', '33½ Studio Tee artwork']);
    }

    public function test_vinyl_pagination_keeps_sort_and_parent_genres(): void
    {
        $pageOne = $this->get('/vinyl?sort=name')->assertOk()->viewData('products');
        $pageTwo = $this->get('/vinyl?sort=name&page=2')->assertOk()->viewData('products');

        $this->assertCount(48, $pageOne);
        $this->assertCount(42, $pageTwo);
        $this->assertStringContainsString('sort=name', $pageOne->nextPageUrl());

        $this->get('/vinyl?genre=idm')->assertOk()
            ->assertSee('Selected Ambient Works 85–92')
            ->assertDontSee('Abbey Road artwork');
        $this->get('/records/selected-ambient-works-85-92')->assertOk()
            ->assertSee('data-gallery-view', false);
    }

    public function test_currency_and_live_cart_totals_share_the_session(): void
    {
        $this->actingAs(User::factory()->create());
        $record = Product::where('slug', 'illmatic')->firstOrFail();
        $tee = Product::where('slug', '33-studio-tee')->firstOrFail();
        $this->post(route('cart.add', $record), ['quantity' => 1])->assertRedirect();
        $this->post(route('cart.add', $tee), ['quantity' => 1])->assertRedirect();
        $this->from('/cart')->post(route('currency.update'), ['currency' => 'USD'])->assertRedirect('/cart');

        $this->patchJson(route('cart.update', $record), ['quantity' => 8])->assertOk()
            ->assertJsonPath('quantity', 2)
            ->assertJsonPath('quantityCount', 3)
            ->assertJsonPath('lineTotal', '$60.63')
            ->assertJsonPath('subtotal', '$86.63')
            ->assertJsonPath('total', '$86.63');

        $this->get('/cart')->assertSee('$86.63');
        $this->get('/merch')->assertSee('$26.00');
    }

    public function test_crate_directories_and_carousel_collections_work(): void
    {
        $this->get('/crates')->assertOk()->assertSee('Artist A–Z')->assertSee('crate-stack', false);
        $this->get('/crates?type=genres')->assertOk()->assertDontSee('artist-heading', false);
        $this->get('/crates?type=artists&sort=count')->assertOk()->assertDontSee('genre-heading', false);
        $this->get('/crates/artists/B')->assertOk()
            ->assertSee('Vespertine')->assertDontSee('Illmatic');
        $this->get('/crate-digging?sort=title')->assertOk()
            ->assertSee('data-record-carousel', false)->assertSee('data-carousel-caption', false)
            ->assertSeeInOrder(['Focus Abbey Road', 'Focus A Love Supreme']);
        $this->get('/genres/electronic?sort=newest')->assertOk()
            ->assertSee('Vespertine')->assertDontSee('Illmatic');
        $this->get('/crates/artists/Z')->assertOk()->assertSee('This crate is empty');
    }
}
