<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileTest extends TestCase
{

    use RefreshDatabase;

    public function test_can_see_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/profile')
            ->assertStatus(200)
            ->assertJsonStructure(['user']);
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create();
        $data = [
            'first_name' => 'test',
            'last_name' => 'Test',
        ];
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/profile',$data)
            ->assertStatus(200)
            ->assertJsonStructure(['user']);
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'first_name' => 'test',
            'last_name' => 'Test',
        ]);
    }
}
