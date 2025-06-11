<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookmarkTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_user_can_bookmark_article(): void
    {
        $user = User::factory()->createOne();
        $article = Article::factory()->createOne();

        // بوکمارک کردن مقاله
        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.bookmarks.sync'), [
                'type' => 'article',
                'id' => $article->uuid
            ])
            ->assertStatus(201)
            ->assertJsonStructure(['data'])
            ->assertJson(['data' => true]);

        // بررسی وجود بوکمارک در دیتابیس
        $this->assertDatabaseHas('bookmarks', [
            'user_uuid' => $user->uuid,
            'bookmark_able_id' => $article->uuid,
            'bookmark_able_type' => \App\Models\Article::class,
        ]);

        // حذف بوکمارک
        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.bookmarks.sync'), [
                'type' => 'article',
                'id' => $article->uuid
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJson(['data' => false]);

        // بررسی حذف بوکمارک از دیتابیس
        $this->assertDatabaseMissing('bookmarks', [
            'user_uuid' => $user->uuid,
            'bookmark_able_id' => $article->uuid,
            'bookmark_able_type' => \App\Models\Article::class,
        ]);
    }


    public function test_user_can_see_bookmarks(): void
    {
        $user = User::factory()->createOne();
        $marks = Bookmark::factory(10)->forArticle()->create([
            'user_uuid' => $user->uuid
        ]);
        $this->actingAs($user,'sanctum')
            ->getJson(route('api.v1.bookmarks.index'))
            ->assertStatus(200)
            ->assertJsonCount(10, 'data.data');
    }

}
