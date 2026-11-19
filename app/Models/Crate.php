<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'color', 'cover_image'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
