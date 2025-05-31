<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.index');
    }

    public function view(User $user, User $s_user): bool
    {
        return $user->hasPermissionTo('users.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $s_user): bool
    {
        return $user->hasPermissionTo('users.edit');
    }

    public function delete(User $user, User $s_user): bool
    {
        return $user->hasPermissionTo('users.destroy');
    }
}
