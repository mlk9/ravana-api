<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function success(array $options = []): JsonResponse
    {
        return response()->json([
            'status' => true,
            'response_code' => $options['code'] ?? 200,
            'message' => $options['message'] ?? __('The action was executed successfully.'),
            'data' => $options['data'] ?? null,
            'meta' => $options['meta'] ?? null,
        ], $options['code'] ?? 200);
    }

    public function error(array $options = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'response_code' => $options['code'] ?? 422,
            'message' => $options['message'] ?? __('Something went wrong. Please try again or contact customer support.'),
            'errors' => $options['errors'] ?? null,
        ], $options['code'] ?? 422);
    }
}
