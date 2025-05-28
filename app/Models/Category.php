<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasUuids, HasFactory;

    protected $primaryKey = 'uuid';
    protected $keyType = 'uuid';

    protected $fillable = [
        'name',
        'slug',
        'descriptions',
        'parent_uuid',
        'creator_uuid',
    ];

    protected $hidden = [
        'creator_uuid',
    ];

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class,'creator_uuid');
    }

    public function parentCategory() : BelongsTo
    {
        return $this->belongsTo(Category::class,'parent_uuid')->withDefault();
    }

    public function childCategories() : HasMany
    {
        return $this->hasMany(Category::class,'parent_uuid');
    }

    public function articles() : BelongsToMany
    {
        return $this->belongsToMany(Article::class,'articles_categories', 'category_uuid');
    }
}
