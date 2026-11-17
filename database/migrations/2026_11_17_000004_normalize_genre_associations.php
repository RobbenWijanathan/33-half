<?php

use App\Support\GenreTaxonomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('products')->where('product_type', 'vinyl')->select('id')->chunkById(100, function ($products) {
                $genresByProduct = DB::table('genre_product')
                    ->join('genres', 'genres.id', '=', 'genre_product.genre_id')
                    ->whereIn('product_id', $products->pluck('id'))
                    ->orderBy('genre_id')->get(['product_id', 'genre_id', 'genres.name'])
                    ->groupBy('product_id');

                foreach ($genresByProduct as $productId => $genres) {
                    $parentIds = collect(GenreTaxonomy::normalize($genres->pluck('name')))
                        ->map(function (string $parent) {
                            $slug = Str::slug($parent);
                            DB::table('genres')->insertOrIgnore([
                                'slug' => $slug, 'name' => $parent,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);

                            return DB::table('genres')->where('slug', $slug)->value('id');
                        });

                    DB::table('genre_product')->where('product_id', $productId)
                        ->whereNotIn('genre_id', $parentIds)->delete();
                    DB::table('genre_product')->insertOrIgnore($parentIds->map(fn ($genreId) => [
                        'product_id' => $productId,
                        'genre_id' => $genreId,
                    ])->all());
                }
            });
        });
    }

    public function down(): void
    {
        // Merged associations cannot reliably be expanded into the original subgenres.
    }
};
