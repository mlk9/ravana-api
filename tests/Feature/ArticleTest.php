<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertJsonStringEqualsJsonString;

class ArticleTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_user_can_give_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $this->assertTrue($user->hasRole('writer'));
    }

    /**
     * A basic feature test example.
     */
    public function test_user_can_create_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $data = [
            'title' => 'Article One',
            'slug' => 'article-one',
            'body' => fake()->paragraph(5),
            'tags' => 'tag1,tag2',
            'status' => 'published',
            'published_at' => now()->timestamp
        ];

        $data = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.articles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

    }

    public function test_user_cannot_create_article_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $data = [
            'title' => 'Article One',
            'body' => 'test content',
            'tags' => 'tag1,tag2',
            'status' => 'published',
            'published_at' => now()->subDays(3)->timestamp
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.articles.index'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['body', 'slug']]);
    }

    public function test_normal_user_cannot_see_draft_articles(): void
    {
        $user = User::factory()->create();
        Article::factory(5)->create(['status' => 'draft']);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.articles.index', ['status' => 'draft']))
            ->assertStatus(403);
    }

    public function test_normal_user_can_see_published_articles(): void
    {
        $user = User::factory()->create();
        Article::factory(5)->create(['status' => 'published', 'published_at' => now()]);

        $res = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.articles.index'))
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.data'); // چون مقاله‌ای نداره، انتظار داریم خروجی صفر باشه
    }

    public function test_user_cannot_see_other_articles(): void
    {
        // ساخت کاربران و مقالات متعلق به بقیه کاربران
        $otherUsers = User::factory(5)->create();
        foreach ($otherUsers as $otherUser) {
            Article::factory(3)->create([
                'author_uuid' => $otherUser->uuid,
            ]);
        }

        // ساخت کاربر تست و بدون هیچ مقاله‌ای برای خودش
        $user = User::factory()->create();
        $user->assignRole('writer');
        // احراز هویت و ارسال درخواست
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.articles.index'))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data.data'); // چون مقاله‌ای نداره، انتظار داریم خروجی صفر باشه
    }

    public function test_user_can_see_self_articles(): void
    {
        // ساخت کاربر تست
        $user = User::factory()->create();
        $user->assignRole('writer');
        // ساخت مقالات برای کاربر تست
        Article::factory(5)->create([
            'author_uuid' => $user->uuid,
        ]);

        // ساخت چند مقاله برای کاربران دیگر
        $otherUsers = User::factory(3)->create();
        foreach ($otherUsers as $otherUser) {
            Article::factory(2)->create([
                'author_uuid' => $otherUser->uuid,
            ]);
        }

        // احراز هویت و ارسال درخواست
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.articles.index'))
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.data'); // فقط ۵ مقاله خودش باید نمایش داده شود
    }


    public function test_user_can_update_self_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $article = Article::factory(1)->createOne([
            'slug' => 'article-one',
        ]);


        $categories = Category::factory(3)->create();

        $data = [
            'title' => 'Article One 2',
            'slug' => 'article-one',
            'tags' => 'tag1,tg3',
            'status' => 'published',
            'published_at' => now()->timestamp
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.articles.update', $article), [...$data, 'categories' => $categories->pluck('uuid')->toArray()])
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseHas(Article::class, [
            'uuid' => $article->uuid,
            'title' => 'Article One 2',
            'slug' => 'article-one',
            'tags' => 'tag1,tg3',
            'status' => 'published',
            'published_at' => now()
        ]);

        foreach ($categories as $category) {
            $this->assertDatabaseHas('articles_categories', [
                'article_uuid' => Article::query()->where('slug', $data['slug'])->first()->uuid,
                'category_uuid' => $category->uuid,
            ]);
        }
    }

    public function test_user_cannot_update_other_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $article = Article::factory()->createOne();

        $userOther = User::factory()->create();

        $this->actingAs($userOther, 'sanctum')
            ->putJson(route('api.v1.panel.articles.update', $article), [])
            ->assertStatus(403);
    }

    public function test_user_can_see_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $article = Article::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.articles.show', $article))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_delete_self_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $article = Article::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.articles.destroy', $article))
            ->assertStatus(200);
        $this->assertDatabaseEmpty(Article::class);
    }

    public function test_user_cannot_delete_other_article(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');
        $article = Article::factory()->createOne();

        $userOther = User::factory()->create();

        $this->actingAs($userOther, 'sanctum')
            ->deleteJson(route('api.v1.panel.articles.destroy', $article))
            ->assertStatus(403);
        $this->assertDatabaseCount(Article::class, 1);
    }

    public function test_user_can_create_article_with_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $categories = Category::factory(3)->create();

        $data = [
            'title' => 'Article One',
            'slug' => 'article-one',
            'body' => fake()->paragraph(5),
            'tags' => 'tag1,tag2',
            'status' => 'published',
            'published_at' => now()->timestamp,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.articles.store'), [...$data, 'categories' => $categories->pluck('uuid')->toArray()])
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $data['published_at'] = now();

        $this->assertDatabaseHas(Article::class, $data);


        foreach ($categories as $category) {
            $this->assertDatabaseHas('articles_categories', [
                'article_uuid' => Article::query()->where('slug', $data['slug'])->first()->uuid,
                'category_uuid' => $category->uuid,
            ]);
        }

    }

    public function test_user_can_see_published_articles(): void
    {
        $user = User::factory()->create();
        $articles = Article::factory(5)->create([
            'published_at' => now()->subDays(1),
            'status' => 'published'
        ]);

        $this->getJson(route('api.v1.articles.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['data' => [ 0 => ['title'] ] ]]);

        $this->getJson(route('api.v1.articles.show', $articles->first()))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['title']]);
    }


}
