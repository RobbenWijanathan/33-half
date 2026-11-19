<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteHistory extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
