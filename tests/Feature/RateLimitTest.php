<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limiting_blocks_excessive_requests()
    {
        // فرض کنیم محدودیت ۵ درخواست در دقیقه است
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson(route('api.v1.articles.index'))->assertStatus(200);
        }

        // درخواست ششم باید بلاک شود
        $response = $this->getJson(route('api.v1.articles.index'))->assertStatus(429);
    }

    public function test_auth_rate_limiting_blocks_excessive_requests()
    {
        $user = User::factory()->createOne();

        // فرض کنیم محدودیت ۵ درخواست در دقیقه است
        for ($i = 0; $i < 100; $i++) {
            $response = $this->actingAs($user,'sanctum')->getJson(route('api.v1.articles.index'))->assertStatus(200);
        }

        // درخواست ششم باید بلاک شود
        $response = $this->actingAs($user,'sanctum')->getJson(route('api.v1.articles.index'))->assertStatus(429);
    }
}
