<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_register(): void
    {
        $data = [
            'first_name' => 'محمد',
            'last_name' => 'ملکی',
            'email' => 'mohammad@test.com',
            'password' => 'password',
        ];

        $this->postJson('/api/v1/auth/register', $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        $data = [
            'first_name' => 'محمد',
            'last_name' => 'ملکی',
            'email' => 'mohammad@test.com',
            'password' => 'password',
        ];

        $this->postJson('/api/v1/auth/register', $data)->assertStatus(201);

        $this->postJson('/api/v1/auth/register', $data)->assertStatus(422);
    }

    public function test_user_cannot_register_with_invalid_email()
    {
        $data = [
            'first_name' => 'محمد',
            'last_name' => 'ملکی',
            'email' => 'mohammad',
            'password' => 'password',
        ];

        $this->postJson('/api/v1/auth/register', $data)->assertStatus(422);
    }

    public function test_user_cannot_register_with_short_or_unmatched_password()
    {
        $data = [
            'first_name' => 'محمد',
            'last_name' => 'ملکی',
            'email' => 'mohammad@test.com',
            'password' => '123',
        ];

        $this->postJson('/api/v1/auth/register', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'password'
            ]);
    }

    public function test_user_cannot_register_with_missing_fields()
    {
        $data = [
            'email' => 'mohammad',
            'password' => 'password',
        ];

        $this->postJson('/api/v1/auth/register', $data)->assertStatus(422);
    }
}
