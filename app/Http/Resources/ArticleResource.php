<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'tags' => $this->tags,
            'body' => $this->body,
            'published_at' => $this->published_at->toDateTimeString(),

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
