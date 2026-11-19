<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'sku', 'price', 'stock'];

    protected $casts = ['price' => 'integer', 'stock' => 'integer'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
