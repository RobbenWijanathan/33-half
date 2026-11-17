<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
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

    // ----- Music discovery scopes (Laravel 12 #[Scope] attribute) -----
    // Used in a controller like: Product::newArrivals()->search($term)->filter($filters)->sorted($sort)

    /** Released within the last NEW_ARRIVAL_DAYS days and not a pre-order. */
    #[Scope]
    protected function newArrivals(Builder $query): void
    {
        $query->where('is_preorder', false)
            ->whereBetween('release_date', [
                today()->subDays(self::NEW_ARRIVAL_DAYS)->toDateString(),
                today()->toDateString(),
            ]);
    }

    /** Match the term against the title, description, artist, genre or label name. */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $like = '%'.$term.'%';
        $query->where(function (Builder $search) use ($like) {
            $search->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('artists', fn (Builder $artist) => $artist->where('name', 'like', $like))
                ->orWhereHas('genres', fn (Builder $genre) => $genre->where('name', 'like', $like))
                ->orWhereHas('label', fn (Builder $label) => $label->where('name', 'like', $like));
        });
    }

    /** Narrow by genre, artist, label, price range and availability (validated request data). */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        if (! empty($filters['genre'])) {
            $query->whereHas('genres', fn (Builder $genre) => $genre->where('slug', $filters['genre']));
        }
        if (! empty($filters['artist'])) {
            $query->whereHas('artists', fn (Builder $artist) => $artist->where('slug', $filters['artist']));
        }
        if (! empty($filters['label'])) {
            $query->whereHas('label', fn (Builder $label) => $label->where('slug', $filters['label']));
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (($filters['availability'] ?? null) === 'in-stock') {
            $query->where('stock', '>', 0)->where('is_preorder', false);
        }
        if (($filters['availability'] ?? null) === 'preorder') {
            $query->where('is_preorder', true);
        }
    }

    /** Order the listing: newest (by release date), price-low, price-high or name. */
    #[Scope]
    protected function sorted(Builder $query, ?string $sort): void
    {
        if ($sort === 'price-low') {
            $query->orderBy('price');
        } elseif ($sort === 'price-high') {
            $query->orderByDesc('price');
        } elseif ($sort === 'name') {
            $query->orderBy('name');
        } else {
            $query->orderByDesc('release_date')->orderByDesc('created_at')->orderByDesc('id');
        }
    }

    public function isNewArrival(): bool
    {
        return ! $this->is_preorder
            && $this->release_date !== null
            && $this->release_date->betweenIncluded(today()->subDays(self::NEW_ARRIVAL_DAYS), today());
    }
}
