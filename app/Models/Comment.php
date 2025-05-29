<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasUuids, HasFactory;

    protected $primaryKey = 'uuid';
    protected $keyType = 'uuid';

    protected $fillable = [
        'text',
        'rate',
        'status',
        'commentable_type',
        'commentable_id',
        'user_uuid',
        'parent_uuid',
        'approved_at',
        'rejected_at',
    ];

    protected $hidden = [
        'user_uuid',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid');
    }

    public function parentComment() : BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_uuid');
    }

    public function child() : HasMany
    {
        return $this->hasMany(Comment::class, 'parent_uuid');
    }

    public function commentable() : MorphTo
    {
        return $this->morphTo();
    }
}
