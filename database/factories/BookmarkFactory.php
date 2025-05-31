<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::query()->inRandomOrder()->first();
        if (!$user) {
            $user = User::factory()->create();
        }
        return [
            'bookmark_able_id' => null,
            'bookmark_able_type' => null,
            'user_uuid' => $user->uuid,
        ];
    }

    public function forArticle() : Factory
    {
        return $this->state(function (array $attributes) {
            $article = Article::query()->inRandomOrder()->first();
            if (!$article) {
                $article = Article::factory()->create();
            }
            return [
                'bookmark_able_id' => $article->uuid,
                'bookmark_able_type' => Article::class,
            ];
        });
    }
}
