<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReferenceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Sample listings from the design. Prices and grades are demo data.
        $records = [
            ['Kid A', 'Radiohead', 'Alternative', 'kid-a', 450000, 'used', 'VG+', 'VG'],
            ['Vespertine', 'Björk', 'Electronic', 'vespertine', 500000, 'new', 'M', 'M'],
            ['Illmatic', 'Nas', 'Hip-Hop', 'illmatic', 350000, 'used', 'VG+', 'VG+'],
            ['Selected Ambient Works 85–92', 'Aphex Twin', 'Ambient', 'ambient', 550000, 'used', 'NM', 'VG+'],
        ];

        foreach ($records as [$title, $artistName, $genreName, $image, $price, $condition, $recordGrade, $sleeveGrade]) {
            $artist = Artist::firstOrCreate(['slug' => Str::slug($artistName)], ['name' => $artistName]);
            $genre = Genre::firstOrCreate(['slug' => Str::slug($genreName)], ['name' => $genreName]);
            $product = Product::firstOrCreate(['slug' => Str::slug($title)], [
                'name' => $title,
                'description' => 'A sample listing from the 33½ design collection. Price, stock, and grades are for demonstration; confirm the pressing and condition before offering this record for sale.',
                'product_type' => 'vinyl',
                'price' => $price,
                'currency' => 'IDR',
                'condition' => $condition,
                'record_grade' => $recordGrade,
                'sleeve_grade' => $sleeveGrade,
                'stock' => 3,
                'cover_image' => 'images/design/'.$image.'.webp',
                'format' => '12" LP',
                'is_preorder' => false,
            ]);
            $product->artists()->syncWithoutDetaching([$artist->id]);
            $product->genres()->syncWithoutDetaching([$genre->id]);
        }
    }
}
