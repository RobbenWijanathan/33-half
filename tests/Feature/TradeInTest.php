<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeInTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_request_is_saved_in_the_session_and_shown_in_quotes(): void
    {
        $this->withoutVite();
        $this->post('/trade-in', [
            'artist' => 'Test Artist',
            'album' => 'Test Album',
            'record_grade' => 'VG+',
            'sleeve_grade' => 'VG',
            'preference' => 'store_credit',
            'name' => 'Record Collector',
            'email' => 'collector@example.com',
        ])->assertRedirect('/your-quotes')->assertSessionHas('quotes.0.album', 'Test Album');

        $this->get('/your-quotes')->assertOk()->assertSee('Test Album')->assertSee('Store credit');
        $this->flushSession();
        $this->get('/your-quotes')->assertSee('No quotes yet.')->assertDontSee('Test Album');
    }

    public function test_invalid_grades_and_contact_details_are_rejected(): void
    {
        $this->post('/trade-in', [
            'artist' => 'Test Artist',
            'album' => 'Test Album',
            'record_grade' => 'perfect',
            'sleeve_grade' => 'VG',
            'preference' => 'cash',
            'name' => 'Collector',
            'email' => 'invalid-email',
        ])->assertSessionHasErrors(['record_grade', 'email'])->assertSessionMissing('quotes');
    }
}
