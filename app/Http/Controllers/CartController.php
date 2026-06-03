<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /** Cart page. */
    public function index()
    {
        $cart = $this->cart->currentCart()->load('items.book');

        return view('cart.index', [
            'items' => $cart->items,
            'subtotalCents' => $cart->subtotalCents(),
        ]);
    }

    /** Add a book to the cart (AJAX). */
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $book = Book::findOrFail($data['book_id']);
        $this->cart->add($book, $data['quantity'] ?? 1);

        return $this->summary("“{$book->title}” added to your cart.");
    }

    /** Change a line item's quantity (AJAX). */
    public function update(Request $request, CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $updated = $this->cart->updateQuantity($item, $data['quantity']);

        return $this->summary(
            $updated ? 'Cart updated.' : 'Item removed.',
            extra: [
                'removed' => $updated === null,
                'line_total' => $updated
                    ? number_format($updated->lineTotalCents() / 100, 2)
                    : null,
            ]
        );
    }

    /** Remove a line item (AJAX). */
    public function remove(CartItem $item): JsonResponse
    {
        $this->authorizeItem($item);
        $this->cart->remove($item);

        return $this->summary('Item removed.', extra: ['removed' => true]);
    }

    /** Ensure the line item belongs to the current visitor's cart. */
    private function authorizeItem(CartItem $item): void
    {
        abort_unless($item->cart_id === $this->cart->currentCart()->id, 403);
    }

    /** Standard JSON payload describing cart state after a mutation. */
    private function summary(string $message, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'count' => $this->cart->count(),
            'subtotal' => number_format($this->cart->subtotalCents() / 100, 2),
        ], $extra));
    }
}
