<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/shop', [CatalogController::class, 'shop'])->name('shop');
Route::get('/vinyl', [CatalogController::class, 'vinyl'])->name('vinyl');
Route::get('/merch', [CatalogController::class, 'merchandise'])->name('merch');
Route::get('/merch/{product:slug}', [CatalogController::class, 'show'])->name('merch.show');
Route::get('/records/{product:slug}', [CatalogController::class, 'show'])->name('products.show');
Route::get('/artists', [CatalogController::class, 'artists'])->name('artists.index');
Route::get('/artists/{artist:slug}', [CatalogController::class, 'artist'])->name('artists.show');
Route::get('/genres', [CatalogController::class, 'genres'])->name('genres.index');
Route::get('/genres/{genre:slug}', [CatalogController::class, 'genre'])->name('genres.show');
Route::get('/new-arrivals', [CatalogController::class, 'newArrivals'])->name('new-arrivals');
Route::get('/pre-orders', [CatalogController::class, 'preorders'])->name('preorders');
Route::get('/crate-digging', [CatalogController::class, 'crate'])->name('crate');
Route::get('/about', [StorefrontController::class, 'about'])->name('about');
Route::get('/contact', [StorefrontController::class, 'contact'])->name('contact');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
