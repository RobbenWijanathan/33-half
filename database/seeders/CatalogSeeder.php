<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $genres = collect(['Ambient', 'Electronic', 'Jazz', 'Indie', 'Soul', 'Experimental'])
            ->mapWithKeys(fn ($name) => [$name => Genre::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])]);
        $labels = collect(['Half Light', 'Loose Ends', 'After Hours'])
            ->mapWithKeys(fn ($name) => [$name => Label::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])]);
        $artists = collect(['Mira Sol', 'The Night Index', 'Nala June', 'Soft Geometry', 'Low Tide Assembly', 'Arlo Vale'])
            ->mapWithKeys(fn ($name) => [$name => Artist::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'bio' => 'An independent artist exploring sounds between familiar genres.',
            ])]);

        $records = [
            ['Afterimage', 'Mira Sol', 'Electronic', 'Half Light', 28, '2026-09-18', 1, true, false],
            ['Nothing Stays Still', 'The Night Index', 'Indie', 'Loose Ends', 32, '2026-08-21', 2, true, false],
            ['Blue Hour Notes', 'Nala June', 'Jazz', 'After Hours', 30, '2026-07-10', 3, true, false],
            ['Forms in Motion', 'Soft Geometry', 'Experimental', 'Half Light', 35, '2026-06-05', 4, false, false],
            ['Shoreline Radio', 'Low Tide Assembly', 'Ambient', 'Loose Ends', 29, '2026-10-20', 5, false, true],
            ['The Last Light', 'Arlo Vale', 'Soul', 'After Hours', 34, '2026-11-12', 6, false, true],
            ['Small Hours', 'Mira Sol', 'Ambient', 'Half Light', 27, '2026-04-17', 7, false, false],
            ['No Fixed Address', 'The Night Index', 'Indie', 'Loose Ends', 31, '2026-03-06', 8, false, false],
        ];

        foreach ($records as [$name, $artist, $genre, $label, $price, $release, $cover, $featured, $preorder]) {
            $product = Product::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'A demo release for the 33½ storefront. Replace this description, artwork, and audio with your own catalog content.',
                'product_type' => 'vinyl',
                'price' => $price,
                'stock' => $preorder ? 0 : 12,
                'cover_image' => 'images/demo/cover-'.$cover.'.svg',
                'audio_preview_url' => 'audio/demo-preview.wav',
                'preview_start_time' => 0,
                'release_date' => $release,
                'format' => '12" LP',
                'label_id' => $labels[$label]->id,
                'is_featured' => $featured,
                'is_preorder' => $preorder,
            ]);
            $product->artists()->sync([$artists[$artist]->id]);
            $product->genres()->sync([$genres[$genre]->id]);
        }

        foreach ([
            ['33½ Studio Tee', 'T-shirts', 26, 9],
            ['Listening Room Tote', 'Tote bags', 18, 10],
            ['Side B Poster', 'Posters', 16, 11],
        ] as [$name, $category, $price, $cover]) {
            Product::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => 'Demo merchandise for the 33½ storefront. Sizes and variants can be added later.',
                'product_type' => 'merchandise',
                'merch_category' => $category,
                'price' => $price,
                'stock' => 15,
                'cover_image' => 'images/demo/cover-'.$cover.'.svg',
                'is_featured' => $cover === 9,
                'is_preorder' => false,
            ]);
        }
    }
}
