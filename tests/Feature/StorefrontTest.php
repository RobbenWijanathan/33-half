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

    public function test_search_matches_genre_label_and_description_as_well_as_title_and_artist(): void
    {
        $this->get('/shop?q=jazz')->assertOk()->assertSee('Blue Hour Notes')->assertDontSee('Afterimage');
        $this->get('/shop?q=after+hours')->assertOk()->assertSee('The Last Light')->assertDontSee('Afterimage');
        $this->get('/shop?q=mira')->assertOk()->assertSee('Afterimage')->assertDontSee('Blue Hour Notes');

        Product::where('slug', 'afterimage')->update(['description' => 'Pressed on translucent orange wax.']);
        $this->get('/shop?q=translucent')->assertOk()->assertSee('Afterimage')->assertDontSee('Blue Hour Notes');
    }

    public function test_active_filters_show_as_removable_chips(): void
    {
        $response = $this->get('/vinyl?genre=jazz&q=blue&max_price=40&sort=name')->assertOk()
            ->assertSee('Genre: Jazz')
            ->assertSee('Search: “blue”')
            ->assertSee('Up to $40')
            ->assertSee('Clear all')
            ->assertSee('1 item for “blue”');

        // Removing one chip keeps the other filters and the sort.
        $response->assertSee('href="'.e(url('/vinyl?q=blue&max_price=40&sort=name')).'"', false);

        $this->get('/vinyl')->assertOk()->assertDontSee('filter-chips', false);
    }

    public function test_a_search_with_no_matches_explains_itself_and_offers_a_way_back(): void
    {
        $this->get('/shop?q=zzzz')->assertOk()
            ->assertSee('No matches for “zzzz”')
            ->assertSee('Clear search')
            ->assertDontSee('Nothing in this section yet');

        $this->get('/shop?genre=jazz&availability=preorder')->assertOk()
            ->assertSee('Nothing matches these filters')
            ->assertSee('Clear all filters');
    }

    public function test_a_reversed_price_range_is_read_the_way_it_was_meant(): void
    {
        $this->get('/vinyl?min_price=31&max_price=28')->assertOk()
            ->assertSee('Afterimage')            // $28
            ->assertSee('No Fixed Address')      // $31
            ->assertDontSee('Forms in Motion')   // $35
            ->assertSee('From $28')
            ->assertSee('Up to $31');
    }

    public function test_listings_page_with_numbered_links_and_keep_the_filters(): void
    {
        foreach (range(1, 13) as $number) {
            Product::create([
                'name' => 'Paging Record '.$number,
                'slug' => 'paging-record-'.$number,
                'product_type' => 'vinyl',
                'price' => 20,
                'stock' => 5,
                'release_date' => today()->subYears(2),
            ]);
        }

        // 8 seeded + 13 new vinyl records = 21, so two pages of 12.
        $this->get('/vinyl?sort=name')->assertOk()
            ->assertSee('Page 1 of 2')
            ->assertSee('aria-current="page"', false)
            ->assertSee('href="'.e(url('/vinyl?sort=name&page=2')).'"', false);

        $this->get('/vinyl?sort=name&page=2')->assertOk()->assertSee('Page 2 of 2');

        // Searching narrows it back to one page, so no pagination is shown.
        $this->get('/vinyl?q=afterimage')->assertOk()->assertDontSee('Page 1 of');
    }

    public function test_the_discovery_scopes_can_be_chained_on_the_model(): void
    {
        $names = Product::search('mira')->filter(['genre' => 'ambient'])->sorted('name')->pluck('name');
        $this->assertSame(['Small Hours'], $names->all());

        $this->assertSame(
            ['Afterimage', 'Nothing Stays Still', 'Blue Hour Notes'],
            Product::newArrivals()->sorted('newest')->pluck('name')->all(),
        );
    }

    public function test_home_hides_the_new_arrivals_section_when_nothing_is_new(): void
    {
        $this->get('/')->assertSee('Fresh on the shelf');
        Product::query()->update(['release_date' => today()->subYear()]);
        $this->get('/')->assertOk()->assertDontSee('Fresh on the shelf');
        $this->get('/new-arrivals')->assertOk()->assertSee('Nothing in this section yet');
    }
}
