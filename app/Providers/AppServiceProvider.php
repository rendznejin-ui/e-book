<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\CartService;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\SandboxGateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Active payment gateway. Swap this single binding to go live.
        $this->app->bind(PaymentGateway::class, SandboxGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Supply the storefront nav with the category list and live cart count
        // (keeps DB calls out of the Blade templates).
        View::composer('layouts.storefront', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->get());
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
