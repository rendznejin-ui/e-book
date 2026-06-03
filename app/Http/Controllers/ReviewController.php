<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Create or update the current user's review for a purchased book. */
    public function store(Request $request, Book $book)
    {
        $user = $request->user();

        // Verified-purchase rule: only buyers of a paid order may review.
        abort_unless($user->hasPurchased($book), 403, 'You can only review books you have purchased.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        // updateOrCreate honours the unique (user_id, book_id) constraint —
        // submitting again edits the existing review rather than erroring.
        Review::updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        return back()->with('success', 'Thanks — your review has been saved.');
    }

    /** Remove the current user's own review. */
    public function destroy(Review $review)
    {
        abort_unless($review->user_id === auth()->id(), 403);

        $review->delete();

        return back()->with('success', 'Your review was removed.');
    }
}
