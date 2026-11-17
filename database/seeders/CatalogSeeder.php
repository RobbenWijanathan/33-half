<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Product;
use App\Support\GenreTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $albums = json_decode(
            file_get_contents(database_path('data/albums.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        DB::transaction(function () use ($albums) {
            // Insert in reverse chart order so the first selections appear first in New Arrivals.
            foreach (array_reverse($albums, true) as $rank => $album) {
                $this->seedAlbum($album, $rank);
            }

            $this->seedMerchandise();
        });
    }

    private function seedAlbum(array $album, int $rank): void
    {
        $artist = Artist::firstOrCreate(
            ['slug' => Str::slug($album['artist'])],
            ['name' => $album['artist']],
        );

        $preorder = in_array($album['slug'], ['animals', 'red'], true);
        $used = ! $preorder && $rank % 3 === 2;

        $product = Product::firstOrCreate(['slug' => $album['slug']], [
            'name' => $album['title'],
            'description' => $album['description'],
            'gallery_images' => $album['gallery'] ?? null,
            'product_type' => 'vinyl',
            'price' => $album['price'],
            'currency' => 'IDR',
            'stock' => $preorder ? 0 : ($used ? 2 : 8),
            'condition' => $used ? 'used' : 'new',
            'record_grade' => $used ? 'VG+' : null,
            'sleeve_grade' => $used ? 'VG' : null,
            'cover_image' => $album['artwork'],
            'audio_preview_url' => $album['preview'] ?? null,
            'release_date' => $album['released'],
            'format' => 'Vinyl LP',
            'is_featured' => $rank < 4,
            'is_preorder' => $preorder,
        ]);

        if (! $product->gallery_images && ! empty($album['gallery'])) {
            $product->update(['gallery_images' => $album['gallery']]);
        }

        $product->artists()->syncWithoutDetaching([$artist->id]);
        $genreIds = collect(GenreTaxonomy::normalize($album['genres']))->map(fn (string $genre) => Genre::firstOrCreate(
            ['slug' => Str::slug($genre)],
            ['name' => $genre],
        )->id);
        $product->syncGenres($genreIds->all());
    }

    private function seedMerchandise(): void
    {
        foreach ([
            ['33½ Studio Tee', 'T-shirts', 416000, 9],
            ['Listening Room Tote', 'Tote bags', 288000, 10],
            ['Side B Poster', 'Posters', 256000, 11],
        ] as [$name, $category, $price, $cover]) {
            Product::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => match ($category) {
                    'T-shirts' => 'A charcoal shop tee with the 33½ Records mark across the chest.',
                    'Tote bags' => 'A roomy record tote with the 33½ Records mark printed on the front.',
                    default => 'A record-room print with warm paper tones and the 33½ Records mark.',
                },
                'product_type' => 'merchandise',
                'merch_category' => $category,
                'price' => $price,
                'currency' => 'IDR',
                'stock' => 15,
                'cover_image' => 'images/demo/cover-'.$cover.'.svg',
                'is_featured' => $cover === 9,
                'is_preorder' => false,
            ]);
        }
    }
}
