<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function success(array $options = []): JsonResponse
    {
        $response = response()->json([
            'status' => true,
            'response_code' => $options['code'] ?? 200,
            'message' => $options['message'] ?? __('The action was executed successfully.'),
            'data' => $options['data'] ?? null,
            'meta' => $options['meta'] ?? null,
        ], $options['code'] ?? 200);

        if (isset($options['cookie']) && $options['cookie'] instanceof \Symfony\Component\HttpFoundation\Cookie) {
            $response->cookie($options['cookie']);
        }

        return $response;
    }

    public function error(array $options = []): JsonResponse
    {
        $response = response()->json([
            'status' => false,
            'response_code' => $options['code'] ?? 422,
            'message' => $options['message'] ?? __('Something went wrong. Please try again or contact customer support.'),
            'errors' => $options['errors'] ?? null,
        ], $options['code'] ?? 422);

        if (isset($options['cookie']) && $options['cookie'] instanceof \Symfony\Component\HttpFoundation\Cookie) {
            $response->cookie($options['cookie']);
        }

        return $response;
    }
}
