<x-storefront-layout>
    <x-slot name="title">{{ $activeCategory?->name ?? 'Browse Books' }}</x-slot>

    {{-- Hero (landing only) --}}
    @if ($isLanding)
        <section class="relative mb-10 overflow-hidden rounded-2xl bg-ink text-white">
            <div class="absolute inset-0 opacity-90 bg-gradient-to-br from-brand-700 via-brand-800 to-ink"></div>
            <div class="relative px-8 py-14 sm:px-12 sm:py-20 max-w-2xl">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-accent-300 ring-1 ring-inset ring-white/20">
                    <span class="text-accent-400">✦</span> Curated reads, fair prices
                </span>
                <h1 class="mt-5 font-serif text-4xl sm:text-5xl font-semibold leading-tight">
                    Find your next<br>great read.
                </h1>
                <p class="mt-4 max-w-md text-stone-200">
                    Browse our catalogue, preview a sample before you buy, and check out in
                    seconds with a secure, paperless receipt.
                </p>
                <form action="{{ route('books.index') }}" method="GET" class="mt-7 flex max-w-md gap-2">
                    <input type="search" name="q" placeholder="Search for a title or author…"
                           class="w-full rounded-full border-0 px-5 py-3 text-sm text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-white" />
                    <button class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-brand-700 hover:bg-stone-100 transition">Search</button>
                </form>
            </div>
        </section>
    @endif

    {{-- Shop by Category + Today's Deals (landing only) --}}
    @if ($isLanding)
        <section class="mb-12">
            <h2 class="font-serif text-2xl font-semibold text-gray-900 mb-4">Shop by category</h2>
            @php
                $tileGradients = [
                    'from-brand-600 to-brand-800',
                    'from-accent-400 to-accent-600',
                    'from-brand-700 to-brand-900',
                    'from-accent-500 to-accent-700',
                    'from-brand-500 to-brand-700',
                ];
            @endphp
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($categories as $category)
                    @php $grad = $tileGradients[crc32($category->name) % count($tileGradients)]; @endphp
                    <a href="{{ route('books.index', ['category' => $category->slug]) }}"
                       class="group rounded-xl overflow-hidden border border-gray-200/80 bg-white shadow-card hover:shadow-card-hover hover:-translate-y-0.5 transition">
                        <div class="aspect-square flex items-center justify-center bg-gradient-to-br {{ $grad }}">
                            <span class="text-5xl drop-shadow-sm transition group-hover:scale-110">{{ $category->icon() }}</span>
                        </div>
                        <div class="p-2.5 text-center">
                            <span class="text-sm font-medium text-gray-800 group-hover:text-brand-700">{{ $category->name }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($deals->isNotEmpty())
            <section class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-serif text-2xl font-semibold text-gray-900">
                        🏷️ Today's deals
                    </h2>
                    <a href="{{ route('books.index', ['sale' => 1]) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">View all deals →</a>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-4 -mx-1 px-1 snap-x">
                    @foreach ($deals as $book)
                        <div class="w-44 sm:w-52 shrink-0 snap-start">
                            <x-book-card :book="$book" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <h2 class="font-serif text-2xl font-semibold text-gray-900 mb-4">All books</h2>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- ===================== Sidebar filters ===================== --}}
        <aside class="lg:w-64 shrink-0 space-y-6">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Categories</h2>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('books.index', request()->except(['category', 'page'])) }}"
                           class="block rounded px-3 py-2 text-sm {{ ! $activeCategory ? 'bg-brand-50 text-brand-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            All books
                        </a>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('books.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                               class="block rounded px-3 py-2 text-sm {{ $activeCategory?->id === $category->id ? 'bg-brand-50 text-brand-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Deals filter --}}
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Deals</h2>
                <a href="{{ route('books.index', request()->boolean('sale') ? request()->except(['sale', 'page']) : array_merge(request()->except('page'), ['sale' => 1])) }}"
                   class="flex items-center justify-between rounded px-3 py-2 text-sm {{ request()->boolean('sale') ? 'bg-accent-50 text-accent-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>🏷️ On sale</span>
                    @if (request()->boolean('sale'))<span>✓</span>@endif
                </a>
            </div>

            {{-- Mobile search (the nav search is hidden on small screens) --}}
            <form action="{{ route('books.index') }}" method="GET" class="md:hidden">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search…"
                       class="w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
            </form>
        </aside>

        {{-- ===================== Results ===================== --}}
        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="font-serif text-3xl font-semibold text-gray-900">
                        {{ $activeCategory?->name ?? 'All Books' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $books->total() }} {{ Str::plural('book', $books->total()) }}
                        @if (request('q')) matching “{{ request('q') }}” @endif
                    </p>
                </div>

                {{-- Sort --}}
                <form action="{{ route('books.index') }}" method="GET">
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <select name="sort" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Newest</option>
                        <option value="price_asc"  @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                        <option value="title"      @selected(request('sort') === 'title')>Title A–Z</option>
                    </select>
                </form>
            </div>

            @if ($books->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
                    <p class="text-4xl mb-2">🔍</p>
                    <p class="text-gray-600">No books found. Try a different search or category.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach ($books as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>
</x-storefront-layout>
