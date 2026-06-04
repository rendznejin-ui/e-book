<x-storefront-layout>
    <x-slot name="title">{{ $book->title }}</x-slot>

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('books.index') }}" class="hover:text-gray-700">Books</a>
        <span class="mx-2">/</span>
        <a href="{{ route('books.index', ['category' => $book->category->slug]) }}" class="hover:text-gray-700">{{ $book->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-700">{{ $book->title }}</span>
    </nav>

    <div class="grid md:grid-cols-3 gap-10">
        {{-- Cover --}}
        <div class="md:col-span-1">
            <div class="aspect-[3/4] rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                @if ($book->cover_image)
                    <img src="{{ asset('storage/'.$book->cover_image) }}" alt="{{ $book->title }}" class="h-full w-full object-cover" />
                @else
                    <span class="text-7xl opacity-40">📘</span>
                @endif
            </div>
        </div>

        {{-- Details --}}
        <div class="md:col-span-2">
            <h1 class="font-serif text-4xl font-semibold leading-tight text-gray-900">{{ $book->title }}</h1>
            <p class="mt-2 text-lg text-gray-600">by {{ $book->author }}</p>

            <div class="mt-3 flex items-center gap-3">
                <x-rating-stars :rating="(float) ($book->reviews_avg_rating ?? 0)" :count="$book->reviews_count" />
                <span class="text-sm text-gray-400">·</span>
                <span class="text-sm text-gray-500">{{ $book->category->name }}</span>
            </div>

            <p class="mt-6 text-3xl font-bold text-gray-900">${{ $book->price }}</p>

            <div class="mt-2">
                @if ($book->stock_qty > 0)
                    <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                        In stock ({{ $book->stock_qty }} available)
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-sm font-medium text-red-700">Out of stock</span>
                @endif
            </div>

            {{-- Actions --}}
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="button"
                        data-add-to-cart data-book-id="{{ $book->id }}"
                        @disabled($book->stock_qty < 1)
                        class="rounded-md bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Add to cart
                </button>

                @if ($book->preview_pdf)
                    <a href="{{ route('books.preview', $book) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-md border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Preview PDF
                    </a>
                @endif

                @php $isWishlisted = in_array($book->id, $wishlistedBookIds ?? [], true); @endphp
                <button type="button" data-wishlist-toggle data-url="{{ route('wishlist.toggle', $book) }}"
                        data-off="text-gray-700" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                        class="wishlist-btn inline-flex items-center gap-2 rounded-md border border-gray-300 px-6 py-3 text-sm font-semibold hover:bg-gray-50 {{ $isWishlisted ? 'text-red-500' : 'text-gray-700' }}">
                    <svg class="h-5 w-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <span class="wishlist-label">{{ $isWishlisted ? 'Wishlisted' : 'Wishlist' }}</span>
                </button>
            </div>

            {{-- Description --}}
            <div class="mt-8 prose prose-sm max-w-none text-gray-700">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Description</h2>
                <p class="mt-2">{{ $book->description ?: 'No description available.' }}</p>
            </div>
        </div>
    </div>

    {{-- ===================== Reviews ===================== --}}
    <section class="mt-14">
        <h2 class="text-xl font-bold text-gray-900 mb-4">
            Reviews <span class="text-gray-400 font-normal">({{ $book->reviews_count }})</span>
        </h2>

        {{-- Write-a-review form (verified purchasers who haven't reviewed yet) --}}
        @auth
            @if ($canReview)
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">Write a review</h3>
                    <form method="POST" action="{{ route('reviews.store', $book) }}" class="space-y-3">
                        @csrf
                        {{-- Star radio picker (CSS peer-based highlight) --}}
                        <div class="flex flex-row-reverse justify-end gap-1" id="rating-picker">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" class="peer sr-only" @checked(old('rating') == $i) required>
                                <label for="star{{ $i }}" title="{{ $i }} star"
                                       class="cursor-pointer text-2xl text-gray-300 hover:text-amber-400 peer-checked:text-amber-400">★</label>
                            @endfor
                        </div>
                        <textarea name="comment" rows="3" maxlength="2000" placeholder="Share your thoughts (optional)…"
                                  class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('comment') }}</textarea>
                        <button type="submit" class="rounded-md bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            Submit review
                        </button>
                    </form>
                </div>
            @elseif ($userReview)
                <p class="mb-6 text-sm text-gray-500">You reviewed this book. Thanks!</p>
            @else
                <p class="mb-6 text-sm text-gray-500">Only verified purchasers can review this book.</p>
            @endif
        @else
            <p class="mb-6 text-sm text-gray-500">
                <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Log in</a> to review books you've purchased.
            </p>
        @endauth

        @forelse ($book->reviews as $review)
            <div class="border-t border-gray-200 py-4">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900">
                        {{ $review->user->name ?? 'Anonymous' }}
                        @if (auth()->id() === $review->user_id)
                            <span class="ml-1 text-xs text-gray-400">(you)</span>
                        @endif
                    </span>
                    <x-rating-stars :rating="$review->rating" />
                </div>
                @if ($review->comment)
                    <p class="mt-2 text-sm text-gray-600">{{ $review->comment }}</p>
                @endif
                @if (auth()->id() === $review->user_id)
                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-gray-400 hover:text-red-600">Delete</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">No reviews yet. Be the first to review this book.</p>
        @endforelse
    </section>

    {{-- ===================== Related ===================== --}}
    @if ($related->isNotEmpty())
        <section class="mt-14">
            <h2 class="text-xl font-bold text-gray-900 mb-4">More in {{ $book->category->name }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                @foreach ($related as $relatedBook)
                    <x-book-card :book="$relatedBook" />
                @endforeach
            </div>
        </section>
    @endif
</x-storefront-layout>
