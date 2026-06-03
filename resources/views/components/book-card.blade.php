@props(['book'])

@php
    // These are populated when the query uses withAvg/withCount; fall back gracefully.
    $avg = (float) ($book->reviews_avg_rating ?? 0);
    $reviewCount = $book->reviews_count ?? null;
    $inStock = $book->stock_qty > 0;
    $isWishlisted = in_array($book->id, $wishlistedBookIds ?? [], true);
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-lg border border-gray-200 bg-white overflow-hidden hover:shadow-md transition']) }}>
    <button type="button" data-wishlist-toggle data-url="{{ route('wishlist.toggle', $book) }}"
            data-off="text-gray-400" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
            class="wishlist-btn absolute top-2 right-2 z-10 rounded-full bg-white/90 p-1.5 shadow-sm hover:bg-white {{ $isWishlisted ? 'text-red-500' : 'text-gray-400' }}"
            title="Toggle wishlist">
        <svg class="h-5 w-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
    </button>

    <a href="{{ route('books.show', $book) }}" class="block">
        <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center overflow-hidden">
            @if ($book->cover_image)
                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}"
                     class="h-full w-full object-cover group-hover:scale-105 transition" />
            @else
                <span class="text-5xl opacity-40">📘</span>
            @endif
        </div>
    </a>

    <div class="flex flex-col flex-1 p-4">
        <a href="{{ route('books.show', $book) }}" class="font-semibold text-gray-900 line-clamp-2 hover:text-indigo-600">
            {{ $book->title }}
        </a>
        <p class="mt-1 text-sm text-gray-500">{{ $book->author }}</p>

        <div class="mt-2">
            <x-rating-stars :rating="$avg" :count="$reviewCount" />
        </div>

        <div class="mt-auto pt-4 flex items-center justify-between">
            <span class="text-lg font-bold text-gray-900">${{ $book->price }}</span>
            @if ($inStock)
                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">In stock</span>
            @else
                <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Out of stock</span>
            @endif
        </div>

        <button type="button"
                data-add-to-cart data-book-id="{{ $book->id }}"
                @disabled(! $inStock)
                class="mt-3 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Add to cart
        </button>
    </div>
</div>
