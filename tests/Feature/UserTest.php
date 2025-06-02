<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertJson;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_user_can_create_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ceo');

        $data = [
            'first_name' => 'Mohammad',
            'last_name' => 'Maleki',
            'email' => 'molkan99@gmail.com',
            'email_verify' => '1',
            'password' => '@Zz7113240',
            'roles' => ['ceo'],
            'permissions' => []
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.users.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_user_cannot_create_user_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ceo');

        $data = [
            'first_name' => 'Mohammad',
            'last_name' => 'Maleki',
            'email' => 'molkan99@gmail.com',
            'email_verify' => now()->timestamp,
            'password' => '@Zz7113240',
            'roles' => ['worker'],
            'permissions' => []
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.users.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors']);

        $data = [
            'first_name' => 'Mohammad',
            'last_name' => 'Maleki',
            'email' => 'molkan99@gmail.com',
            'email_verified_at' => null,
            'password' => '213',
            'roles' => ['ceo'],
            'permissions' => []
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.users.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors']);

        $data = [
            'first_name' => 'Mohammad',
            'last_name' => 'Maleki',
            'email' => $user->email,
            'email_verified_at' => now()->timestamp,
            'password' => '@Zz7113240',
            'roles' => ['worker'],
            'permissions' => []
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.users.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_user_can_see_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ceo');

        User::factory(10)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.users.index'))
            ->assertStatus(200)
            ->assertJsonCount(11, 'data.data');
    }

    public function test_user_cannot_see_users(): void
    {
        $user = User::factory()->create();

        User::factory(10)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.users.index'))
            ->assertStatus(403);
    }

    public function test_user_can_edit_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ceo');

        $data = [
            'email' => 'molkan99@gmail.com',
            'email_verify' => 'true',
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.users.update', $user), $data)
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'email' => 'molkan99@gmail.com',
        ]);
    }

    public function test_user_can_delete_user(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $otherUser = User::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.users.destroy', $otherUser))
            ->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'uuid' => $otherUser->uuid,
        ]);
    }

    public function test_user_can_suspended_user(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $otherUser = User::factory()->createOne();

        $data = [
            'suspend' => '1',
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.users.update', $otherUser), $data)
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
