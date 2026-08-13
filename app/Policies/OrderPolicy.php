<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_manager']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial', 'courier'])
            || ($user->partner_id !== null && $user->hasAnyRole(['partner_admin', 'partner_operator']));
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasAnyRole(['partner_admin', 'partner_operator'])) {
            return $user->partner_id !== null && (int) $order->partner_id === (int) $user->partner_id;
        }

        if ($user->hasRole('courier')) {
            return $order->deliveries()->where('courier_id', $user->id)->exists();
        }

        if ($user->hasRole('admin_filial')) {
            return (int) $order->filial_id === (int) $user->filial_id;
        }

        if ($user->hasRole('employee')) {
            return (int) $order->filial_id === (int) $user->filial_id
                && ($order->created_by_id === $user->id
                    || $order->responsible_user_id === $user->id
                    || $order->documents()->where('user_id', $user->id)->exists());
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial'])
            || ($user->partner_id !== null && $user->hasAnyRole(['partner_admin', 'partner_operator']));
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('courier')) {
            return false;
        }

        return $this->view($user, $order);
    }
}
