@props(['book'])

@php
    // These are populated when the query uses withAvg/withCount; fall back gracefully.
    $avg = (float) ($book->reviews_avg_rating ?? 0);
    $reviewCount = $book->reviews_count ?? null;
    $inStock = $book->stock_qty > 0;
    $isWishlisted = in_array($book->id, $wishlistedBookIds ?? [], true);
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-gray-200/80 bg-white overflow-hidden shadow-card hover:shadow-card-hover hover:-translate-y-1 transition duration-200']) }}>
    <button type="button" data-wishlist-toggle data-url="{{ route('wishlist.toggle', $book) }}"
            data-off="text-gray-500" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
            class="wishlist-btn absolute top-3 right-3 z-10 rounded-full bg-white/95 p-2 shadow-sm ring-1 ring-gray-200/70 backdrop-blur hover:bg-white {{ $isWishlisted ? 'text-red-500' : 'text-gray-500' }}"
            title="Toggle wishlist">
        <svg class="h-4 w-4" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
    </button>

    <a href="{{ route('books.show', $book) }}" class="block">
        <div class="aspect-[3/4] bg-stone-100 flex items-center justify-center overflow-hidden">
            @if ($book->cover_image)
                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}"
                     class="h-full w-full object-cover group-hover:scale-[1.04] transition duration-300" />
            @else
                <span class="text-5xl opacity-40">📘</span>
            @endif
        </div>
    </a>

    <div class="flex flex-col flex-1 p-4">
        <a href="{{ route('books.show', $book) }}" class="font-serif text-base font-semibold leading-snug text-gray-900 line-clamp-2 hover:text-brand-700">
            {{ $book->title }}
        </a>
        <p class="mt-1 text-sm text-gray-500">{{ $book->author }}</p>

        <div class="mt-2">
            <x-rating-stars :rating="$avg" :count="$reviewCount" />
        </div>

        <div class="mt-auto pt-4 flex items-center justify-between gap-2">
            <span class="text-lg font-bold text-gray-900">${{ $book->price }}</span>
            @if ($inStock)
                <button type="button"
                        data-add-to-cart data-book-id="{{ $book->id }}"
                        class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add
                </button>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">Sold out</span>
            @endif
        </div>
    </div>
</div>
