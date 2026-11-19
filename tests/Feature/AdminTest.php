<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Crate;
use App\Models\Genre;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Genre $genre;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => 'admin'])->save();
        $this->genre = Genre::create(['name' => 'Jazz', 'slug' => 'jazz']);
        $this->actingAs($this->admin);
    }

    private function recordData(): array
    {
        return ['name' => 'Test Record', 'slug' => 'test-record', 'description' => 'A jazz record.', 'price' => 200000, 'stock' => 3, 'condition' => 'used', 'record_grade' => 'VG+', 'sleeve_grade' => 'VG', 'artist_names' => 'Test Artist', 'genre_ids' => [$this->genre->id]];
    }

    public function test_record_crud_validates_inputs_and_replaces_uploads_safely(): void
    {
        $data = $this->recordData();
        $this->post('/admin/records', $data + ['cover' => UploadedFile::fake()->createWithContent('front.png', file_get_contents(base_path('tests/Fixtures/cover.png')))])->assertSessionHasNoErrors();
        $record = Product::firstOrFail();
        $firstImage = substr($record->cover_image, 8);
        Storage::disk('public')->assertExists($firstImage);
        $this->assertSame('Test Artist', $record->artists()->first()->name);
        $this->assertSame('Jazz', $record->genres()->first()->name);
        $this->put(route('admin.records.update', $record), array_replace($data, ['price' => -1, 'stock' => -1]))->assertSessionHasErrors(['price', 'stock']);
        $this->put(route('admin.records.update', $record), array_replace($data, ['price' => 100000000]))->assertSessionHasErrors('price');
        $this->put(route('admin.records.update', $record), array_replace($data, ['name' => 'Edited Record', 'artist_names' => 'Artist, With Comma', 'cover' => UploadedFile::fake()->createWithContent('replacement.png', file_get_contents(base_path('tests/Fixtures/cover.png')))]))->assertSessionHasNoErrors();
        $this->assertSame('Artist, With Comma', $record->artists()->sole()->name);
        Storage::disk('public')->assertMissing($firstImage);
        $this->assertSame('Edited Record', $record->fresh()->name);
        $this->get(route('admin.records.edit', $record))->assertOk()->assertSee('Edited Record');
        $this->delete(route('admin.records.destroy', $record))->assertRedirect('/admin/records');
        $this->assertSoftDeleted($record);
        $this->assertDatabaseCount('admin_audit_logs', 3);
    }

    public function test_merchandise_variants_are_editable_and_foreign_variant_ids_are_rejected(): void
    {
        $data = ['name' => 'Store Tee', 'slug' => 'store-tee', 'description' => 'Cotton shirt.', 'price' => 200000, 'stock' => 0, 'condition' => 'new', 'variants' => [['name' => 'Medium', 'sku' => 'TEE-M', 'price' => 210000, 'stock' => 5]]];
        $this->post('/admin/merchandise', $data)->assertSessionHasNoErrors();
        $duplicate = $data;
        $duplicate['slug'] = 'duplicate-variants';
        $duplicate['variants'] = [['name' => 'One', 'sku' => 'DUPLICATE-SKU', 'price' => 100000, 'stock' => 1], ['name' => 'Two', 'sku' => 'DUPLICATE-SKU', 'price' => 100000, 'stock' => 1]];
        $this->post('/admin/merchandise', $duplicate)->assertSessionHasErrors('variants.0.sku');
        $this->flushSession();
        $merch = Product::firstOrFail();
        $variant = $merch->variants()->firstOrFail();
        $this->get(route('admin.merchandise.edit', $merch))->assertOk()->assertSee('TEE-M');
        $this->get('/merch/store-tee')->assertOk()->assertSee('Medium')->assertSee('data-product-variant', false);
        $other = Product::create(['name' => 'Other Tee', 'slug' => 'other-tee', 'description' => 'Other', 'price' => 100000, 'stock' => 0, 'product_type' => 'merchandise']);
        $foreign = $other->variants()->create(['name' => 'Large', 'sku' => 'OTHER-L', 'price' => 100000, 'stock' => 2]);
        $bad = $data;
        $bad['variants'][0]['id'] = $foreign->id;
        $this->put(route('admin.merchandise.update', $merch), $bad)->assertSessionHasErrors('variants.0.id');
        $data['variants'][0]['id'] = $variant->id;
        $data['variants'][0]['stock'] = 9;
        $this->put(route('admin.merchandise.update', $merch), $data)->assertSessionHasNoErrors();
        $this->assertSame(9, $variant->fresh()->stock);
        $data['variants'][0]['delete'] = 1;
        $this->put(route('admin.merchandise.update', $merch), $data)->assertSessionHasNoErrors();
        $this->assertSoftDeleted($variant);
        $this->delete(route('admin.merchandise.destroy', $merch))->assertRedirect('/admin/merchandise');
    }

    public function test_crates_are_managed_and_visible_in_the_public_carousel(): void
    {
        $this->post('/admin/records', $this->recordData())->assertSessionHasNoErrors();
        $record = Product::firstOrFail();
        $data = ['name' => 'Sunday Jazz', 'slug' => 'sunday-jazz', 'description' => 'Slow Sunday listening.', 'color' => '#70451f', 'product_ids' => [$record->id]];
        $this->post('/admin/crates', $data)->assertSessionHasNoErrors();
        $crate = Crate::firstOrFail();
        $this->get('/crates?type=curated')->assertOk()->assertSee('Sunday Jazz');
        $this->get('/crates/sunday-jazz')->assertOk()->assertSee('Test Record')->assertSee('data-record-carousel', false);
        $this->put(route('admin.crates.update', $crate), array_replace($data, ['color' => 'red']))->assertSessionHasErrors('color');
        $this->put(route('admin.crates.update', $crate), array_replace($data, ['name' => 'Evening Jazz']))->assertSessionHasNoErrors();
        $this->get(route('admin.crates.edit', $crate))->assertOk()->assertSee('Evening Jazz');
        $this->delete(route('admin.crates.destroy', $crate))->assertRedirect('/admin/crates');
        $this->assertSoftDeleted($crate);
        $this->assertDatabaseHas('products', ['id' => $record->id, 'deleted_at' => null]);
    }

    public function test_last_active_admin_cannot_be_removed_and_credit_adjustments_require_reasons(): void
    {
        $data = ['name' => $this->admin->name, 'email' => $this->admin->email, 'role' => 'user', 'is_active' => 1];
        $this->put(route('admin.users.update', $this->admin), $data)->assertSessionHasErrors('role');
        $this->put(route('admin.users.update', $this->admin), array_replace($data, ['role' => 'admin', 'is_active' => 0]))->assertSessionHasErrors('role');
        $user = User::factory()->create();
        $this->post(route('admin.users.credit', $user), ['amount' => 100000])->assertSessionHasErrors('reason');
        $this->post(route('admin.users.credit', $user), ['amount' => 100000, 'reason' => 'Store adjustment'])->assertSessionHasNoErrors();
        $this->post(route('admin.users.credit', $user), ['amount' => -100001, 'reason' => 'Too much'])->assertSessionHasErrors('credit');
        $this->assertSame(100000, $user->fresh()->store_credit);
        $this->assertDatabaseCount('credit_transactions', 1);
        $this->assertTrue(AdminAuditLog::where('action', 'credit.adjusted')->exists());
        $this->put(route('admin.users.update', $user), ['name' => 'Updated User', 'email' => $user->email, 'role' => 'user', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee('Store adjustment');
    }

    public function test_all_management_tables_and_create_forms_render_and_filter(): void
    {
        $this->post('/admin/records', $this->recordData())->assertSessionHasNoErrors();
        foreach (['/admin', '/admin/records', '/admin/merchandise', '/admin/crates', '/admin/orders', '/admin/quotes', '/admin/users', '/admin/audit', '/admin/records/create', '/admin/merchandise/create', '/admin/crates/create'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/admin/records?q=missing')->assertOk()->assertSee('No products match');
        $this->get('/admin/records?q=Test&sort=stock&stock=low&condition=used')->assertOk()->assertSee('Test Record');
        $this->get('/admin/users?role=user&sort=name')->assertOk()->assertDontSee($this->admin->email);
        $this->get('/admin/audit?action=product.saved')->assertOk()->assertSee('Saved Test Record');
    }
}
