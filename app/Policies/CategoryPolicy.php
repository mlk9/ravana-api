<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        // همه می‌تونن ببینن
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return (
                $user->can('categories.edit') && $category->creator_uuid === $user->uuid
            ) || $user->can('categories.control-other');
    }

    public function delete(User $user, Category $category): bool
    {
        return (
                $user->can('categories.destroy') && $category->creator_uuid === $user->uuid
            ) || $user->can('categories.control-other');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can('categories.control-other');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->can('categories.control-other');
    }
}
