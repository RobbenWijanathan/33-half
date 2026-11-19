<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TradeInQuote extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['photos' => 'array', 'cash_offer' => 'integer', 'credit_offer' => 'integer', 'accepted_amount' => 'integer', 'completed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->hasMany(QuoteHistory::class)->orderBy('id');
    }

    public function nextStatuses(): array
    {
        return match ($this->status) {
            'pending_review' => ['offer_sent', 'rejected', 'cancelled'],
            'offer_sent' => ['accepted', 'declined', 'cancelled'],
            'accepted' => ['waiting_for_delivery', 'cancelled'],
            'waiting_for_delivery' => ['delivered', 'cancelled'],
            'delivered' => ['inspection'],
            'inspection' => ['completed', 'rejected'],
            default => [],
        };
    }

    public function changeStatus(string $status, User $actor, array $data = []): void
    {
        DB::transaction(function () use ($status, $actor, $data) {
            $user = User::lockForUpdate()->findOrFail($this->user_id);
            $quote = self::lockForUpdate()->findOrFail($this->id);
            if (! in_array($status, $quote->nextStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'That quote status change is not allowed.']);
            }
            if (in_array($status, ['accepted', 'declined', 'waiting_for_delivery'], true)) {
                abort_unless($actor->id === $quote->user_id, 403);
            } elseif ($status === 'cancelled') {
                abort_unless($actor->id === $quote->user_id || $actor->isAdmin(), 403);
            } else {
                abort_unless($actor->isAdmin(), 403);
            }
            if ($status === 'offer_sent') {
                if (empty($data['cash_offer']) && empty($data['credit_offer'])) {
                    throw ValidationException::withMessages(['cash_offer' => 'Enter at least one positive offer in IDR.']);
                }
                $quote->fill(collect($data)->only(['cash_offer', 'credit_offer', 'offer_notes'])->all());
            }
            if ($status === 'accepted') {
                $type = $data['accepted_type'] ?? '';
                $amount = match ($type) {
                    'cash' => $quote->cash_offer, 'store_credit' => $quote->credit_offer, default => null
                };
                if (! $amount) {
                    throw ValidationException::withMessages(['accepted_type' => 'Choose one of the available offers.']);
                }
                $quote->accepted_type = $type;
                $quote->accepted_amount = $amount;
            }
            if ($status === 'waiting_for_delivery') {
                if (! in_array($data['delivery_method'] ?? '', ['drop_off', 'delivery'], true)) {
                    throw ValidationException::withMessages(['delivery_method' => 'Choose delivery or drop-off.']);
                }
                $quote->delivery_method = $data['delivery_method'];
            }
            if ($status === 'completed') {
                if ($quote->accepted_type === 'store_credit') {
                    $user->changeCredit($quote->accepted_amount, 'issuance', 'Trade-in #'.$quote->id.' — '.$quote->album, 'quote-'.$quote->id, $actor->id, null, $quote->id);
                }
                $quote->completed_at = now();
            }
            $quote->status = $status;
            $quote->save();
            $quote->histories()->create(['actor_id' => $actor->id, 'status' => $status, 'note' => $data['note'] ?? $data['offer_notes'] ?? null]);
            if ($actor->isAdmin()) {
                AdminAuditLog::record('quote.status', $quote, $status.': '.($data['note'] ?? $data['offer_notes'] ?? ''));
            }
        });
    }
}
