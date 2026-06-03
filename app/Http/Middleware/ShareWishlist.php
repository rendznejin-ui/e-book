<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the authenticated user's wishlisted book ids with every view (so the
 * heart icon on cards/detail can render its state) using a single query per
 * request. Available inside Blade components via View::share.
 */
class ShareWishlist
{
    public function handle(Request $request, Closure $next): Response
    {
        $ids = $request->user()
            ? $request->user()->wishlistBooks()->pluck('books.id')->all()
            : [];

        View::share('wishlistedBookIds', $ids);

        return $next($request);
    }
}
