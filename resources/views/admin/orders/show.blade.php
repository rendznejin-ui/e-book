<x-admin-layout>
    <x-slot name="title">Order {{ $order->order_number }}</x-slot>

    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; All orders</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        {{-- Items + totals --}}
        <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white p-6">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="py-2">Item</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Unit</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="py-2 text-gray-900">{{ $item->title }}</td>
                            <td class="py-2 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-2 text-right text-gray-600">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                            <td class="py-2 text-right text-gray-900">${{ number_format($item->lineTotalCents() / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 ml-auto w-56 text-sm">
                <div class="flex justify-between py-1"><span class="text-gray-500">Subtotal</span><span>${{ number_format($order->subtotal_cents / 100, 2) }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-500">Tax</span><span>${{ number_format($order->tax_cents / 100, 2) }}</span></div>
                <div class="flex justify-between border-t border-gray-200 py-2 font-bold"><span>Total</span><span>${{ number_format($order->total_cents / 100, 2) }}</span></div>
            </div>
        </div>

        {{-- Customer + status management --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-sm">
                <h2 class="font-semibold text-gray-900 mb-2">Customer</h2>
                <p class="text-gray-700">{{ $order->user->name ?? '—' }}</p>
                <p class="text-gray-500">{{ $order->user->email ?? '' }}</p>
                <h2 class="font-semibold text-gray-900 mt-4 mb-2">Ship to</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $order->shipping_name }}
{{ $order->shipping_address }}</p>
                <p class="text-gray-500">{{ $order->shipping_phone }}</p>
                @if ($order->payment)
                    <h2 class="font-semibold text-gray-900 mt-4 mb-2">Payment</h2>
                    <p class="text-gray-700">{{ ucfirst($order->payment->status) }} · {{ $order->payment->transaction_ref }}</p>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="font-semibold text-gray-900 mb-3">Update status</h2>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="flex-1 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['pending', 'paid', 'cancelled', 'refunded'] as $s)
                            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
