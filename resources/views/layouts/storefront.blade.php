<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Books' }} — {{ config('app.name', 'E-Book') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <div class="min-h-screen flex flex-col">
        {{-- ===================== Top navigation ===================== --}}
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    {{-- Brand --}}
                    <a href="{{ route('books.index') }}" class="flex items-center gap-2 shrink-0">
                        <span class="text-2xl">📚</span>
                        <span class="text-xl font-semibold text-gray-900">{{ config('app.name', 'E-Book') }}</span>
                    </a>

                    {{-- Search (GET to catalog) --}}
                    <form action="{{ route('books.index') }}" method="GET" class="hidden md:flex flex-1 max-w-md">
                        <input type="search" name="q" value="{{ request('q') }}"
                               placeholder="Search title or author…"
                               class="w-full rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        <button type="submit"
                                class="rounded-r-md bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-700">
                            Search
                        </button>
                    </form>

                    {{-- Right side --}}
                    <div class="flex items-center gap-3 shrink-0">
                        {{-- Categories dropdown --}}
                        <div class="hidden sm:block">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                                        Categories
                                        <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('books.index')">All books</x-dropdown-link>
                                    @foreach ($navCategories as $navCategory)
                                        <x-dropdown-link :href="route('books.index', ['category' => $navCategory->slug])">
                                            {{ $navCategory->name }}
                                        </x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                        </div>

                        {{-- Cart --}}
                        <a href="{{ route('cart.index') }}" class="relative inline-flex items-center rounded-md p-2 text-gray-600 hover:text-gray-900" title="Cart">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-.534 1.872-1.5 2.182-3l.318-2.5H6.106M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                            <span id="cart-count"
                                  class="absolute -top-0.5 -right-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-indigo-600 px-1 text-xs font-semibold text-white {{ ($cartCount ?? 0) < 1 ? 'hidden' : '' }}">
                                {{ $cartCount ?? 0 }}
                            </span>
                        </a>

                        {{-- Account --}}
                        @auth
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                                        {{ Str::limit(Auth::user()->name, 14) }}
                                        <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @if (Auth::user()->isAdmin())
                                        <x-dropdown-link :href="url('/admin')">Admin dashboard</x-dropdown-link>
                                    @endif
                                    <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                                    <x-dropdown-link :href="route('orders.index')">My orders</x-dropdown-link>
                                    <x-dropdown-link :href="route('wishlist.index')">My wishlist</x-dropdown-link>
                                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            Log out
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Log in</a>
                            <a href="{{ route('register') }}"
                               class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- ===================== Flash messages ===================== --}}
        @if (session('success'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- ===================== Page content ===================== --}}
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        {{-- ===================== Footer ===================== --}}
        <footer class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-500 flex justify-between">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'E-Book') }}</span>
                <span>Online Book Store</span>
            </div>
        </footer>
    </div>
    {{-- Toast container --}}
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden rounded-md bg-gray-900 px-4 py-3 text-sm text-white shadow-lg"></div>

    {{-- ===================== Cart interactions (vanilla JS + fetch) ===================== --}}
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        let toastTimer;

        function toast(message, isError) {
            const el = document.getElementById('toast');
            el.textContent = message;
            el.classList.toggle('bg-red-600', !!isError);
            el.classList.toggle('bg-gray-900', !isError);
            el.classList.remove('hidden');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => el.classList.add('hidden'), 2500);
        }

        function setCount(count) {
            const badge = document.getElementById('cart-count');
            if (!badge) return;
            badge.textContent = count;
            badge.classList.toggle('hidden', count < 1);
        }

        async function send(url, method, body) {
            const res = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: body ? JSON.stringify(body) : null,
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Something went wrong.');
            }
            return res.json();
        }

        function setWishState(btn, on) {
            const off = (btn.dataset.off || 'text-gray-400').split(' ');
            btn.classList.toggle('text-red-500', on);
            off.forEach(c => on ? btn.classList.remove(c) : btn.classList.add(c));
            const svg = btn.querySelector('svg');
            if (svg) svg.setAttribute('fill', on ? 'currentColor' : 'none');
            const label = btn.querySelector('.wishlist-label');
            if (label) label.textContent = on ? 'Wishlisted' : 'Wishlist';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        }

        // --- Add to cart (catalog cards + book detail) ---
        document.addEventListener('click', async (e) => {
            // --- Wishlist toggle ---
            const wishBtn = e.target.closest('[data-wishlist-toggle]');
            if (wishBtn) {
                e.preventDefault();
                try {
                    const res = await fetch(wishBtn.dataset.url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    });
                    if (res.status === 401 || res.status === 419) { window.location = @json(route('login')); return; }
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Could not update wishlist.');
                    setWishState(wishBtn, data.wishlisted);
                    toast(data.message);
                } catch (err) { toast(err.message, true); }
                return;
            }

            const addBtn = e.target.closest('[data-add-to-cart]');
            if (addBtn) {
                e.preventDefault();
                addBtn.disabled = true;
                try {
                    const data = await send(@json(route('cart.add')), 'POST', {
                        book_id: Number(addBtn.dataset.bookId),
                        quantity: Number(addBtn.dataset.quantity || 1),
                    });
                    setCount(data.count);
                    toast(data.message);
                } catch (err) {
                    toast(err.message, true);
                } finally {
                    addBtn.disabled = false;
                }
                return;
            }

            // --- Quantity steppers (cart page) ---
            const step = e.target.closest('[data-qty-step]');
            if (step) {
                const id = step.dataset.itemId;
                const input = document.querySelector(`[data-qty-input][data-item-id="${id}"]`);
                const max = Number(input.dataset.max || 99);
                let next = Number(input.value) + Number(step.dataset.dir);
                next = Math.max(0, Math.min(next, max));
                await changeQty(id, next, input);
                return;
            }

            // --- Remove line (cart page) ---
            const removeBtn = e.target.closest('[data-remove-item]');
            if (removeBtn) {
                const id = removeBtn.dataset.itemId;
                try {
                    const data = await send(`/cart/items/${id}`, 'DELETE');
                    dropRow(id, data);
                    toast(data.message);
                } catch (err) {
                    toast(err.message, true);
                }
            }
        });

        async function changeQty(id, qty, input) {
            try {
                const data = await send(`/cart/items/${id}`, 'PATCH', { quantity: qty });
                setCount(data.count);
                document.getElementById('cart-subtotal').textContent = '$' + data.subtotal;
                if (data.removed) {
                    dropRow(id, data);
                } else {
                    input.value = qty;
                    const lt = document.querySelector(`[data-line-total][data-item-id="${id}"]`);
                    if (lt && data.line_total) lt.textContent = '$' + data.line_total;
                }
            } catch (err) {
                toast(err.message, true);
            }
        }

        function dropRow(id, data) {
            const row = document.querySelector(`[data-cart-row][data-item-id="${id}"]`);
            if (row) row.remove();
            setCount(data.count);
            const sub = document.getElementById('cart-subtotal');
            if (sub) sub.textContent = '$' + data.subtotal;
            if (data.count < 1) {
                ['cart-items', 'cart-summary'].forEach(i => document.getElementById(i)?.classList.add('hidden'));
                document.getElementById('cart-empty')?.classList.remove('hidden');
            }
        }
    })();
    </script>

    @stack('scripts')
</body>
</html>
