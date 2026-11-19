<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradeInQuote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'string', 'max:30'], 'sort' => ['nullable', 'in:newest,oldest,status,user']]);
        $quotes = TradeInQuote::with('user');
        if (! empty($data['q'])) {
            $quotes->where(function ($query) use ($data) {
                $query->where('album', 'like', '%'.$data['q'].'%')->orWhere('artist', 'like', '%'.$data['q'].'%')->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$data['q'].'%')->orWhere('email', 'like', '%'.$data['q'].'%'));
            });
        }
        if (! empty($data['status'])) {
            $quotes->where('status', $data['status']);
        }
        $sort = $data['sort'] ?? 'newest';
        if ($sort === 'user') {
            $quotes->join('users', 'users.id', '=', 'trade_in_quotes.user_id')->select('trade_in_quotes.*')->orderBy('users.name');
        } elseif ($sort === 'status') {
            $quotes->orderBy('status');
        }
        $quotes->orderBy('trade_in_quotes.id', $sort === 'oldest' ? 'asc' : 'desc');

        return view('admin.quotes.index', ['quotes' => $quotes->paginate(15)->withQueryString()]);
    }

    public function show(TradeInQuote $quote)
    {
        return view('admin.quotes.show', ['quote' => $quote->load(['user', 'histories'])]);
    }

    public function update(Request $request, TradeInQuote $quote)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['offer_sent', 'delivered', 'inspection', 'completed', 'rejected', 'cancelled'])],
            'cash_offer' => ['nullable', 'integer', 'min:1', 'max:1000000000'], 'credit_offer' => ['nullable', 'integer', 'min:1', 'max:1000000000'],
            'offer_notes' => ['nullable', 'string', 'max:2000'], 'note' => ['required', 'string', 'max:2000'],
        ]);
        $quote->changeStatus($data['status'], $request->user(), $data);

        return back()->with('notice', 'Quote updated.');
    }
}
