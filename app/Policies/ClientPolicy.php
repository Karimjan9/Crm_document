<?php

namespace App\Policies;

use App\Models\ClientsModel;
use App\Models\User;

class ClientPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_manager']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial']);
    }

    public function view(User $user, ClientsModel $client): bool
    {
        return $client->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['employee', 'admin_filial']);
    }

    public function update(User $user, ClientsModel $client): bool
    {
        return $client->isVisibleTo($user);
    }

    public function delete(User $user, ClientsModel $client): bool
    {
        return $client->isVisibleTo($user);
    }
}
