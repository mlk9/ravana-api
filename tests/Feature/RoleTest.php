<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    /**
     * A basic feature test example.
     */
    public function test_user_can_create_role(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $data = [
            'name' => 'custom-role',
            'permissions' => [
                'articles.index',
                'articles.create',
                'articles.edit',
                'articles.show',
                'articles.destroy',
            ]
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas(Role::class,['name' => 'custom-role']);
    }

    public function test_user_cannot_create_role_with_duplicate_name(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $data = [
            'name' => 'custom-role',
            'permissions' => [
                'articles.index',
                'articles.create',
                'articles.edit',
                'articles.show',
                'articles.destroy',
            ]
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['name']]);

        $this->assertDatabaseHas(Role::class,['name' => 'custom-role']);
    }

    public function test_user_cannot_create_role_with_unknown_permission(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $data = [
            'name' => 'custom-role',
            'permissions' => [
                'man1',
                'man2',
            ]
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['permissions.0']]);

        $this->assertDatabaseMissing(Role::class,['name' => 'custom-role']);
    }

    public function test_user_can_see_role(): void
    {
        // ساخت کاربر تست
        $user = User::factory()->create();
        $user->assignRole('ceo');

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.roles.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_update_role(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $data = [
            'id' => 99,
            'name' => 'custom-role',
            'permissions' => [
                'articles.index',
                'articles.create',
                'articles.edit',
                'articles.show',
                'articles.destroy',
            ]
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.roles.update',['role' => 'custom-role']), [
                'name' => 'custom2',
                'permissions' => [
                    'articles.index',
                    'articles.create',
                ]
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertDatabaseHas(Role::class,['name' => 'custom2']);
    }

    public function test_user_can_delete_roles(): void
    {
        $user = User::factory()->createOne();
        $user->assignRole('ceo');

        $data = [
            'id' => 88,
            'name' => 'custom-role',
            'permissions' => [
                'articles.index',
                'articles.create',
                'articles.edit',
                'articles.show',
                'articles.destroy',
            ]
        ];

        $res = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.roles.destroy',['role' => 'custom-role']))
            ->assertStatus(200);

        $this->assertDatabaseMissing(Role::class,['name' => 'custom-role']);
    }
    public function test_other_user_cannot_any_access_roles(): void
    {
        $user = User::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.roles.index'), [])
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), [])
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.roles.destroy',['role' => 'ceo']))
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.roles.update',['role' => 'ceo']), [])
            ->assertStatus(403);

    }
}
