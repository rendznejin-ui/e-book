<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Payment\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly CartService $cart,
        private readonly PaymentGateway $gateway,
    ) {
    }

    /** Step 1 — shipping details + order summary (server-computed). */
    public function create(Request $request)
    {
        $cart = $this->cart->currentCart()->load('items.book');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $subtotal = $cart->subtotalCents();
        $tax = (int) round($subtotal * (float) config('store.tax_percent', 0) / 100);

        return view('checkout.create', [
            'items' => $cart->items,
            'subtotalCents' => $subtotal,
            'taxCents' => $tax,
            'totalCents' => $subtotal + $tax,
            'user' => $request->user(),
        ]);
    }

    /** Step 2 — create the pending order + QR, then go to the payment screen. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'shipping_phone' => ['required', 'string', 'max:30'],
        ]);

        $order = $this->checkout->placeOrder($request->user(), $data);

        return redirect()->route('checkout.payment', $order);
    }

    /** Step 3 — show the mock QR + "simulate approval". */
    public function payment(Order $order)
    {
        $this->authorize('view', $order);

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }
        if ($order->status !== 'pending') {
            return redirect()->route('cart.index');
        }

        $order->loadMissing('payment', 'items');
        $qr = $this->gateway->createQrPayload($order->payment);

        $qrSvg = QrCode::format('svg')->size(240)->margin(1)->generate($qr['payload']);

        return view('checkout.payment', [
            'order' => $order,
            'qrSvg' => $qrSvg,
            'signature' => $qr['signature'],
        ]);
    }

    /** AJAX poll used by the payment screen to detect approval. */
    public function status(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json(['status' => $order->status]);
    }

    /** Step 4 — sandbox approval (verifies the signed QR, idempotent). */
    public function confirm(Request $request, Order $order): JsonResponse
    {
        $this->authorize('pay', $order);
        $order->loadMissing('payment');

        if ($order->status === 'pending') {
            $valid = $this->gateway->verify(
                $order->payment->transaction_ref,
                $order->payment->amount_cents,
                (string) $request->input('signature'),
            );

            if (! $valid) {
                return response()->json(['message' => 'Invalid payment signature.'], 422);
            }

            $this->checkout->confirmPayment($order);
        }

        return response()->json([
            'status' => 'paid',
            'redirect' => route('checkout.success', $order),
        ]);
    }

    /** Abandon a pending order and release its reserved stock. */
    public function cancel(Order $order)
    {
        $this->authorize('pay', $order);
        $this->checkout->cancel($order);

        return redirect()->route('cart.index')->with('success', 'Your pending order was cancelled.');
    }

    /** Step 5 — post-payment confirmation screen. */
    public function success(Order $order)
    {
        $this->authorize('view', $order);

        if ($order->status !== 'paid') {
            return redirect()->route('checkout.payment', $order);
        }

        $order->load('items', 'payment');

        return view('checkout.success', compact('order'));
    }
}
