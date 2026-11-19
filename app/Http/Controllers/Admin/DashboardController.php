<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\TradeInQuote;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'users' => User::count(), 'records' => Product::vinyl()->count(), 'merchandise' => Product::merchandise()->count(),
            'orders' => Order::count(), 'completed' => Order::whereIn('status', ['delivered', 'picked_up'])->count(),
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'creditSales' => Order::where('payment_status', 'paid')->sum('credit_applied'),
            'pendingQuotes' => TradeInQuote::where('status', 'pending_review')->count(),
            'lowStock' => Product::with('variants')->where(function ($query) {
                $query->where('stock', '<=', 3)->orWhereHas('variants', fn ($variants) => $variants->where('stock', '<=', 3));
            })->take(10)->get(),
            'recentOrders' => Order::with('user')->latest()->take(6)->get(),
            'recentQuotes' => TradeInQuote::with('user')->latest()->take(6)->get(),
        ]);
    }

    public function audit(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'sort' => ['nullable', 'in:newest,oldest'], 'action' => ['nullable', 'string', 'max:60']]);
        $logs = AdminAuditLog::with('actor');
        if (! empty($data['q'])) {
            $logs->where('description', 'like', '%'.$data['q'].'%');
        }
        if (! empty($data['action'])) {
            $logs->where('action', $data['action']);
        }

        return view('admin.audit', ['logs' => $logs->orderBy('id', ($data['sort'] ?? '') === 'oldest' ? 'asc' : 'desc')->paginate(20)->withQueryString(), 'actions' => AdminAuditLog::distinct()->orderBy('action')->pluck('action')]);
    }
}
