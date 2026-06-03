<x-storefront-layout>
    <x-slot name="title">My Wishlist</x-slot>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Wishlist</h1>

    @if ($books->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
            <p class="text-4xl mb-2">🤍</p>
            <p class="text-gray-600">Your wishlist is empty.</p>
            <a href="{{ route('books.index') }}" class="mt-4 inline-block rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                Discover books
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($books as $book)
                <x-book-card :book="$book" />
            @endforeach
        </div>
    @endif
</x-storefront-layout>
