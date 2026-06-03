<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    /**
     * Public catalog: active books with optional search + category filter.
     */
    public function index(Request $request)
    {
        $query = Book::query()
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        // Keyword search across title and author.
        if ($term = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%");
            });
        }

        // Category filter by slug.
        $activeCategory = null;
        if ($slug = $request->query('category')) {
            $activeCategory = Category::where('slug', $slug)->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        // Sorting.
        $sort = $request->query('sort');
        match ($sort) {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $books = $query->paginate(12)->withQueryString();

        return view('books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'activeCategory' => $activeCategory,
        ]);
    }

    /**
     * Single book detail page. Route-model bound by slug (see Book::getRouteKeyName).
     */
    public function show(Book $book)
    {
        abort_unless($book->is_active, 404);

        $book->loadAvg('reviews', 'rating')
            ->loadCount('reviews')
            ->load(['category', 'reviews.user']);

        $related = Book::where('is_active', true)
            ->where('category_id', $book->category_id)
            ->whereKeyNot($book->id)
            ->latest()
            ->take(4)
            ->get();

        $user = request()->user();
        $userReview = $user ? $book->reviews->firstWhere('user_id', $user->id) : null;
        $canReview = $user && ! $userReview && $user->hasPurchased($book);

        return view('books.show', compact('book', 'related', 'canReview', 'userReview'));
    }

    /**
     * Securely stream a book's sample PDF.
     *
     * The file lives on the private "local" disk (storage/app/private), so it is
     * NOT directly web-accessible — it can only be reached through this route,
     * giving us a single place to add access control later.
     */
    public function preview(Book $book): StreamedResponse
    {
        abort_if(! $book->is_active || ! $book->preview_pdf, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($book->preview_pdf), 404);

        return $disk->response(
            $book->preview_pdf,
            "{$book->slug}-preview.pdf",
            ['Content-Type' => 'application/pdf'],
            'inline' // render in the browser tab rather than forcing a download
        );
    }
}
