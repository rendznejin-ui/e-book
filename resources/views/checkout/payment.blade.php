<x-storefront-layout>
    <x-slot name="title">Payment</x-slot>

    <div class="max-w-md mx-auto">
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
            <h1 class="text-xl font-bold text-gray-900">Scan to pay</h1>
            <p class="mt-1 text-sm text-gray-500">Order {{ $order->order_number }}</p>

            {{-- Mock QR (sandbox) --}}
            <div class="mt-6 flex justify-center">
                <div class="rounded-lg border border-gray-200 p-3">
                    {!! $qrSvg !!}
                </div>
            </div>

            <p class="mt-6 text-3xl font-bold text-gray-900">${{ number_format($order->total_cents / 100, 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">Ref: {{ $order->payment->transaction_ref }}</p>

            <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span id="pay-status">Awaiting payment…</span>
            </div>

            {{-- Sandbox controls --}}
            <div class="mt-8 space-y-3">
                <button id="simulate-pay" type="button"
                        data-signature="{{ $signature }}"
                        data-confirm-url="{{ route('checkout.confirm', $order) }}"
                        class="w-full rounded-md bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-60">
                    ✅ Simulate payment approval
                </button>

                <form method="POST" action="{{ route('checkout.cancel', $order) }}">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-md border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Cancel order
                    </button>
                </form>
            </div>

            <p class="mt-6 text-xs text-gray-400">
                This is a sandbox gateway — no real payment is processed. The QR encodes an
                HMAC-signed payload; approval verifies that signature server-side.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const btn = document.getElementById('simulate-pay');
        const statusEl = document.getElementById('pay-status');
        const statusUrl = @json(route('checkout.status', $order));

        async function approve() {
            btn.disabled = true;
            statusEl.textContent = 'Processing…';
            try {
                const res = await fetch(btn.dataset.confirmUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ signature: btn.dataset.signature }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Payment failed.');
                statusEl.textContent = 'Approved! Redirecting…';
                window.location = data.redirect;
            } catch (err) {
                statusEl.textContent = err.message;
                btn.disabled = false;
            }
        }
        btn.addEventListener('click', approve);

        // Poll in case payment is approved elsewhere (e.g. a real scan).
        const poll = setInterval(async () => {
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.status === 'paid') {
                    clearInterval(poll);
                    window.location = @json(route('checkout.success', $order));
                }
            } catch (e) { /* keep polling */ }
        }, 3000);
    })();
    </script>
    @endpush
</x-storefront-layout>
