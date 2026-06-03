<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /** The current user's wishlisted books. */
    public function index(Request $request)
    {
        $books = $request->user()->wishlistBooks()
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest('wishlists.created_at')
            ->get();

        return view('wishlist.index', compact('books'));
    }

    /** Toggle a book in/out of the wishlist (AJAX). */
    public function toggle(Request $request, Book $book): JsonResponse
    {
        // toggle() returns ['attached' => [...], 'detached' => [...]].
        $result = $request->user()->wishlistBooks()->toggle($book->id);

        $wishlisted = ! empty($result['attached']);

        return response()->json([
            'wishlisted' => $wishlisted,
            'message' => $wishlisted ? 'Added to your wishlist.' : 'Removed from your wishlist.',
            'count' => $request->user()->wishlistBooks()->count(),
        ]);
    }
}
