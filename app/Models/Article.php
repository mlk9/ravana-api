<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'author_uuid'
    ];

    public function author() : BelongsTo
    {
        return $this->belongsTo(User::class,'author_uuid');
    }
}
