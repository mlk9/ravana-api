<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:250'],
            'last_name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)
                ->mixedCase()     // حروف بزرگ و کوچک
                ->letters()       // شامل حروف باشد
                ->numbers()       // شامل عدد باشد
                ->symbols()       // شامل نمادها باشد
                ->uncompromised() // در دیتابیس رمزهای لو رفته نباشد
            ],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::query()->create($validated);
        $token = $user->createToken('First Api')->plainTextToken;
        return $this->success([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token
            ],
            'code' => 201,
            'cookie' => Cookie::make('auth_token', $token, 60 * 24, '/', null, false, true)
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:16'],
        ]);
        $user = User::query()->where('email', $request->input('email'))->first();
        if ($user) {
            if (Hash::check($request->input('password'), $user->password)) {

                if($user->suspended_at)
                {
                    return $this->error([
                        'message' => __('Your account has been suspended due to imposed restrictions. Please contact support for further information.'),
                        'errors' => ['email' => __('Your account has been suspended due to imposed restrictions. Please contact support for further information.')],
                        'code' => 403
                    ]);
                }
                $token = $user->createToken('First Api')->plainTextToken;
                return $this->success([
                    'data' => [
                        'user' => new UserResource($user),
                        'token' => $token
                    ],
                    'code' => 200,
                    'cookie' => Cookie::make('auth_token', $token, 60 * 24, '/', null, false, true)
                ]);
            }
        }


        return $this->error([
            'message' => __('No users matched the given criteria.'),
            'errors' => ['email' => __('No users matched the given criteria.')],
            'code' => 404
        ]);
    }

    public function forgot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);
        $user = User::query()->where('email', $request->input('email'))->first();
        if ($user) {
            $token = \Illuminate\Support\Facades\Password::createToken($user);
        }

        return $this->success();
    }

    public function change_password(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()     // حروف بزرگ و کوچک
                ->letters()       // شامل حروف باشد
                ->numbers()       // شامل عدد باشد
                ->symbols()       // شامل نمادها باشد
                ->uncompromised() // در دیتابیس رمزهای لو رفته نباشد
            ],
        ]);
        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user) {
            if (\Illuminate\Support\Facades\Password::tokenExists($user, $request->input('token'))) {
                $user->update(['password' => Hash::make($request->input('password'))]);
                return $this->success();
            } else {
                return $this->error([
                    'message' => __('The provided token is invalid. Please ensure the information you entered is correct.')
                ]);
            }
        }

        return $this->error();
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success(['data' => new UserResource($request->user())]);
    }

    public function profile_update(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:250'],
            'last_name' => ['required', 'string', 'max:250'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', Password::min(8)
                ->mixedCase()     // حروف بزرگ و کوچک
                ->letters()       // شامل حروف باشد
                ->numbers()       // شامل عدد باشد
                ->symbols()       // شامل نمادها باشد
                ->uncompromised() // در دیتابیس رمزهای لو رفته نباشد
            ],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $res = $user->update($validated);
        if ($res) {
            return $this->success(['data' => new UserResource($user)]);
        } else {
            return $this->error();
        }
    }


}
