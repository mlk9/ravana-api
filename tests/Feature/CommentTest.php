<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_user_can_create_comment(): void
    {
        $user = User::factory()->createOne();
        $article = Article::factory()->create();

        $data = [
            'text' => fake()->paragraph(3),
            'rate' => 5,
            'type' => 'article',
            'id' => $article->uuid,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.comments.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_create_comment_for_comment(): void
    {
        $user = User::factory()->createOne();
        $article = Article::factory()->create();
        $comment = Comment::factory()->forArticle()->create();

        $data = [
            'text' => fake()->paragraph(3),
            'rate' => 5,
            'type' => 'article',
            'id' => $article->uuid,
            'parent' => $comment->uuid,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.comments.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_see_self_comments(): void
    {
        $user = User::factory()->createOne();

        Comment::factory(5)->forArticle()->create([
            'user_uuid' => $user->uuid,
        ]);

        $otherUsers = User::factory(3)->create();
        foreach ($otherUsers as $otherUser) {
            Comment::factory(2)->forArticle()->create([
                'user_uuid' => $otherUser->uuid,
            ]);
        }

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.comments.index'))
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_user_can_see_self_comment(): void
    {
        $user = User::factory()->createOne();

        $comment = Comment::factory()->forArticle()->create([
            'user_uuid' => $user->uuid,
        ]);
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.comments.show', $comment))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_cannot_see_other_comment(): void
    {
        $user = User::factory()->createOne();

        $comment = Comment::factory()->forArticle()->create([
            'user_uuid' => $user->uuid,
        ]);

        $otherUser = User::factory()->createOne();

        $this->actingAs($otherUser, 'sanctum')
            ->getJson(route('api.v1.comments.show', $comment))
            ->assertStatus(404);
    }


    public function test_user_can_app_or_rej_comment(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');
        $comment = Comment::factory()->forArticle()->createOne();

        $data = [
            'status' => fake()->randomElement(['rejected','approved']),
            'answer' => fake()->paragraph(1),
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.comments.answer',$comment),$data)
            ->assertStatus(200);

        $this->assertDatabaseHas('comments', [
            'uuid' => $comment->uuid,
            'status' => $data['status']
        ]);
    }

    public function test_user_can_delete_comment(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');
        $comment = Comment::factory()->forArticle()->createOne();

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.comments.destroy',$comment))
            ->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'uuid' => $comment->uuid
        ]);
    }

    public function test_user_can_edit_comment(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');
        $comment = Comment::factory()->forArticle()->createOne();

        $data = [
            'status' => fake()->randomElement(['rejected','approved']),
            'text' => fake()->paragraph(1),
            'rate' => rand(0,5)
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.comments.update',$comment),$data)
            ->assertStatus(200);

        $this->assertDatabaseHas('comments', [
            'uuid' => $comment->uuid,
            'status' => $data['status'],
            'text' => $data['text'],
            'rate' => $data['rate'],
        ]);
    }

    public function test_user_can_see_all_comments(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        Comment::factory(5)->forArticle()->create([
            'user_uuid' => $user->uuid,
        ]);

        $otherUsers = User::factory(3)->create();
        foreach ($otherUsers as $otherUser) {
            Comment::factory(2)->forArticle()->create([
                'user_uuid' => $otherUser->uuid,
            ]);
        }

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.comments.index'))
            ->assertStatus(200)
            ->assertJsonCount(11, 'data.data');
    }

    public function test_other_user_cannot_any_access_comments(): void
    {
        $user = User::factory()->createOne();

        $comment = Comment::factory()->forArticle()->createOne();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.comments.index'), [])
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.comments.show',$comment))
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.comments.destroy',$comment))
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.comments.update',$comment), [])
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.comments.answer',$comment), [
                'status' => 'approved',
                'answer' => 'test answer'
            ])
            ->assertStatus(403);
    }

    public function test_user_can_see_article_comments(): void
    {
        $user = User::factory()->createOne();
        $article = Article::factory()->createOne();

        $comments = Comment::factory(10)->forArticle()->create([
            'status' => 'approved',
        ]);

        Comment::factory(3)->forArticle()->create([
            'parent_uuid' => $comments->first()->uuid,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.comments.article',$article))
            ->assertStatus(200)
            ->assertJsonCount(10, 'data.data');
    }
}
