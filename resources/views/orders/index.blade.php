<x-storefront-layout>
    <x-slot name="title">My Orders</x-slot>

    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h1>

    @php
        $badge = [
            'paid' => 'bg-green-50 text-green-700',
            'pending' => 'bg-amber-50 text-amber-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'refunded' => 'bg-red-50 text-red-700',
        ];
    @endphp

    @if ($orders->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white py-16 text-center">
            <p class="text-4xl mb-2">🧾</p>
            <p class="text-gray-600">You have no orders yet.</p>
            <a href="{{ route('books.index') }}" class="mt-4 inline-block rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Start shopping</a>
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->items_count }}</td>
                            <td class="px-4 py-3 text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-storefront-layout>
