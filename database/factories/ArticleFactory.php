<?php

namespace Database\Factories;

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
        $slug = str($title)->replace(' ','-').now()->format('ymdhis');
        $user = User::query()->inRandomOrder()->first();

        if (! $user) {
            $user = User::factory()->create(); // یا firstOrCreate(['email' => 'x@test.com'], [...])
        }

        $author_uuid = $user->uuid;
        return [
            'title' => $title,
            'slug' => $slug,
            'body' => $this->faker->paragraph(6),
            'tags' => $this->faker->words(6,true),
            'author_uuid' => $author_uuid
        ];
    }
}
