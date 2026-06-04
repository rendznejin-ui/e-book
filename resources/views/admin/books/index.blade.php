<x-admin-layout>
    <x-slot name="title">Books</x-slot>

    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title or author…"
                   class="rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
            <button class="rounded-md border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">Search</button>
        </form>
        <a href="{{ route('admin.books.create') }}" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">+ New book</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($books as $book)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-8 shrink-0 rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                                    @if ($book->cover_image)
                                        <img src="{{ asset('storage/'.$book->cover_image) }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-sm opacity-40">📘</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 line-clamp-1">{{ $book->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $book->author }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $book->category->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-900">${{ $book->price }}</td>
                        <td class="px-4 py-3 {{ $book->stock_qty == 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ $book->stock_qty }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $book->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $book->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.books.edit', $book) }}" class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" class="inline ml-3"
                                  onsubmit="return confirm('Delete “{{ $book->title }}”?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No books found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $books->links() }}</div>
</x-admin-layout>
