<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_are_redirected_and_regular_users_cannot_use_admin_routes(): void
    {
        foreach (['/cart', '/checkout', '/account', '/orders', '/your-quotes', '/delivery-tracking', '/admin'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->actingAs(User::factory()->create());
        foreach (['/admin', '/admin/records', '/admin/merchandise', '/admin/crates', '/admin/quotes', '/admin/orders', '/admin/users', '/admin/audit'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/records', [])->assertForbidden();
    }

    public function test_registration_hashes_password_and_ignores_privileged_fields(): void
    {
        $this->post('/register', ['name' => 'New Collector', 'email' => 'COLLECTOR@example.com', 'password' => 'Records1234', 'password_confirmation' => 'Records1234', 'role' => 'admin', 'store_credit' => 99999999])->assertRedirect('/account');
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->role);
        $this->assertSame(0, $user->store_credit);
        $this->assertSame('collector@example.com', $user->email);
        $this->assertTrue(Hash::check('Records1234', $user->password));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_returns_to_intended_page_and_rejects_inactive_accounts(): void
    {
        $user = User::factory()->create(['password' => 'Records1234']);
        $this->get('/cart')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'Records1234'])->assertRedirect('/cart');
        $this->post('/logout');
        $user->forceFill(['is_active' => false])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'Records1234'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/account')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_uses_the_broker_token_and_cannot_be_replayed(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('notice');
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $data = ['token' => $token, 'email' => $user->email, 'password' => 'FreshRecords123', 'password_confirmation' => 'FreshRecords123'];
        $this->post('/reset-password', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check('FreshRecords123', $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_settings_require_current_password_and_address_ownership(): void
    {
        $user = User::factory()->create(['password' => 'Records1234']);
        $other = User::factory()->create();
        $address = $other->addresses()->create(['label' => 'Home', 'recipient' => 'Other', 'phone' => '123', 'street' => 'Street 1', 'city' => 'Jakarta', 'province' => 'DKI', 'postal_code' => '12110']);
        $this->actingAs($user)->patch('/account', ['name' => 'Updated', 'email' => $user->email, 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->patch('/account', ['name' => 'Updated', 'email' => $user->email, 'current_password' => 'Records1234', 'role' => 'admin', 'store_credit' => 500000])->assertSessionHasNoErrors();
        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame(0, $user->fresh()->store_credit);
        $this->delete('/addresses/'.$address->id)->assertForbidden();
        $this->patch('/addresses/'.$address->id, $address->toArray())->assertForbidden();
        $this->put('/account/password', ['current_password' => 'Records1234', 'password' => 'FreshRecords123', 'password_confirmation' => 'FreshRecords123'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('FreshRecords123', $user->fresh()->password));
    }

    public function test_failed_logins_are_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
