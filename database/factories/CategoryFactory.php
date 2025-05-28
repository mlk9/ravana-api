<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = join(' ', $this->faker->words(5));
        $slug = str($name)->replace(' ', '-') . now()->format('ymdhis');
        $user = User::query()->inRandomOrder()->first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $creator_uuid = $user->uuid;
        return [
            'name' => $name,
            'slug' => $slug,
            'descriptions' => $this->faker->paragraph(6),
            'parent_uuid' => null,
            'creator_uuid' => $creator_uuid
        ];
    }
}
