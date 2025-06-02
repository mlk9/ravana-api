<?php

namespace App\Policies;

use App\Models\User;

class ImagePolicy
{
    public function upload(User $user): bool
    {
        return $user->hasPermissionTo('images.upload');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo('images.delete');
    }
}
