<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->with('category')
            ->when($request->query('q'), fn ($q, $term) =>
                $q->where('title', 'like', "%{$term}%")->orWhere('author', 'like', "%{$term}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        return view('admin.books.create', [
            'book' => new Book(['is_active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBook($request);

        $book = new Book();
        $this->fill($book, $data, $request);
        $book->save();

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” created.");
    }

    public function edit(Book $book)
    {
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $data = $this->validateBook($request, $book);
        $this->fill($book, $data, $request);
        $book->save();

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” updated.");
    }

    public function destroy(Book $book)
    {
        $book->delete(); // soft delete

        return redirect()->route('admin.books.index')->with('success', "“{$book->title}” deleted.");
    }

    /** @return array<string,mixed> */
    private function validateBook(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book)],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:2048'],          // 2 MB
            'preview_pdf' => ['nullable', 'mimes:pdf', 'max:10240'],     // 10 MB
        ]);
    }

    private function fill(Book $book, array $data, Request $request): void
    {
        $book->title = $data['title'];
        $book->author = $data['author'];
        $book->category_id = $data['category_id'];
        $book->isbn = $data['isbn'] ?? null;
        $book->description = $data['description'] ?? null;
        $book->price_cents = (int) round($data['price'] * 100);
        $book->stock_qty = $data['stock_qty'];
        $book->is_active = $request->boolean('is_active');

        if (! $book->slug || $book->isDirty('title')) {
            $book->slug = $this->uniqueSlug($data['title'], $book);
        }

        // Cover goes on the public disk (web-visible via storage:link).
        if ($request->hasFile('cover_image')) {
            $book->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        // Preview PDF goes on the private disk (served only via the secure route).
        if ($request->hasFile('preview_pdf')) {
            $book->preview_pdf = $request->file('preview_pdf')->store('previews', 'local');
        }
    }

    private function uniqueSlug(string $title, Book $book): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (Book::where('slug', $slug)->whereKeyNot($book->id ?? 0)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
