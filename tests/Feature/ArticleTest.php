<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertJsonStringEqualsJsonString;

class ArticleTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_user_can_create_article(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'Article One',
            'slug' => 'article-one',
            'body' => 'test content',
            'tags' => 'tag1,tag2',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/articles', $data)
            ->assertStatus(201)
            ->assertJsonStructure(['article']);
    }

    public function test_user_cannot_create_article_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'Article One',
            'slug' => 'article-one',
            'body' => 'test content',
            'tags' => 'tag1,tag2',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/articles', $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['tags']]);
    }

    public function test_user_can_see_self_articles(): void
    {
        $users = User::factory(15)->create();
        Article::factory(15)->create();


        $user = User::factory()->create();
        Article::factory(15)->create([
            'author_uuid' => $user->uuid,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/articles')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data.data'); // بررسی اینکه فقط ۱۵ مقاله آمده
    }


}
