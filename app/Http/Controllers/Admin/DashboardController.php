<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'revenue_cents' => (int) Order::where('status', 'paid')->sum('total_cents'),
            'paid_orders' => Order::where('status', 'paid')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'customers' => User::where('role', 'customer')->count(),
            'books' => Book::count(),
        ];

        $lowStock = Book::where('is_active', true)
            ->where('stock_qty', '<', 5)
            ->orderBy('stock_qty')
            ->take(5)
            ->get();

        $recentOrders = Order::with('user')->latest()->take(8)->get();

        // Best sellers across all paid + pending orders (by snapshot title).
        $topBooks = OrderItem::query()
            ->selectRaw('title, SUM(quantity) as sold')
            ->groupBy('title')
            ->orderByDesc('sold')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'lowStock', 'recentOrders', 'topBooks'));
    }
}
