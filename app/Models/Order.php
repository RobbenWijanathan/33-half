<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'address_snapshot' => 'array', 'inventory_restored_at' => 'datetime',
        'subtotal' => 'integer', 'delivery_fee' => 'integer', 'credit_applied' => 'integer', 'total' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->orderBy('id');
    }

    public function nextStatuses(): array
    {
        $steps = $this->fulfillment === 'pickup'
            ? ['pending', 'confirmed', 'ready_for_pickup', 'picked_up']
            : ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered'];
        $position = array_search($this->status, $steps, true);
        $next = $position !== false && isset($steps[$position + 1]) ? [$steps[$position + 1]] : [];
        if (in_array($this->status, ['pending', 'confirmed', 'processing', 'ready_for_pickup'], true)) {
            $next[] = $this->payment_status === 'paid' ? 'refunded' : 'cancelled';
        }

        return $next;
    }

    public function updateStatus(string $status, User $actor, string $note): void
    {
        abort_unless($actor->isAdmin(), 403);
        DB::transaction(function () use ($status, $actor, $note) {
            $user = User::lockForUpdate()->findOrFail($this->user_id);
            $order = self::lockForUpdate()->findOrFail($this->id);
            if (! in_array($status, $order->nextStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'That order status change is not allowed.']);
            }
            if (! in_array($status, ['cancelled', 'refunded'], true) && $order->payment_status !== 'paid') {
                throw ValidationException::withMessages(['status' => 'Verify the payment before confirming this order.']);
            }
            if (in_array($status, ['cancelled', 'refunded'], true)) {
                // These actions are available only before dispatch or pickup.
                foreach ($order->items()->orderBy('product_id')->orderBy('product_variant_id')->get() as $item) {
                    $product = Product::withTrashed()->lockForUpdate()->findOrFail($item->product_id);
                    if ($item->product_variant_id) {
                        ProductVariant::withTrashed()->whereKey($item->product_variant_id)->lockForUpdate()->firstOrFail()->increment('stock', $item->quantity);
                    } else {
                        $product->increment('stock', $item->quantity);
                    }
                }
                if ($order->credit_applied > 0) {
                    $user->changeCredit($order->credit_applied, 'reversal', 'Credit returned for '.$order->number, 'order-reversal-'.$order->id, $actor->id, $order->id);
                }
                $order->inventory_restored_at = now();
                $order->payment_status = $status === 'refunded' ? 'refunded' : 'cancelled';
                $order->payment()->update(['status' => $order->payment_status, 'note' => $note]);
            }
            $order->status = $status;
            $order->save();
            $order->histories()->create(['actor_id' => $actor->id, 'status' => $status, 'note' => $note]);
            AdminAuditLog::record('order.status', $order, $status.': '.$note);
        });
    }

    public function verifyPayment(User $actor, string $note): void
    {
        abort_unless($actor->isAdmin(), 403);
        DB::transaction(function () use ($actor, $note) {
            $order = self::lockForUpdate()->findOrFail($this->id);
            if ($order->payment_status !== 'pending' || $order->status !== 'pending') {
                throw ValidationException::withMessages(['payment' => 'This payment cannot be verified again.']);
            }
            $order->update(['payment_status' => 'paid']);
            $order->payment()->update(['status' => 'paid', 'verified_by' => $actor->id, 'verified_at' => now(), 'note' => $note]);
            $order->histories()->create(['actor_id' => $actor->id, 'status' => $order->status, 'note' => 'Manual payment verified. '.$note]);
            AdminAuditLog::record('payment.verified', $order, $note);
        });
    }
}
