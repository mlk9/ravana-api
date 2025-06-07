<?php

namespace App\Http\Resources;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'descriptions' => $this->descriptions,
            'creator' => [
               'uuid' => $this->user?->uuid,
               'first_name' => $this->user?->first_name,
               'last_name' => $this->user?->last_name,
               'email' => $this->user?->email,
            ],
            'child' => CategoryResource::collection($this->whenLoaded('child')),
            'articles_count' => Article::query()->whereHas('categories', function ($query) {
                return $query->where('uuid', $this->uuid);
            })->where('status','published')->count()
        ];
    }
}
