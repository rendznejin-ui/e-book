<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /**
     * Turn the current cart into a pending order with an initiated payment.
     *
     * Everything happens inside one transaction. Book rows are locked
     * (lockForUpdate) while we validate stock and decrement it, so two buyers
     * racing for the last copy can't both succeed. Prices are read from the
     * locked rows — never trusted from the client — and snapshotted onto the
     * order items so later price edits never rewrite this invoice.
     *
     * @param  array{shipping_name:string,shipping_address:string,shipping_phone:string}  $shipping
     *
     * @throws ValidationException when the cart is empty or stock is insufficient.
     */
    public function placeOrder(User $user, array $shipping): Order
    {
        return DB::transaction(function () use ($user, $shipping) {
            $cart = $this->cart->currentCart()->load('items.book');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $subtotal = 0;
            $lines = [];

            foreach ($cart->items as $item) {
                /** @var Book|null $book */
                $book = Book::whereKey($item->book_id)->lockForUpdate()->first();

                if (! $book || ! $book->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => "“{$item->book->title}” is no longer available.",
                    ]);
                }

                if ($book->stock_qty < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Not enough stock for “{$book->title}” — only {$book->stock_qty} left.",
                    ]);
                }

                $subtotal += $book->price_cents * $item->quantity;
                $lines[] = ['book' => $book, 'qty' => $item->quantity];
            }

            $taxPercent = (float) config('store.tax_percent', 0);
            $tax = (int) round($subtotal * $taxPercent / 100);
            $total = $subtotal + $tax;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->uniqueOrderNumber(),
                'status' => 'pending',
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
                'shipping_name' => $shipping['shipping_name'],
                'shipping_address' => $shipping['shipping_address'],
                'shipping_phone' => $shipping['shipping_phone'],
            ]);

            foreach ($lines as $line) {
                /** @var Book $book */
                $book = $line['book'];

                $order->items()->create([
                    'book_id' => $book->id,
                    'title' => $book->title,                 // snapshot
                    'unit_price_cents' => $book->price_cents, // snapshot
                    'quantity' => $line['qty'],
                ]);

                $book->decrement('stock_qty', $line['qty']);
            }

            $order->payment()->create([
                'gateway' => 'sandbox_qr',
                'transaction_ref' => $this->uniqueTransactionRef(),
                'amount_cents' => $total,
                'status' => 'initiated',
            ]);

            return $order->load('payment', 'items');
        });
    }

    /**
     * Mark a pending order as paid. Idempotent: confirming an order that is
     * already paid (e.g. a double-clicked button or replayed request) is a
     * no-op that simply returns the order.
     */
    public function confirmPayment(Order $order): Order
    {
        if ($order->status !== 'pending') {
            return $order;
        }

        return DB::transaction(function () use ($order) {
            $order->payment()->update(['status' => 'succeeded', 'paid_at' => now()]);
            $order->update(['status' => 'paid']);

            // The purchase is complete — empty the buyer's cart.
            $this->cart->currentCart()->items()->delete();

            return $order->fresh('payment', 'items');
        });
    }

    /**
     * Cancel a pending order and release the stock it had reserved.
     */
    public function cancel(Order $order): Order
    {
        if ($order->status !== 'pending') {
            return $order;
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->book_id) {
                    Book::whereKey($item->book_id)->lockForUpdate()->first()
                        ?->increment('stock_qty', $item->quantity);
                }
            }

            $order->payment()->update(['status' => 'failed']);
            $order->update(['status' => 'cancelled']);

            return $order->fresh('payment', 'items');
        });
    }

    private function uniqueOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    private function uniqueTransactionRef(): string
    {
        do {
            $ref = 'TXN-'.strtoupper(Str::random(16));
        } while (Payment::where('transaction_ref', $ref)->exists());

        return $ref;
    }
}
