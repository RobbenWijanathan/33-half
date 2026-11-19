<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['amount' => 'integer', 'verified_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
