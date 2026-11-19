<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', 'string', 'max:30'], 'payment' => ['nullable', 'in:pending,paid,cancelled,refunded'], 'fulfillment' => ['nullable', 'in:pickup,delivery'], 'sort' => ['nullable', 'in:newest,oldest,total,status']]);
        $orders = Order::with(['user', 'items']);
        if (! empty($data['q'])) {
            $orders->where(function ($query) use ($data) {
                $query->where('number', 'like', '%'.$data['q'].'%')->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$data['q'].'%')->orWhere('email', 'like', '%'.$data['q'].'%'));
            });
        }
        foreach (['status', 'fulfillment', 'payment' => 'payment_status'] as $key => $column) {
            $field = is_int($key) ? $column : $key;
            if (! empty($data[$field])) {
                $orders->where($column, $data[$field]);
            }
        }
        $sort = $data['sort'] ?? 'newest';
        $orders->orderBy(in_array($sort, ['total', 'status']) ? $sort : 'id', $sort === 'newest' ? 'desc' : 'asc');

        return view('admin.orders.index', ['orders' => $orders->paginate(15)->withQueryString()]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', ['order' => $order->load(['user', 'items', 'payment', 'histories'])]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate(['status' => ['required', 'string', 'max:30'], 'note' => ['required', 'string', 'max:2000']]);
        $order->updateStatus($data['status'], $request->user(), $data['note']);

        return back()->with('notice', 'Order updated.');
    }

    public function payment(Request $request, Order $order)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $order->verifyPayment($request->user(), $data['note']);

        return back()->with('notice', 'Manual payment verified.');
    }

    public function tracking(Request $request, Order $order)
    {
        abort_unless($order->fulfillment === 'delivery', 422);
        $data = $request->validate(['carrier' => ['required', 'string', 'max:120'], 'tracking_number' => ['required', 'string', 'max:120'], 'tracking_url' => ['nullable', 'url:http,https', 'max:1000']]);
        DB::transaction(function () use ($order, $data, $request) {
            $locked = Order::lockForUpdate()->findOrFail($order->id);
            abort_if(in_array($locked->status, ['cancelled', 'refunded']), 422);
            $locked->update($data);
            $locked->histories()->create(['actor_id' => $request->user()->id, 'status' => $locked->status, 'note' => 'Tracking updated: '.$data['carrier'].' · '.$data['tracking_number']]);
            AdminAuditLog::record('order.tracking', $locked, $data['carrier'].' · '.$data['tracking_number']);
        });

        return back()->with('notice', 'Tracking saved.');
    }
}
