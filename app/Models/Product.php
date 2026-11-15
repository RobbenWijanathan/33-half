<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    /** How long after its release date a product still counts as a new arrival. */
    public const NEW_ARRIVAL_DAYS = 90;

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

    /** Released within the last NEW_ARRIVAL_DAYS days and not a pre-order. */
    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->where('is_preorder', false)
            ->whereBetween('release_date', [
                today()->subDays(self::NEW_ARRIVAL_DAYS)->toDateString(),
                today()->toDateString(),
            ]);
    }

    public function isNewArrival(): bool
    {
        return ! $this->is_preorder
            && $this->release_date !== null
            && $this->release_date->betweenIncluded(today()->subDays(self::NEW_ARRIVAL_DAYS), today());
    }
}
