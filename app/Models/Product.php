<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'product_type', 'merch_category', 'price',
        'condition', 'record_grade', 'sleeve_grade', 'gallery_images', 'currency', 'stock', 'cover_image', 'audio_preview_url', 'preview_start_time',
        'release_date', 'format', 'label_id', 'is_featured', 'is_preorder',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'price' => 'decimal:2',
            'release_date' => 'date',
            'is_featured' => 'boolean',
            'is_preorder' => 'boolean',
        ];
    }

    public function getFormattedPriceAttribute(): string
    {
        return Money::format((float) $this->price);
    }

    public function getCoverUrlAttribute(): string
    {
        if (str_starts_with($this->cover_image ?? '', 'storage/')) {
            return asset($this->cover_image);
        }
        $cachedCover = 'images/albums/'.$this->slug.'.webp';
        if (is_file(public_path($cachedCover))) {
            return asset($cachedCover);
        }

        return asset($this->cover_image ?: 'images/demo/placeholder.svg');
    }

    public function getGenreLineAttribute(): string
    {
        return $this->genres->take(3)->pluck('name')->join(' • ');
    }

    public function getCardGenreLineAttribute(): string
    {
        return $this->genres->take(2)->pluck('name')->join(' • ');
    }

    public function getGalleryViewsAttribute(): array
    {
        $front = ['url' => $this->cover_url, 'label' => 'Front cover'];
        $extra = array_slice($this->gallery_images ?? [], 0, 2);

        if ($extra === []) {
            return [$front, ['url' => $front['url'], 'label' => 'Cover reference']];
        }

        return [$front, ...array_map(fn (array $image) => [
            'url' => asset($image['path']),
            'label' => $image['label'],
        ], $extra)];
    }

    public function syncGenres(array $genreIds): void
    {
        $genreIds = array_values(array_unique($genreIds));
        if (count($genreIds) > 3) {
            throw new \InvalidArgumentException('A record can have at most three genres.');
        }

        $this->genres()->sync($genreIds);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function crates()
    {
        return $this->belongsToMany(Crate::class);
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

    /**
     * Music discovery search: match the term against the title, description, artist, genre or label.
     * Laravel 12 #[Scope] attribute, used as Product::search($term) or $query->search($term).
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);
        if ($term === '') {
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

    public function scopeMerchandise(Builder $query): Builder
    {
        return $query->where('product_type', 'merchandise');
    }
}
