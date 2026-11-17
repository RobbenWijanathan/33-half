<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'newArrivals' => Product::vinyl()->with(['artists', 'genres'])
                ->where('is_preorder', false)->latest('id')->take(4)->get(),
            'merchandise' => Product::merchandise()->take(2)->get(),
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
