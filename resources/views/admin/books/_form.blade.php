{{-- Shared create/edit form. Expects $book, $categories, $action, $method. --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="max-w-3xl">
    @csrf
    @if (($method ?? 'POST') === 'PUT')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input name="title" value="{{ old('title', $book->title) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Author</label>
                <input name="author" value="{{ old('author', $book->author) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" required class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Price (USD)</label>
                <input name="price" type="number" step="0.01" min="0"
                       value="{{ old('price', $book->price_cents !== null ? number_format($book->price_cents / 100, 2, '.', '') : '') }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Stock</label>
                <input name="stock_qty" type="number" min="0" value="{{ old('stock_qty', $book->stock_qty ?? 0) }}" required
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">ISBN <span class="text-gray-400">(optional)</span></label>
                <input name="isbn" value="{{ old('isbn', $book->isbn) }}"
                       class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500">{{ old('description', $book->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Cover image</label>
                @if ($book->cover_image)
                    <img src="{{ asset('storage/'.$book->cover_image) }}" alt="" class="mt-1 mb-2 h-20 rounded border">
                @endif
                <input name="cover_image" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                <p class="text-xs text-gray-400 mt-1">JPG/PNG, max 2 MB. Leave empty to keep current.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Preview PDF</label>
                @if ($book->preview_pdf)
                    <p class="mt-1 mb-2 text-xs text-green-600">✓ Preview attached</p>
                @endif
                <input name="preview_pdf" type="file" accept="application/pdf" class="mt-1 block w-full text-sm text-gray-600">
                <p class="text-xs text-gray-400 mt-1">PDF, max 10 MB. Leave empty to keep current.</p>
            </div>
        </div>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $book->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            <span class="text-sm text-gray-700">Active (visible in store)</span>
        </label>
    </div>

    <div class="mt-5 flex gap-3">
        <button class="rounded-md bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Save book</button>
        <a href="{{ route('admin.books.index') }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
