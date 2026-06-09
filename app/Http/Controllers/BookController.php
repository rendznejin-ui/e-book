<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Book::query()
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($term = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('author', 'like', "%{$term}%");
            });
        }

        $activeCategory = null;
        if ($slug = $request->query('category')) {
            $activeCategory = Category::where('slug', $slug)->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $sort = $request->query('sort');
        match ($sort) {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        
        if ($request->boolean('sale')) {
            $query->whereColumn('compare_at_cents', '>', 'price_cents');
        }

        $books = $query->paginate(12)->withQueryString();

        $isLanding = ! $request->query('q') && ! $activeCategory
            && ! $request->boolean('sale') && $books->currentPage() === 1;

        $deals = collect();

        if ($isLanding) {
            $deals = Book::where('is_active', true)
                ->whereColumn('compare_at_cents', '>', 'price_cents')
                ->withAvg('reviews', 'rating')->withCount('reviews')
                ->latest()->take(8)->get();
        }

        return view('books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'activeCategory' => $activeCategory,
            'isLanding' => $isLanding,
            'deals' => $deals,
        ]);
    }

    
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
