<?php

namespace App\Http\Controllers;

use App\Support\RecordGrades;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TradeInController extends Controller
{
    public function create(): View
    {
        return view('pages.trade-in', ['grades' => RecordGrades::labels()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'artist' => ['required', 'string', 'max:120'],
            'album' => ['required', 'string', 'max:160'],
            'record_grade' => ['required', Rule::in(array_keys(RecordGrades::labels()))],
            'sleeve_grade' => ['required', Rule::in(array_keys(RecordGrades::labels()))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'preference' => ['required', Rule::in(['cash', 'store_credit'])],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        // The design's demo flow saves session details, not a real offer.
        $data['notes'] = $data['notes'] ?? '';
        $data['photos'] = collect($request->file('photos', []))
            ->map(fn ($photo) => Str::limit($photo->getClientOriginalName(), 200))
            ->all();
        $data['id'] = 'Q-'.Str::upper(Str::random(8));
        $data['status'] = 'pending_review';
        $data['submitted_at'] = now()->format('d M Y, H:i');

        $quotes = $request->session()->get('quotes', []);
        array_unshift($quotes, $data);
        $request->session()->put('quotes', array_slice($quotes, 0, 50));

        return redirect()->route('quotes.index')
            ->with('notice', 'Your demo request has been saved. No request has been sent to the store.');
    }

    public function index(Request $request): View
    {
        return view('pages.quotes', ['quotes' => $request->session()->get('quotes', [])]);
    }
}
