<?php

namespace App\Policies;

use App\Models\TradeInQuote;
use App\Models\User;

class TradeInQuotePolicy
{
    public function view(User $user, TradeInQuote $quote): bool
    {
        return $user->id === $quote->user_id || $user->isAdmin();
    }
}
