<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * A user may view (and act on) only their own orders. Admins may view any.
     * Prevents order-id enumeration like /orders/5 or /checkout/5/payment.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->isAdmin();
    }

    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }
}
