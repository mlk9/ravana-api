<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

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
            'thumbnail' => null,
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

    private function createColoredImage(): string
    {
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

        // تولید رنگ تصادفی
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);
        $hexColor = sprintf('#%02X%02X%02X', $r, $g, $b);

        // ساخت تصویر و پر کردن با رنگ
        $image = $manager->create(640, 480)->fill($hexColor);
        $path = storage_path('app/tmp/' . \Illuminate\Support\Str::random(10) . '.jpg');
        @mkdir(dirname($path), 0777, true);
        $image->toJpeg()->save($path);
        return $path;
    }

    public function withImage(): Factory
    {
        return $this->afterMaking(function (Article $article) {

            $path = $this->createColoredImage();

            if (!file_exists($path)) {
                throw new \Exception('Image not created: ' . $path);
            }

            $uploaded = new UploadedFile(
                $path,
                basename($path),
                'image/jpeg',
                null,
                true // important for testing mode
            );

            $image = json_encode(app(ImageService::class)->upload([$uploaded])[0]);

            $article->thumbnail = json_encode($image);

            unlink($path);
        });
    }
}
