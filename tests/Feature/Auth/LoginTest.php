<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['user']]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => '12345678'])
            ->assertStatus(404);
    }

    public function test_suspended_user_cannot_login(): void
    {
        $user = User::factory()->suspended()->createOne();

        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(403);
    }

    public function test_user_cannot_use_after_suspended(): void
    {
        $user = User::factory()->createOne();

        $this->actingAs($user,'sanctum')
            ->getJson(route('api.v1.panel.auth.profile.show'))
            ->assertStatus(200);

        $user->update(['suspended_at' => now()]);

        $this->actingAs($user,'sanctum')
            ->getJson(route('api.v1.panel.auth.profile.show'))
            ->assertStatus(403);
    }
}
