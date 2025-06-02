<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ImageService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use function Ramsey\Uuid\v1;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_upload_and_compress_images()
    {
        // 1. فیک کردن فضای ذخیره‌سازی
        Storage::fake('uploads');

        // 2. ساخت فایل تستی
        $testFile = UploadedFile::fake()->image('example.jpg', 1600, 1600);

        // 3. اجرای تابع
        $service = new ImageService();
        $results = $service->upload([$testFile]);

        // 4. بررسی ساختار خروجی
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertArrayHasKey('original', $results[0]);
        $this->assertArrayHasKey('preview', $results[0]);
        $this->assertArrayHasKey('thumb', $results[0]);

        // 5. بررسی وجود فایل‌ها در مسیر صحیح
        $now = now();
        $basePath = "{$now->year}/{$now->month}/{$now->day}";

        foreach (['original', 'preview', 'thumb'] as $label) {
            $fileUrl = $results[0][$label];
            $filename = basename($fileUrl); // مثل: 664bdff2d6f5a_original.webp
            Storage::disk('uploads')->assertExists("{$basePath}/{$filename}");
        }
    }

    public function test_user_can_upload_image(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->createOne();
        $user->assignRole('writer');

        // شبیه‌سازی فایل
        $file = UploadedFile::fake()->image('test.jpg', 1200, 1200);

        // ارسال درخواست به API فرضی
        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.images.upload'),[
                'images' => [$file],
            ]);

        // بررسی موفقیت
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['original', 'preview', 'thumb'],
            ]
        ]);
    }

    public function test_user_cannot_upload_image_with_wrong_mime(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->createOne();
        $user->assignRole('writer');

        // شبیه‌سازی فایل
        $file = UploadedFile::fake()->image('test.tto', 1200, 1200);

        // ارسال درخواست به API فرضی
        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.v1.panel.images.upload'),[
                'images' => [$file],
            ]);

        // بررسی موفقیت
        $response->assertStatus(422);
    }

    public function test_user_can_delete_image(): void
    {
        Storage::fake('uploads');

        $user = User::factory()->create();
        $user->assignRole('writer');

        $file = UploadedFile::fake()->image('test.jpg', 1200, 1200);

        $uploadResponse = $this->actingAs($user, 'sanctum')->postJson(route('api.v1.panel.images.upload'), [
            'images' => [$file],
        ]);

        $uploadResponse->assertStatus(200);
        $url = $uploadResponse['data'][0]['original'];

        // حذف تصویر
        $deleteResponse = $this->actingAs($user, 'sanctum')->deleteJson(route('api.v1.panel.images.delete'), [
            'url' => $url,
        ]);

        $deleteResponse->assertStatus(200);

        // استخراج مسیر فایل از URL
        $path = str_replace(Storage::disk('uploads')->url('/'), '', $url);

        Storage::disk('uploads')->assertMissing($path);
    }


}
