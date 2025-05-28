<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryTest extends TestCase
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
    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $data = [
            'name' => 'Cat One',
            'slug' => 'cat-one',
            'descriptions' => fake()->paragraph(5),
            'parent_uuid' => null,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.categories.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_create_category_with_parent(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->createOne();
        $user->assignRole('writer');


        $data = [
            'name' => 'Cat One',
            'slug' => 'cat-one',
            'descriptions' => fake()->paragraph(5),
            'parent_uuid' => $category->uuid,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.categories.store'), $data)
            ->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_user_cannot_create_category_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $data = [
            'name' => 'Cat One',
            'slug' => 'cat-one',
            'descriptions' => 'ddd',
            'parent_uuid' => 'dcede',
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.categories.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['parent_uuid', 'descriptions']]);
    }

    public function test_user_cannot_create_category_with_duplicate(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $category = Category::factory()->createOne();
        $data = [
            'name' => 'Cat One',
            'slug' => $category->slug,
            'descriptions' => fake()->paragraph(5),
            'parent_uuid' => null,
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.categories.store'), $data)
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['slug']]);
    }

    public function test_user_cannot_see_other_categories(): void
    {
        // ساخت کاربران و مقالات متعلق به بقیه کاربران
        $otherUsers = User::factory(5)->create();
        foreach ($otherUsers as $otherUser) {
            Category::factory(3)->create([
                'creator_uuid' => $otherUser->uuid,
            ]);
        }

        // ساخت کاربر تست و بدون هیچ مقاله‌ای برای خودش
        $user = User::factory()->create();
        $user->assignRole('writer');

        // احراز هویت و ارسال درخواست
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.categories.index'))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data.data'); // چون مقاله‌ای نداره، انتظار داریم خروجی صفر باشه
    }

    public function test_user_can_see_self_categories(): void
    {
        // ساخت کاربر تست
        $user = User::factory()->create();
        $user->assignRole('writer');

        // ساخت مقالات برای کاربر تست
        Category::factory(5)->create([
            'creator_uuid' => $user->uuid,
        ]);

        // ساخت چند مقاله برای کاربران دیگر
        $otherUsers = User::factory(3)->create();
        foreach ($otherUsers as $otherUser) {
            Category::factory(2)->create([
                'creator_uuid' => $otherUser->uuid,
            ]);
        }

        // احراز هویت و ارسال درخواست
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.categories.index'))
            ->assertStatus(200)
            ->assertJsonCount(5, 'data.data'); // فقط ۵ مقاله خودش باید نمایش داده شود
    }


    public function test_user_can_update_self_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $categoryP = Category::factory()->createOne();

        $category = Category::factory(1)->createOne([
            'slug' => 'category-one',
        ]);

        $data = [
            'name' => 'Category One 2',
            'slug' => 'category-one',
            'parent_uuid' => $categoryP->uuid
        ];

        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.v1.panel.categories.update',$category), $data)
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertDatabaseHas(Category::class,[
            'uuid' => $category->uuid,
            'name' => 'Category One 2',
            'slug' => 'category-one',
            'parent_uuid' => $categoryP->uuid
        ]);
    }

    public function test_user_cannot_update_other_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $category = Category::factory()->createOne();

        $userOther = User::factory()->create();

        $this->actingAs($userOther, 'sanctum')
            ->putJson(route('api.v1.panel.categories.update',$category), [])
            ->assertStatus(403);
    }

    public function test_user_can_see_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $category = Category::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.v1.panel.categories.show',$category))
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_can_delete_self_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $category = Category::factory()->createOne();

        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.v1.panel.categories.destroy',$category))
            ->assertStatus(200);
        $this->assertDatabaseEmpty(Category::class);
    }

    public function test_user_cannot_delete_other_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('writer');

        $category = Category::factory()->createOne();

        $userOther = User::factory()->create();

        $this->actingAs($userOther, 'sanctum')
            ->deleteJson(route('api.v1.panel.categories.destroy',$category))
            ->assertStatus(403);
        $this->assertDatabaseCount(Category::class ,1);
    }

    public function test_user_can_see_categories(): void
    {
        $user = User::factory()->create();
        $categories = Category::factory(5)->create();

        $this->getJson(route('api.v1.categories.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['data' => [ 0 => ['name'] ] ]]);

        $this->getJson(route('api.v1.categories.show', $categories->first()))
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['name']]);
    }
}
