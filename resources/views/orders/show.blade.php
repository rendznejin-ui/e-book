<x-storefront-layout>
    <x-slot name="title">Order {{ $order->order_number }}</x-slot>

    @php
        $badge = [
            'paid' => 'bg-green-50 text-green-700',
            'pending' => 'bg-amber-50 text-amber-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'refunded' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; All orders</a>
            <a href="{{ route('orders.receipt', $order) }}"
               class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Download PDF
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-8">
            {{-- Header --}}
            <div class="flex items-start justify-between border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ config('store.name') }}</h1>
                    <p class="text-sm text-gray-500">Receipt</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                    <p class="text-sm text-gray-500">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                    <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[$order->status] ?? '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>

            {{-- Ship to --}}
            <div class="grid sm:grid-cols-2 gap-6 py-6 text-sm">
                <div>
                    <h2 class="font-semibold text-gray-700 mb-1">Billed to</h2>
                    <p class="text-gray-600">{{ $order->shipping_name }}</p>
                    <p class="text-gray-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
                    <p class="text-gray-600">{{ $order->shipping_phone }}</p>
                </div>
                @if ($order->payment)
                    <div class="sm:text-right">
                        <h2 class="font-semibold text-gray-700 mb-1">Payment</h2>
                        <p class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $order->payment->gateway)) }}</p>
                        <p class="text-gray-600">{{ $order->payment->transaction_ref }}</p>
                        <p class="text-gray-600">{{ ucfirst($order->payment->status) }}@if($order->payment->paid_at) · {{ $order->payment->paid_at->format('M j, Y g:i A') }}@endif</p>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <table class="min-w-full text-sm border-t border-gray-200">
                <thead class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-3">Item</th>
                        <th class="py-3 text-center">Qty</th>
                        <th class="py-3 text-right">Unit</th>
                        <th class="py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="py-3 text-gray-900">{{ $item->title }}</td>
                            <td class="py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-3 text-right text-gray-600">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                            <td class="py-3 text-right text-gray-900">${{ number_format($item->lineTotalCents() / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div class="mt-4 ml-auto w-full sm:w-64 text-sm">
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900">${{ number_format($order->subtotal_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-gray-500">Tax</span>
                    <span class="text-gray-900">${{ number_format($order->tax_cents / 100, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 py-2 text-base font-bold">
                    <span class="text-gray-900">Total</span>
                    <span class="text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-storefront-layout>
