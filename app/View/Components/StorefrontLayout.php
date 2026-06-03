<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class StorefrontLayout extends Component
{
    /**
     * Public-facing storefront layout (nav + categories + footer).
     */
    public function render(): View
    {
        return view('layouts.storefront');
    }
}
