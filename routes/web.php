<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\TradeInController;
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
Route::redirect('/new-arrivals', '/vinyl', 301);
Route::get('/pre-orders', [CatalogController::class, 'preorders'])->name('preorders');
Route::get('/crate-digging', [CatalogController::class, 'crate'])->name('crate');
Route::get('/about', [StorefrontController::class, 'about'])->name('about');
Route::get('/contact', [StorefrontController::class, 'contact'])->name('contact');
Route::post('/currency', CurrencyController::class)->block()->name('currency.update');

Route::get('/crates/artists/{letter}', [CatalogController::class, 'artistLetter'])->where('letter', '[A-Za-z]')->name('crates.letter');
Route::get('/crates', [CatalogController::class, 'crates'])->name('crates');
Route::get('/crates/{crate:slug}', [CatalogController::class, 'curatedCrate'])->name('crates.show');

Route::get('/trade-in', [TradeInController::class, 'create'])->name('trade.create');
Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:10,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/settings', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'password'])->name('account.password');
    Route::get('/account/credits', [AccountController::class, 'credits'])->name('account.credits');
    Route::resource('addresses', AddressController::class)->only(['store', 'update', 'destroy']);
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}/add', [CartController::class, 'add'])->block()->name('cart.add');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->block()->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'remove'])->block()->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->block()->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/delivery-tracking', [OrderController::class, 'index'])->name('orders.tracking');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/trade-in', [TradeInController::class, 'store'])->middleware('throttle:10,1')->name('trade.store');
    Route::get('/your-quotes', [TradeInController::class, 'index'])->name('quotes.index');
    Route::get('/your-quotes/{quote}', [TradeInController::class, 'show'])->name('quotes.show');
    Route::patch('/your-quotes/{quote}', [TradeInController::class, 'update'])->name('quotes.update');
    Route::get('/your-quotes/{quote}/photos/{photo}', [TradeInController::class, 'photo'])->whereNumber('photo')->name('quotes.photo');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit', [Admin\DashboardController::class, 'audit'])->name('audit');
    Route::resource('records', Admin\ProductController::class)->parameters(['records' => 'product'])->except('show');
    Route::resource('merchandise', Admin\ProductController::class)->parameters(['merchandise' => 'product'])->except('show');
    Route::resource('crates', Admin\CrateController::class)->except('show');
    Route::resource('quotes', Admin\QuoteController::class)->only(['index', 'show', 'update']);
    Route::resource('orders', Admin\OrderController::class)->only(['index', 'show', 'update']);
    Route::post('/orders/{order}/payment', [Admin\OrderController::class, 'payment'])->name('orders.payment');
    Route::patch('/orders/{order}/tracking', [Admin\OrderController::class, 'tracking'])->name('orders.tracking');
    Route::resource('users', Admin\UserController::class)->only(['index', 'edit', 'update']);
    Route::post('/users/{user}/credit', [Admin\UserController::class, 'credit'])->name('users.credit');
});
