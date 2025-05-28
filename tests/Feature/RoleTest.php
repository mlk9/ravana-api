<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $user = User::factory()->create();
        $user->assignRole('ceo');

        $data = [
            'name' => 'custom-role',
            'permissions' => [
                'articles.create',
                'articles.update',
            ]
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.roles.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }
}
