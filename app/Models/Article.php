<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    use HasUuids, HasFactory;

    protected $primaryKey = 'uuid';
    protected $keyType = 'uuid';

    protected $fillable = [
        'title',
        'slug',
        'body',
        'tags',
        'status',
        'published_at',
        'author_uuid'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];

    protected $hidden = [
        'author_uuid',
    ];

    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class,'author_uuid');
    }

    public function categories() : BelongsToMany
    {
        return $this->belongsToMany(Category::class,'articles_categories', 'article_uuid');
    }

    public function comments() : MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bookmarks() : MorphMany
    {
        return $this->morphMany(Bookmark::class,'bookmark_able');
    }
}
