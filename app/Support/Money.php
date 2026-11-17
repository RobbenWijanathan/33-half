<?php

namespace App\Support;

class Money
{
    public static function currency(): string
    {
        return session('currency', 'IDR');
    }

    public static function rate(): float
    {
        return max(1, config('storefront.idr_per_usd'));
    }

    public static function fromDisplay(float $amount): float
    {
        return self::currency() === 'USD' ? $amount * self::rate() : $amount;
    }

    public static function format(float $rupiah): string
    {
        return self::currency() === 'USD'
            ? '$'.number_format($rupiah / self::rate(), 2)
            : 'Rp '.number_format($rupiah, 0, ',', '.');
    }
}
