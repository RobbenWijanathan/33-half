<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable
{
    protected $attributes = ['role' => 'user', 'is_active' => true, 'store_credit' => 0];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'store_credit' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function quotes()
    {
        return $this->hasMany(TradeInQuote::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function changeCredit(int $amount, string $type, string $reason, string $reference, ?int $actorId = null, ?int $orderId = null, ?int $quoteId = null): void
    {
        DB::transaction(function () use ($amount, $type, $reason, $reference, $actorId, $orderId, $quoteId) {
            $user = self::lockForUpdate()->findOrFail($this->id);
            if (CreditTransaction::where('reference', $reference)->exists()) {
                return;
            }
            if ($user->store_credit + $amount < 0) {
                throw ValidationException::withMessages(['credit' => 'There is not enough store credit.']);
            }
            $user->store_credit += $amount;
            $user->save();
            $user->creditTransactions()->create([
                'actor_id' => $actorId, 'order_id' => $orderId, 'trade_in_quote_id' => $quoteId,
                'type' => $type, 'amount' => $amount, 'balance_after' => $user->store_credit,
                'reference' => $reference, 'reason' => $reason,
            ]);
            $this->store_credit = $user->store_credit;
        });
    }
}
