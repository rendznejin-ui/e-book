<x-admin-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $cards = [
                ['Revenue', '$'.number_format($stats['revenue_cents'] / 100, 2), 'text-green-600'],
                ['Paid orders', $stats['paid_orders'], 'text-gray-900'],
                ['Pending', $stats['pending_orders'], 'text-amber-600'],
                ['Customers', $stats['customers'], 'text-gray-900'],
                ['Books', $stats['books'], 'text-gray-900'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $color])
            <div class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid lg:grid-cols-3 gap-6">
        {{-- Recent orders --}}
        <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Recent orders</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-600 hover:text-brand-800">View all</a>
            </div>
            <table class="min-w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-gray-900 hover:text-brand-600">{{ $order->order_number }}</a>
                                <div class="text-xs text-gray-500">{{ $order->user->name ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $order->created_at->format('M j') }}</td>
                            <td class="px-5 py-3 text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</td>
                            <td class="px-5 py-3"><span class="text-xs text-gray-500">{{ ucfirst($order->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-center text-gray-500">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-6">
            {{-- Low stock --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="px-5 py-4 border-b border-gray-200"><h2 class="font-semibold text-gray-900">Low stock</h2></div>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse ($lowStock as $book)
                        <li class="px-5 py-3 flex justify-between">
                            <a href="{{ route('admin.books.edit', $book) }}" class="text-gray-700 hover:text-brand-600 line-clamp-1">{{ $book->title }}</a>
                            <span class="font-semibold {{ $book->stock_qty == 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $book->stock_qty }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500">All books well stocked.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Top sellers --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="px-5 py-4 border-b border-gray-200"><h2 class="font-semibold text-gray-900">Best sellers</h2></div>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse ($topBooks as $row)
                        <li class="px-5 py-3 flex justify-between">
                            <span class="text-gray-700 line-clamp-1">{{ $row->title }}</span>
                            <span class="text-gray-500">{{ $row->sold }} sold</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500">No sales yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
