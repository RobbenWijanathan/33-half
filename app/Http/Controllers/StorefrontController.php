<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'newArrivals' => Product::vinyl()->with('artists')->where('is_preorder', false)->latest('release_date')->take(4)->get(),
            'featured' => Product::vinyl()->with('artists')->where('is_featured', true)->take(4)->get(),
            'preorders' => Product::vinyl()->with('artists')->where('is_preorder', true)->take(4)->get(),
            'merchandise' => Product::merchandise()->take(4)->get(),
            'genres' => Genre::orderBy('name')->get(),
            'featuredArtist' => Artist::withCount('products')->first(),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
