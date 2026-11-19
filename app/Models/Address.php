<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['label', 'recipient', 'phone', 'street', 'city', 'province', 'postal_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function snapshot(): array
    {
        return $this->only(['recipient', 'phone', 'street', 'city', 'province', 'postal_code']);
    }
}
