<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly ReceiptService $receipts)
    {
    }

    /** The signed-in user's order history. */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /** On-screen receipt / order detail. */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items', 'payment', 'user');

        return view('orders.show', compact('order'));
    }

    /** Download the same receipt as a PDF. */
    public function receipt(Order $order)
    {
        $this->authorize('view', $order);

        return $this->receipts->pdf($order)
            ->download("receipt-{$order->order_number}.pdf");
    }
}
