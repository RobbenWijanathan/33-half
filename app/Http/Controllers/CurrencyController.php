<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate(['currency' => ['required', 'in:IDR,USD']]);
        $request->session()->put('currency', $data['currency']);

        return back();
    }
}
