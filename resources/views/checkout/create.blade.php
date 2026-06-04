<x-storefront-layout>
    <x-slot name="title">Checkout</x-slot>

    <h1 class="font-serif text-3xl font-semibold text-gray-900 mb-6">Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-3 gap-8">
        @csrf

        {{-- Shipping details --}}
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">Shipping details</h2>

                @if ($errors->any())
                    <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="shipping_name" class="block text-sm font-medium text-gray-700">Full name</label>
                    <input id="shipping_name" name="shipping_name" type="text" required
                           value="{{ old('shipping_name', $user->name) }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="shipping_address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3" required
                              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('shipping_address') }}</textarea>
                </div>

                <div>
                    <label for="shipping_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input id="shipping_phone" name="shipping_phone" type="text" required
                           value="{{ old('shipping_phone') }}"
                           class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
        </div>

        {{-- Order summary --}}
        <div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sticky top-20">
                <h2 class="text-lg font-semibold text-gray-900">Order summary</h2>

                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <li class="flex justify-between gap-3 py-2 text-sm">
                            <span class="text-gray-600 line-clamp-1">{{ $item->book->title }} × {{ $item->quantity }}</span>
                            <span class="text-gray-900 whitespace-nowrap">${{ number_format($item->lineTotalCents() / 100, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 border-t border-gray-200 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Subtotal</dt>
                        <dd class="text-gray-900">${{ number_format($subtotalCents / 100, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Tax</dt>
                        <dd class="text-gray-900">${{ number_format($taxCents / 100, 2) }}</dd>
                    </div>
                    <div class="flex justify-between text-base font-semibold border-t border-gray-200 pt-2">
                        <dt class="text-gray-900">Total</dt>
                        <dd class="text-gray-900">${{ number_format($totalCents / 100, 2) }}</dd>
                    </div>
                </dl>

                <button type="submit"
                        class="mt-6 w-full rounded-md bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700">
                    Continue to payment
                </button>
                <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-700">
                    Back to cart
                </a>
            </div>
        </div>
    </form>
</x-storefront-layout>
