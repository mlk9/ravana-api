<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
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
            'text' => $this->faker->paragraph(rand(1,6)),
            'rate' => rand(0,5),
            'commentable_id' => null,
            'commentable_type' => null,
            'user_uuid' => $user->uuid,
            'parent_uuid' => null,
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
                'commentable_id' => $article->uuid,
                'commentable_type' => Article::class,
            ];
        });
    }
}
