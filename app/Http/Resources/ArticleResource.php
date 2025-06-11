<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $is_bookmark = false;

        if ($user) {
            $is_bookmark = Bookmark::query()
                ->where('bookmark_able_id', $this->uuid)
                ->where('bookmark_able_type', Article::class)
                ->where('user_uuid', $user->uuid)
                ->exists();
        }
        $thumbnail = $this->thumbnail;
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'thumbnail' => is_null($thumbnail) ? [] : (is_string($thumbnail) ? json_decode($thumbnail) : (object)$thumbnail),
            'slug' => $this->slug,
            'tags' => $this->tags,
            'body' => $this->body,
            'published_at' => $this->published_at->toDateTimeString(),

            'is_bookmark' => $is_bookmark,

            // Category info
            'category' => [
                'uuid' => $this->category?->uuid,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],

            // Author info
            'author' => [
                'uuid' => $this->author?->uuid,
                'first_name' => $this->author?->first_name,
                'last_name' => $this->author?->last_name,
                'email' => $this->author?->email,
            ],

        ];
    }
}
