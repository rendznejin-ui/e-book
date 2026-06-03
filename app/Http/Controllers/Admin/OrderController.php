<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => ['pending', 'paid', 'cancelled', 'refunded'],
            'activeStatus' => $request->query('status'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load('items', 'payment', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'paid', 'cancelled', 'refunded'])],
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', "Order {$order->order_number} marked as {$data['status']}.");
    }
}
