<x-storefront-layout>
    <x-slot name="title">Your Cart</x-slot>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Your Cart</h1>

    <div id="cart-wrapper" class="{{ $items->isEmpty() ? '' : 'grid lg:grid-cols-3 gap-8' }}">
        {{-- Empty state --}}
        <div id="cart-empty" class="{{ $items->isEmpty() ? '' : 'hidden' }} lg:col-span-3 rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
            <p class="text-4xl mb-2">🛒</p>
            <p class="text-gray-600">Your cart is empty.</p>
            <a href="{{ route('books.index') }}" class="mt-4 inline-block rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                Browse books
            </a>
        </div>

        {{-- Line items --}}
        <div class="lg:col-span-2 {{ $items->isEmpty() ? 'hidden' : '' }}" id="cart-items">
            <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white">
                @foreach ($items as $item)
                    <div class="flex items-center gap-4 p-4" data-cart-row data-item-id="{{ $item->id }}">
                        <div class="h-20 w-16 shrink-0 rounded bg-gray-100 flex items-center justify-center overflow-hidden">
                            @if ($item->book->cover_image)
                                <img src="{{ asset('storage/'.$item->book->cover_image) }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl opacity-40">📘</span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <a href="{{ route('books.show', $item->book) }}" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1">
                                {{ $item->book->title }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $item->book->author }}</p>
                            <p class="text-sm text-gray-500 mt-1">${{ $item->book->price }} each</p>
                        </div>

                        {{-- Quantity stepper --}}
                        <div class="flex items-center rounded-md border border-gray-300">
                            <button type="button" class="px-3 py-1.5 text-gray-600 hover:bg-gray-50" data-qty-step data-dir="-1" data-item-id="{{ $item->id }}">−</button>
                            <input type="text" inputmode="numeric" readonly value="{{ $item->quantity }}"
                                   class="w-12 border-0 text-center text-sm focus:ring-0" data-qty-input data-item-id="{{ $item->id }}" data-max="{{ $item->book->stock_qty }}">
                            <button type="button" class="px-3 py-1.5 text-gray-600 hover:bg-gray-50" data-qty-step data-dir="1" data-item-id="{{ $item->id }}">+</button>
                        </div>

                        <div class="w-20 text-right font-semibold text-gray-900" data-line-total data-item-id="{{ $item->id }}">
                            ${{ number_format($item->lineTotalCents() / 100, 2) }}
                        </div>

                        <button type="button" class="text-gray-400 hover:text-red-600" title="Remove" data-remove-item data-item-id="{{ $item->id }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Summary --}}
        <div class="{{ $items->isEmpty() ? 'hidden' : '' }}" id="cart-summary">
            <div class="rounded-lg border border-gray-200 bg-white p-6 sticky top-20">
                <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                <div class="mt-4 flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900" id="cart-subtotal">${{ number_format($subtotalCents / 100, 2) }}</span>
                </div>
                <p class="mt-1 text-xs text-gray-400">Taxes and totals are calculated at checkout.</p>

                <a href="{{ route('checkout.create') }}"
                   class="mt-6 block w-full rounded-md bg-indigo-600 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-indigo-700">
                    Proceed to checkout
                </a>
                <a href="{{ route('books.index') }}" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-700">
                    Continue shopping
                </a>
            </div>
        </div>
    </div>
</x-storefront-layout>
