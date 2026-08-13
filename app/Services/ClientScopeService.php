<?php

namespace App\Services;

use App\Models\ClientsModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ClientScopeService
{
    public function query(?User $user): Builder
    {
        return ClientsModel::query()->visibleTo($user);
    }

    public function findOrFail(int $id, ?User $user): ClientsModel
    {
        return $this->query($user)->findOrFail($id);
    }

    public function assertVisible(ClientsModel $client, ?User $user): ClientsModel
    {
        abort_unless($client->isVisibleTo($user), 403);

        return $client;
    }
}
