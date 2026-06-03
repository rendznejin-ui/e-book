<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartService
{
    /** Request-scoped cache so we don't resolve the cart repeatedly. */
    private ?Cart $cart = null;

    /**
     * Resolve the active cart for the current visitor.
     *
     * - Authenticated: the user's cart (created on demand). Any lingering guest
     *   cart from before login is merged in once, then discarded.
     * - Guest: a session-tracked cart (carts.session_id), remembered via the
     *   "cart_id" session key so it survives the session-id rotation on login.
     */
    public function currentCart(): Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        if (Auth::check()) {
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            $guestCartId = session('cart_id');
            if ($guestCartId && $guestCartId !== $userCart->id) {
                if ($guestCart = Cart::with('items')->find($guestCartId)) {
                    if ($guestCart->user_id === null) {
                        $this->mergeInto($guestCart, $userCart);
                        $guestCart->delete();
                    }
                }
            }
            session()->forget('cart_id');

            return $this->cart = $userCart;
        }

        // Guest visitor.
        if ($id = session('cart_id')) {
            if ($cart = Cart::find($id)) {
                return $this->cart = $cart;
            }
        }

        $cart = Cart::create(['session_id' => session()->getId()]);
        session(['cart_id' => $cart->id]);

        return $this->cart = $cart;
    }

    /**
     * Add a book to the cart (or increase its quantity), capped at available stock.
     *
     * @throws ValidationException when the book is inactive/out of stock.
     */
    public function add(Book $book, int $quantity = 1): CartItem
    {
        $quantity = max(1, $quantity);

        if (! $book->is_active || $book->stock_qty < 1) {
            throw ValidationException::withMessages(['book' => 'This book is not available.']);
        }

        $cart = $this->currentCart();
        $item = $cart->items()->where('book_id', $book->id)->first();

        $desired = ($item?->quantity ?? 0) + $quantity;
        $desired = min($desired, $book->stock_qty); // never exceed stock

        if ($item) {
            $item->update(['quantity' => $desired]);
        } else {
            $item = $cart->items()->create([
                'book_id' => $book->id,
                'quantity' => $desired,
            ]);
        }

        return $item;
    }

    /**
     * Set an explicit quantity for a line item. Quantity <= 0 removes it.
     * Returns null when the line was removed.
     */
    public function updateQuantity(CartItem $item, int $quantity): ?CartItem
    {
        if ($quantity < 1) {
            $item->delete();

            return null;
        }

        // Clamp to current stock so a stale tab can't over-order.
        $quantity = min($quantity, $item->book->stock_qty);
        $item->update(['quantity' => max(1, $quantity)]);

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Total units in the cart (for the nav badge).
     * Read-only: does NOT create a cart, so anonymous page views and bots
     * don't spawn empty cart rows just to render a "0" badge.
     */
    public function count(): int
    {
        $cart = $this->existingCart();

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    /** Find the current visitor's cart without creating one. */
    private function existingCart(): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->first();
        }

        $id = session('cart_id');

        return $id ? Cart::find($id) : null;
    }

    public function subtotalCents(): int
    {
        return $this->currentCart()->loadMissing('items.book')->subtotalCents();
    }

    /** Merge each guest line into the destination cart, respecting stock caps. */
    private function mergeInto(Cart $from, Cart $to): void
    {
        foreach ($from->items as $guestItem) {
            $existing = $to->items()->where('book_id', $guestItem->book_id)->first();
            $book = $guestItem->book;
            if (! $book) {
                continue;
            }

            $qty = ($existing?->quantity ?? 0) + $guestItem->quantity;
            $qty = min($qty, $book->stock_qty);

            if ($existing) {
                $existing->update(['quantity' => $qty]);
            } else {
                $to->items()->create(['book_id' => $guestItem->book_id, 'quantity' => $qty]);
            }
        }
    }
}
