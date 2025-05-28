<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_user_can_send_request(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/forgot', ['email' => $user->email])
            ->assertStatus(200);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create();

        $token = '123456'; // کدی که می‌خوای استفاده کنی
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token), // هش‌شده‌ی کد
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/forgot/change-password', [
            'email' => $user->email,
            'token' => $token, // نسخه غیر هش‌شده که توی فرم کاربر وارد می‌کنه
            'password' => '@Pp78651234',
            'password_confirmation' => '@Pp78651234',
        ])->assertStatus(200);

        // اطمینان از این که با رمز جدید می‌تونه وارد شه
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => '@Pp78651234',
        ])->assertStatus(200);
    }
}
