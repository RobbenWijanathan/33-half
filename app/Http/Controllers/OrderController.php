<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->latest();
        if ($request->routeIs('orders.tracking')) {
            $orders->where('fulfillment', 'delivery');
        }

        return view('account.orders', ['orders' => $orders->paginate(12), 'tracking' => $request->routeIs('orders.tracking')]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        return view('account.order', ['order' => $order->load(['items', 'payment', 'histories'])]);
    }
}
