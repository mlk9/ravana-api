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
            ->getJson(route('api.v1.panel.auth.profile.show'))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create();
        $data = [
            'first_name' => 'test',
            'last_name' => 'Test',
        ];
        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.auth.profile.update'),$data)
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'first_name' => 'test',
            'last_name' => 'Test',
        ]);
    }
}
