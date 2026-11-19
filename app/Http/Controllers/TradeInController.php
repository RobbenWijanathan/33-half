<?php

namespace App\Http\Controllers;

use App\Models\TradeInQuote;
use App\Support\RecordGrades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TradeInController extends Controller
{
    public function create()
    {
        return view('pages.trade-in', ['grades' => RecordGrades::labels()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'artist' => ['required', 'string', 'max:120'], 'album' => ['required', 'string', 'max:160'],
            'record_grade' => ['required', Rule::in(array_keys(RecordGrades::labels()))],
            'sleeve_grade' => ['required', Rule::in(array_keys(RecordGrades::labels()))],
            'notes' => ['nullable', 'string', 'max:2000'], 'preference' => ['required', Rule::in(['cash', 'store_credit'])],
            'photos' => ['nullable', 'array', 'max:6'], 'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $photos = [];
        try {
            foreach ($request->file('photos', []) as $photo) {
                $photos[] = $photo->store('quotes', 'local');
            }
            $data['photos'] = $photos;
            $quote = DB::transaction(function () use ($request, $data) {
                $quote = $request->user()->quotes()->create($data);
                $quote->histories()->create(['actor_id' => $request->user()->id, 'status' => 'pending_review', 'note' => 'Quote request submitted.']);

                return $quote;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($photos);
            throw $exception;
        }

        return redirect()->route('quotes.show', $quote)->with('notice', 'Your request is saved. The store will review it soon.');
    }

    public function index(Request $request)
    {
        $sort = $request->validate(['sort' => ['nullable', Rule::in(['newest', 'oldest', 'status'])]])['sort'] ?? 'newest';
        $quotes = $request->user()->quotes();
        if ($sort === 'status') {
            $quotes->orderBy('status');
        }
        $quotes->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc');

        return view('pages.quotes', ['quotes' => $quotes->paginate(12)->withQueryString()]);
    }

    public function show(TradeInQuote $quote)
    {
        Gate::authorize('view', $quote);

        return view('account.quote', ['quote' => $quote->load('histories')]);
    }

    public function update(Request $request, TradeInQuote $quote)
    {
        abort_unless($request->user()->id === $quote->user_id, 403);
        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'declined', 'waiting_for_delivery', 'cancelled'])],
            'accepted_type' => ['nullable', Rule::in(['cash', 'store_credit'])], 'delivery_method' => ['nullable', Rule::in(['drop_off', 'delivery'])],
        ]);
        $quote->changeStatus($data['status'], $request->user(), $data);

        return back()->with('notice', 'Quote updated.');
    }

    public function photo(TradeInQuote $quote, int $photo)
    {
        Gate::authorize('view', $quote);
        $path = $quote->photos[$photo] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
