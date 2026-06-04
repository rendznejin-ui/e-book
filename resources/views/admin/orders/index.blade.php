<x-admin-layout>
    <x-slot name="title">Orders</x-slot>

    @php
        $badge = [
            'paid' => 'bg-green-50 text-green-700',
            'pending' => 'bg-amber-50 text-amber-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'refunded' => 'bg-red-50 text-red-700',
        ];
    @endphp

    {{-- Status filter --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index') }}"
           class="rounded-full px-3 py-1 text-sm {{ ! $activeStatus ? 'bg-brand-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">All</a>
        @foreach ($statuses as $status)
            <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
               class="rounded-full px-3 py-1 text-sm capitalize {{ $activeStatus === $status ? 'bg-brand-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50' }}">{{ $status }}</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[$order->status] ?? '' }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-600 hover:text-brand-800 font-medium">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $orders->links() }}</div>
</x-admin-layout>
