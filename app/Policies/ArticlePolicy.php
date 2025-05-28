<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user, $status): bool
    {
        if($user->hasPermissionTo('articles.index'))
        {
            return true;
        }else{
            return $status==='published';
        }
    }

    public function view(User $user, Article $article): bool
    {
        // همه می‌تونن ببینن
        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        return (
                $user->can('articles.edit') && $article->author_uuid === $user->uuid
            ) || $user->can('articles.control-other');
    }

    public function delete(User $user, Article $article): bool
    {
        return (
                $user->can('articles.destroy') && $article->author_uuid === $user->uuid
            ) || $user->can('articles.control-other');
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->can('articles.control-other');
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->can('articles.control-other');
    }
}
