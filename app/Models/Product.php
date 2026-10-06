<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'product_type', 'merch_category', 'price',
        'stock', 'cover_image', 'audio_preview_url', 'preview_start_time',
        'release_date', 'format', 'label_id', 'is_featured', 'is_preorder',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'release_date' => 'date',
            'is_featured' => 'boolean',
            'is_preorder' => 'boolean',
        ];
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    public function scopeVinyl(Builder $query): Builder
    {
        return $query->where('product_type', 'vinyl');
    }

    public function scopeMerchandise(Builder $query): Builder
    {
        return $query->where('product_type', 'merchandise');
    }
}
