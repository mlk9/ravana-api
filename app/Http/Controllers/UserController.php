<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function me(Request $request): JsonResponse
    {
        return $this->success(['data' => $request->user()->toArray()]);
    }
}
