<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name', 'BPU') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/bpu-mark.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
    <div class="min-h-screen flex">
        {{-- ===================== Sidebar ===================== --}}
        <aside class="hidden md:flex w-64 shrink-0 flex-col bg-brand-900 text-gray-300">
            <div class="h-16 flex items-center gap-2.5 px-5 border-b border-white/10">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm">
                    <img src="{{ asset('images/bpu-mark.svg') }}" alt="BPU" class="h-7 w-7">
                </span>
                <span class="flex flex-col leading-none">
                    <span class="font-serif text-lg font-semibold text-white">{{ config('app.name', 'BPU') }}</span>
                    <span class="text-[9px] font-medium uppercase tracking-[0.18em] text-gray-400">Admin Console</span>
                </span>
            </div>

            @php
                $nav = [
                    ['admin.dashboard', 'Dashboard', 'admin'],
                    ['admin.books.index', 'Books', 'admin/books*'],
                    ['admin.categories.index', 'Categories', 'admin/categories*'],
                    ['admin.orders.index', 'Orders', 'admin/orders*'],
                ];
            @endphp
            <nav class="flex-1 px-3 py-4 space-y-1">
                @foreach ($nav as [$route, $label, $pattern])
                    <a href="{{ route($route) }}"
                       class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->is($pattern) ? 'bg-brand-600 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-gray-800 p-3">
                <a href="{{ route('books.index') }}" class="block rounded-md px-3 py-2 text-sm hover:bg-gray-800 hover:text-white">← View store</a>
            </div>
        </aside>

        {{-- ===================== Main ===================== --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Admin' }}</h1>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-500">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-gray-500 hover:text-gray-900">Log out</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
