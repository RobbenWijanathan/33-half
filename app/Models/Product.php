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
        'condition', 'record_grade', 'sleeve_grade', 'currency', 'stock', 'cover_image', 'audio_preview_url', 'preview_start_time',
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

    public function getFormattedPriceAttribute(): string
    {
        return $this->formatMoney((float) $this->price);
    }

    public function formatMoney(float $amount): string
    {
        if ($this->currency === 'IDR') {
            return 'Rp '.number_format($amount, 0, ',', '.');
        }

        return '$'.number_format($amount, 2);
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
