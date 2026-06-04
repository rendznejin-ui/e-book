<x-storefront-layout>
    <x-slot name="title">Payment successful</x-slot>

    <div class="max-w-lg mx-auto text-center">
        <div class="rounded-lg border border-gray-200 bg-white p-8">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <svg class="h-9 w-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            </div>

            <h1 class="mt-5 font-serif text-3xl font-semibold text-gray-900">Payment successful</h1>
            <p class="mt-2 text-gray-600">
                Thank you! Your order <span class="font-semibold">{{ $order->order_number }}</span> is confirmed.
            </p>

            <div class="mt-6 rounded-md bg-gray-50 p-4 text-left text-sm">
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Total paid</span>
                    <span class="font-semibold text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Transaction</span>
                    <span class="text-gray-900">{{ $order->payment->transaction_ref }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Paid at</span>
                    <span class="text-gray-900">{{ $order->payment->paid_at?->format('M j, Y g:i A') }}</span>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('orders.show', $order) }}"
                   class="rounded-md bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    View receipt
                </a>
                <a href="{{ route('orders.receipt', $order) }}"
                   class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Download PDF
                </a>
                <a href="{{ route('books.index') }}"
                   class="rounded-md px-5 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Continue shopping
                </a>
            </div>
        </div>
    </div>
</x-storefront-layout>
