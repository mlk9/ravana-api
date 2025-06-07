<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = join(' ', $this->faker->words(5));
        $slug = str($title)->replace(' ', '-') . now()->format('ymdhis');
        $user = User::query()->inRandomOrder()->first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $author_uuid = $user->uuid;
        return [
            'title' => $title,
            'slug' => $slug,
            'body' => $this->faker->paragraph(6),
            'tags' => $this->faker->words(6, true),
            'status' => $this->faker->randomElement(['draft', 'archived', 'published']),
            'published_at' => $this->faker->randomElement([now()->addDays(rand(1, 30)), now()->subDays(rand(1, 30))]),
            'author_uuid' => $author_uuid
        ];
    }

    public function configure() : Factory
    {
        return $this->afterCreating(function (Article $article) {
            if(Category::query()->count() === 0)
            {
                Category::factory(10)->create();
            }
            $categoryUuids = Category::query()->inRandomOrder()->take(rand(1, 3))->pluck('uuid');
            $article->categories()->sync($categoryUuids);
        });
    }

    public function published() : Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => now(),
            ];
        });
    }
}
