@props(['book'])

@php
    // These are populated when the query uses withAvg/withCount; fall back gracefully.
    $avg = (float) ($book->reviews_avg_rating ?? 0);
    $reviewCount = $book->reviews_count ?? null;
    $inStock = $book->stock_qty > 0;
    $isWishlisted = in_array($book->id, $wishlistedBookIds ?? [], true);
    $onSale = $book->onSale();
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col rounded-xl border border-gray-200/80 bg-white overflow-hidden shadow-card hover:shadow-card-hover hover:-translate-y-1 transition duration-200']) }}>
    {{-- Discount badge --}}
    @if ($onSale)
        <span class="absolute top-3 left-0 z-10 rounded-r-md bg-accent-500 px-2 py-1 text-xs font-bold text-white shadow-sm">
            {{ $book->discountPercent() }}% OFF
        </span>
    @endif

    {{-- Wishlist --}}
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
        <p class="mt-1 text-sm text-gray-500 line-clamp-1">{{ $book->author }}</p>

        <div class="mt-2 flex items-center gap-1.5">
            <x-rating-stars :rating="$avg" :count="$reviewCount" />
        </div>

        {{-- Price block --}}
        <div class="mt-3">
            <div class="flex items-baseline gap-2">
                <span class="text-xl font-bold text-gray-900">${{ $book->price }}</span>
                @if ($onSale)
                    <span class="text-sm text-gray-400 line-through">${{ $book->comparePrice }}</span>
                @endif
            </div>
            @if ($onSale)
                <p class="mt-0.5 text-xs font-medium text-accent-700">
                    You save ${{ number_format(($book->compare_at_cents - $book->price_cents) / 100, 2) }}
                </p>
            @endif
        </div>

        <p class="mt-2 text-[11px] uppercase tracking-wide text-gray-400">BPU Store · ships in 2–4 days</p>

        <div class="mt-3">
            @if ($inStock)
                <button type="button" data-add-to-cart data-book-id="{{ $book->id }}"
                        class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-.534 1.872-1.5 2.182-3l.318-2.5H6.106M7.5 14.25 5.106 5.272"/></svg>
                    Add to Cart
                </button>
            @else
                <button type="button" disabled
                        class="w-full rounded-lg bg-gray-100 px-3 py-2.5 text-sm font-semibold text-gray-400 cursor-not-allowed">
                    Sold out
                </button>
            @endif
        </div>
    </div>
</div>
