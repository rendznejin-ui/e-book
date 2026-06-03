@props([
    'rating' => 0,   // average rating, 0..5
    'count' => null, // optional number of reviews to show alongside
])

@php
    $full = (int) floor($rating);
    $hasHalf = ($rating - $full) >= 0.5;
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
    <div class="flex text-amber-400">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $full)
                {{-- full star --}}
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.34 4.12a1 1 0 00.95.69h4.33c.97 0 1.37 1.24.59 1.81l-3.5 2.54a1 1 0 00-.36 1.12l1.34 4.12c.3.92-.76 1.69-1.54 1.12l-3.5-2.54a1 1 0 00-1.18 0l-3.5 2.54c-.78.57-1.84-.2-1.54-1.12l1.34-4.12a1 1 0 00-.36-1.12L1.7 9.55c-.78-.57-.38-1.81.59-1.81h4.33a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @elseif ($i === $full + 1 && $hasHalf)
                {{-- half star --}}
                <svg class="h-4 w-4" viewBox="0 0 20 20"><defs><linearGradient id="half-{{ $i }}"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="#e5e7eb"/></linearGradient></defs><path fill="url(#half-{{ $i }})" d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.34 4.12a1 1 0 00.95.69h4.33c.97 0 1.37 1.24.59 1.81l-3.5 2.54a1 1 0 00-.36 1.12l1.34 4.12c.3.92-.76 1.69-1.54 1.12l-3.5-2.54a1 1 0 00-1.18 0l-3.5 2.54c-.78.57-1.84-.2-1.54-1.12l1.34-4.12a1 1 0 00-.36-1.12L1.7 9.55c-.78-.57-.38-1.81.59-1.81h4.33a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @else
                {{-- empty star --}}
                <svg class="h-4 w-4 text-gray-300" viewBox="0 0 20 20" fill="currentColor"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.34 4.12a1 1 0 00.95.69h4.33c.97 0 1.37 1.24.59 1.81l-3.5 2.54a1 1 0 00-.36 1.12l1.34 4.12c.3.92-.76 1.69-1.54 1.12l-3.5-2.54a1 1 0 00-1.18 0l-3.5 2.54c-.78.57-1.84-.2-1.54-1.12l1.34-4.12a1 1 0 00-.36-1.12L1.7 9.55c-.78-.57-.38-1.81.59-1.81h4.33a1 1 0 00.95-.69L9.05 2.93z"/></svg>
            @endif
        @endfor
    </div>
    @if (! is_null($count))
        <span class="text-xs text-gray-500">({{ $count }})</span>
    @endif
</div>
