<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;


class ImageService
{
    public function upload(array $files): array
    {
        try {
            $imageManager = new ImageManager(
                new \Intervention\Image\Drivers\Gd\Driver() // یا use Gd\Driver() اگر GD داری
            );

            $allResults = [];
            $quality = 75;
            $sizes = [
                'original' => 1200,
                'preview' => 600,
                'thumb' => 300,
            ];

            $directory = join('/', [now()->year, now()->month, now()->day]);

            foreach ($files as $file) {
                $basename = uniqid();
                $result = [];
                foreach ($sizes as $label => $width) {
                    $filename = "{$basename}_{$label}.webp";
                    $path = "{$directory}/{$filename}";

                    $image = $imageManager->read($file->getRealPath());

                    if ($image->width() > $width) {
                        $image = $image->scaleDown(width: $width);
                    }

                    $encoded = $image->toWebp(quality: $quality);
                    Storage::drive('uploads')->put($path, $encoded);

                    $result[$label] = Storage::drive('uploads')->url("{$directory}/{$filename}");
                }
                $allResults[] = $result;
            }

            return $allResults;
        } catch (\Throwable $e) {
            Log::error('Images Upload Service Error ', ['error' => $e->getMessage()]);
            throw new $e;
        }
    }

    public function delete(string $fullPath): bool
    {
        try {
            // حذف "/uploads/" از ابتدای مسیر
            $relativePath = ltrim(str_replace('uploads/', '', $fullPath), '/');

            // جدا کردن دایرکتوری و نام فایل
            $pathParts = explode('/', $relativePath);
            $filename = array_pop($pathParts); // 650f85b45a9a0_original.webp
            $directory = implode('/', $pathParts); // 23/2/4

            // استخراج basename
            $basename = preg_replace('/_(original|preview|thumb)\.webp$/', '', $filename);
            if (!$basename) {
                throw new \Exception("Invalid filename format: $filename");
            }

            $sizes = ['original', 'preview', 'thumb'];

            foreach ($sizes as $label) {
                $path = "{$directory}/{$basename}_{$label}.webp";

                if (Storage::drive('uploads')->exists($path)) {
                    Storage::drive('uploads')->delete($path);
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Images Deletion Service Error', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

