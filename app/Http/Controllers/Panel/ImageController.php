<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    use ApiResponse;

    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function upload(Request $request) : JsonResponse
    {
        $request->validate([
            'images' => ['required','array'],
            'images.*' => ['image','mimes:jpeg,png,jpg,webp','max:2048'],
        ]);

        Gate::authorize('image-upload');

        try {
            $results = $this->imageService->upload($request->file('images'));
            if(empty($results))
            {
                throw new \RuntimeException(__("Failed to process uploaded images."));
            }

        }
        catch (\Throwable $e)
        {
            return $this->error(['message' => $e->getMessage()]);
        }

        return $this->success(['data' => $results]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request) : JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string', 'min:20', 'regex:/^[\w\-\/\.]+$/'],
        ]);

        Gate::authorize('image-delete');

        $success = $this->imageService->delete($request->input('url'));

        if (!$success) {
            return $this->error(['message' => __('Failed to delete images')]);
        }

        return $this->success();
    }
}
