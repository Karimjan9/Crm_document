<?php

namespace App\Support;

use App\Models\User;

class RoleHomeRoute
{
    public static function for(User $user): ?string
    {
        return match (true) {
            $user->hasRole('super_admin') => 'superadmin.index',
            $user->hasRole('admin_manager') => 'admin.index',
            $user->hasRole('admin_filial') => 'admin_filial.index',
            $user->hasRole('employee') => 'employee.document.index',
            $user->hasRole('courier') => 'courier.documents.index',
            $user->hasAnyRole(['partner_admin', 'partner_operator']) => 'partner.dashboard',
            $user->hasRole('user') => 'account.home',
            default => null,
        };
    }
}
