<?php

namespace Database\Seeders;

use App\Models\Crate;
use App\Models\Genre;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Jazz', 'Electronic', 'Rock'] as $name) {
            $genre = Genre::where('name', $name)->first();
            if (! $genre) {
                continue;
            }
            $crate = Crate::withTrashed()->firstOrCreate(['slug' => strtolower($name).'-essentials'], [
                'name' => $name.' Essentials', 'description' => 'A starting point for your next '.$name.' discovery.',
                'color' => match ($name) {
                    'Jazz' => '#70451f', 'Electronic' => '#384e54', default => '#70253c'
                },
            ]);
            if ($crate->wasRecentlyCreated) {
                $crate->products()->sync($genre->products()->vinyl()->pluck('products.id'));
            }
        }

        // Development accounts are created only from local environment settings.
        if (! app()->environment('local')) {
            return;
        }
        $email = config('demo.admin_email');
        $password = config('demo.admin_password');
        if ($email && $password) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || ! preg_match('/[A-Za-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
                throw new \RuntimeException('Set a valid DEV_ADMIN_EMAIL and a DEV_ADMIN_PASSWORD with at least 8 characters, letters and numbers.');
            }
            $admin = User::firstOrCreate(['email' => strtolower($email)], ['name' => config('demo.admin_name'), 'password' => $password]);
            if ($admin->wasRecentlyCreated) {
                $admin->forceFill(['role' => 'admin'])->save();
            }
        } else {
            $this->command?->warn('Development admin not seeded. Configure DEV_ADMIN_EMAIL and DEV_ADMIN_PASSWORD, or run php artisan user:admin.');
        }

        if (! config('demo.seed_samples') || ! config('demo.user_password')) {
            return;
        }
        DB::transaction(function () {
            $names = ['Maya Collector', 'Adi Listener', 'Nadia Digger'];
            foreach ($names as $index => $name) {
                $user = User::firstOrCreate(['email' => ['maya', 'adi', 'nadia'][$index].'@33half.test'], ['name' => $name, 'password' => config('demo.user_password')]);
                $user->addresses()->firstOrCreate(['label' => 'Home'], ['recipient' => $name, 'phone' => '08120000000'.($index + 1), 'street' => 'Jalan Musik '.($index + 1), 'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'postal_code' => '12110']);
                $this->sampleQuote($user, $index);
                $this->sampleOrder($user, $index);
            }
        });
        $tote = Product::where('slug', 'listening-room-tote')->first();
        if ($tote) {
            $tote->variants()->withTrashed()->firstOrCreate(['sku' => '33H-TOTE-NATURAL'], ['name' => 'Natural', 'price' => 288000, 'stock' => 8]);
            $tote->variants()->withTrashed()->firstOrCreate(['sku' => '33H-TOTE-BURGUNDY'], ['name' => 'Burgundy', 'price' => 304000, 'stock' => 5]);
        }
    }

    private function sampleQuote(User $user, int $index): void
    {
        $quote = $user->quotes()->firstOrCreate(['album' => 'Sample collection '.($index + 1)], [
            'artist' => 'Local collection', 'record_grade' => 'VG+', 'sleeve_grade' => 'VG',
            'preference' => 'store_credit', 'notes' => 'Development sample. No real record or cash is involved.',
        ]);
        if (! $quote->wasRecentlyCreated) {
            return;
        }
        $statuses = ['pending_review'];
        if ($index === 1) {
            $quote->update(['status' => 'offer_sent', 'cash_offer' => 220000, 'credit_offer' => 270000, 'offer_notes' => 'Sample offer, subject to inspection.']);
            $statuses[] = 'offer_sent';
        }
        if ($index === 0) {
            $quote->update(['status' => 'completed', 'cash_offer' => 250000, 'credit_offer' => 300000, 'accepted_type' => 'store_credit', 'accepted_amount' => 300000, 'delivery_method' => 'drop_off', 'completed_at' => now()]);
            $statuses = ['pending_review', 'offer_sent', 'accepted', 'waiting_for_delivery', 'delivered', 'inspection', 'completed'];
            $user->changeCredit(300000, 'issuance', 'Completed sample trade-in #'.$quote->id, 'quote-'.$quote->id, null, null, $quote->id);
        }
        foreach ($statuses as $step => $status) {
            $quote->histories()->create(['actor_id' => $user->id, 'status' => $status, 'note' => 'Development sample history.', 'created_at' => now()->subHours(count($statuses) - $step)]);
        }
    }

    private function sampleOrder(User $user, int $index): void
    {
        $token = '00000000-0000-4000-8000-00000000000'.($index + 1);
        if ($user->orders()->where('checkout_token', $token)->exists()) {
            return;
        }
        $product = Product::vinyl()->where('stock', '>=', 1)->orderBy('id')->skip($index)->first();
        if (! $product) {
            return;
        }
        $delivery = $index !== 1;
        $price = (int) $product->price;
        $fee = $delivery ? max(0, config('storefront.delivery_fee')) : 0;
        $credit = $index === 0 ? min(100000, $price + $fee, $user->fresh()->store_credit) : 0;
        $status = ['delivered', 'ready_for_pickup', 'pending'][$index];
        $order = $user->orders()->create([
            'number' => '33H-DEMO-00'.($index + 1), 'checkout_token' => $token, 'fulfillment' => $delivery ? 'delivery' : 'pickup',
            'status' => $status, 'payment_status' => $index === 2 ? 'pending' : 'paid',
            'subtotal' => $price, 'delivery_fee' => $fee, 'credit_applied' => $credit, 'total' => $price + $fee - $credit,
            'address_snapshot' => $delivery ? $user->addresses()->first()->snapshot() : null,
            'pickup_instructions' => ! $delivery ? config('storefront.pickup_location')."\n".config('storefront.pickup_instructions') : null,
            'carrier' => $index === 0 ? 'Sample Courier' : null, 'tracking_number' => $index === 0 ? 'DEMO-DELIVERED-001' : null,
        ]);
        $order->items()->create(['product_id' => $product->id, 'name' => $product->name, 'unit_price' => $price, 'quantity' => 1, 'total' => $price]);
        $product->decrement('stock');
        $order->payment()->create(['amount' => $order->total, 'status' => $order->payment_status, 'verified_at' => $index === 2 ? null : now(), 'note' => 'Development sample, no real payment.']);
        if ($credit) {
            $user->changeCredit(-$credit, 'redemption', 'Sample purchase '.$order->number, 'order-'.$order->id, null, $order->id);
        }
        $statuses = match ($index) {
            0 => ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered'], 1 => ['pending', 'confirmed', 'ready_for_pickup'], default => ['pending']
        };
        foreach ($statuses as $step => $stage) {
            $order->histories()->create(['actor_id' => $user->id, 'status' => $stage, 'note' => 'Development sample history.', 'created_at' => now()->subHours(count($statuses) - $step)]);
        }
    }
}
