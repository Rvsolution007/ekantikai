<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Client;

class ClientPolicy
{
    public function view(Admin $admin, Client $client): bool
    {
        return $admin->id === $client->admin_id;
    }

    public function update(Admin $admin, Client $client): bool
    {
        return $admin->id === $client->admin_id;
    }

    public function delete(Admin $admin, Client $client): bool
    {
        return $admin->id === $client->admin_id;
    }
}
