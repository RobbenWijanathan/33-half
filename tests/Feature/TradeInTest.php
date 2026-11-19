<?php

namespace Tests\Feature;

use App\Models\TradeInQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TradeInTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->forceFill(['role' => 'admin'])->save();
    }

    private function submit(): TradeInQuote
    {
        $this->actingAs($this->user)->post('/trade-in', [
            'artist' => 'Test Artist', 'album' => 'Test Album', 'record_grade' => 'VG+', 'sleeve_grade' => 'VG',
            'preference' => 'store_credit', 'notes' => 'Light sleeve wear.', 'photos' => [UploadedFile::fake()->createWithContent('record.png', file_get_contents(base_path('tests/Fixtures/cover.png')))],
            'user_id' => $this->admin->id, 'credit_offer' => 999999999, 'status' => 'completed',
        ])->assertSessionHasNoErrors()->assertRedirect();

        return TradeInQuote::firstOrFail();
    }

    public function test_quotes_and_private_photos_persist_and_are_owned_by_the_signed_in_user(): void
    {
        $quote = $this->submit();
        $this->assertSame($this->user->id, $quote->user_id);
        $this->assertSame('pending_review', $quote->status);
        $this->assertNull($quote->credit_offer);
        Storage::disk('local')->assertExists($quote->photos[0]);
        $this->flushSession();
        $this->get('/your-quotes')->assertOk()->assertSee('Test Album');
        $this->get(route('quotes.show', $quote))->assertOk()->assertSee('Light sleeve wear.');
        $this->get(route('quotes.photo', [$quote, 0]))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('quotes.show', $quote))->assertForbidden();
        $this->get(route('quotes.photo', [$quote, 0]))->assertForbidden();
        $this->patch(route('quotes.update', $quote), ['status' => 'cancelled'])->assertForbidden();
        $this->actingAs($this->admin)->get(route('quotes.photo', [$quote, 0]))->assertOk();
    }

    public function test_full_quote_workflow_issues_store_credit_exactly_once_after_inspection(): void
    {
        $quote = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.quotes.update', $quote), ['status' => 'completed', 'note' => 'Skip inspection'])->assertSessionHasErrors('status');
        $this->patch(route('admin.quotes.update', $quote), ['status' => 'offer_sent', 'cash_offer' => 150000, 'credit_offer' => 200000, 'note' => 'Reviewed'])->assertSessionHasNoErrors();
        $this->actingAs($this->user)->patch(route('quotes.update', $quote), ['status' => 'accepted', 'accepted_type' => 'store_credit', 'accepted_amount' => 999999])->assertSessionHasNoErrors();
        $this->assertSame(0, $this->user->fresh()->store_credit);
        $this->assertSame(200000, $quote->fresh()->accepted_amount);
        $this->patch(route('quotes.update', $quote), ['status' => 'waiting_for_delivery', 'delivery_method' => 'drop_off'])->assertSessionHasNoErrors();
        foreach (['delivered', 'inspection', 'completed'] as $status) {
            $this->actingAs($this->admin)->patch(route('admin.quotes.update', $quote), ['status' => $status, 'note' => 'Reviewed: '.$status])->assertSessionHasNoErrors();
        }
        $this->assertSame(200000, $this->user->fresh()->store_credit);
        $this->assertDatabaseHas('credit_transactions', ['user_id' => $this->user->id, 'trade_in_quote_id' => $quote->id, 'amount' => 200000, 'type' => 'issuance']);
        $this->patch(route('admin.quotes.update', $quote), ['status' => 'completed', 'note' => 'Duplicate'])->assertSessionHasErrors('status');
        $this->assertDatabaseCount('credit_transactions', 1);
        $this->assertDatabaseCount('quote_histories', 7);
        $this->actingAs($this->user)->get('/account/credits')->assertOk()->assertSee('Rp 200.000');
    }

    public function test_cash_completion_does_not_issue_credit_and_declined_offers_are_final(): void
    {
        $quote = $this->submit();
        $this->actingAs($this->admin)->patch(route('admin.quotes.update', $quote), ['status' => 'offer_sent', 'cash_offer' => 150000, 'note' => 'Cash offer'])->assertSessionHasNoErrors();
        $this->actingAs($this->user)->patch(route('quotes.update', $quote), ['status' => 'accepted', 'accepted_type' => 'store_credit'])->assertSessionHasErrors('accepted_type');
        $this->patch(route('quotes.update', $quote), ['status' => 'accepted', 'accepted_type' => 'cash'])->assertSessionHasNoErrors();
        $this->patch(route('quotes.update', $quote), ['status' => 'waiting_for_delivery', 'delivery_method' => 'delivery'])->assertSessionHasNoErrors();
        foreach (['delivered', 'inspection', 'completed'] as $status) {
            $this->actingAs($this->admin)->patch(route('admin.quotes.update', $quote), ['status' => $status, 'note' => 'Manual cash paid REF-CASH'])->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('credit_transactions', 0);
        $other = $this->user->quotes()->create(['artist' => 'Other', 'album' => 'Other', 'record_grade' => 'VG', 'sleeve_grade' => 'VG', 'preference' => 'cash', 'status' => 'offer_sent', 'cash_offer' => 100000]);
        $this->actingAs($this->user)->patch(route('quotes.update', $other), ['status' => 'declined'])->assertSessionHasNoErrors();
        $this->patch(route('quotes.update', $other), ['status' => 'accepted', 'accepted_type' => 'cash'])->assertSessionHasErrors('status');
    }

    public function test_invalid_grades_and_executable_uploads_are_rejected(): void
    {
        $this->actingAs($this->user)->post('/trade-in', [
            'artist' => 'Test', 'album' => 'Test', 'record_grade' => 'perfect', 'sleeve_grade' => 'VG', 'preference' => 'cash',
            'photos' => [UploadedFile::fake()->create('script.php', 1, 'application/x-php')],
        ])->assertSessionHasErrors(['record_grade', 'photos.0']);
        $this->assertDatabaseCount('trade_in_quotes', 0);
    }
}
