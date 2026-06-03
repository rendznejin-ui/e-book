<x-storefront-layout>
    <x-slot name="title">{{ $activeCategory?->name ?? 'Browse Books' }}</x-slot>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- ===================== Sidebar filters ===================== --}}
        <aside class="lg:w-64 shrink-0 space-y-6">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Categories</h2>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('books.index', request()->except(['category', 'page'])) }}"
                           class="block rounded px-3 py-2 text-sm {{ ! $activeCategory ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                            All books
                        </a>
                    </li>
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('books.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                               class="block rounded px-3 py-2 text-sm {{ $activeCategory?->id === $category->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Mobile search (the nav search is hidden on small screens) --}}
            <form action="{{ route('books.index') }}" method="GET" class="md:hidden">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search…"
                       class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
            </form>
        </aside>

        {{-- ===================== Results ===================== --}}
        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
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
                            class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
