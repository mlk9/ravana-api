<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('comments.index');
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->can('comments.show') && ($comment->commentable->author_uuid == $user->uuid || $user->can('comments.control-other'));
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->can('comments.edit') &&  ($comment->commentable->author_uuid == $user->uuid || $user->can('comments.control-other'));
    }

    public function answer(User $user, Comment $comment): bool
    {
        return $user->can('comments.edit') &&  ($comment->commentable->author_uuid == $user->uuid || $user->can('comments.control-other'));
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->can('comments.destroy') &&  ($comment->commentable->author_uuid == $user->uuid || $user->can('comments.control-other'));
    }
}
