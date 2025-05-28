<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required','string', 'max:250'],
            'last_name' => ['required','string', 'max:250'],
            'email' => ['required','email', 'unique:users,email'],
            'password' => ['required','string', 'min:8', 'max:16'],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::query()->create($validated);

        return $this->success([
            'data' => [
                'user' => $user,
                'token' => $user->createToken('First Api')->plainTextToken
            ],
            'code' => 201
        ]);
    }

    public function login(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string', 'min:8', 'max:16'],
        ]);
        $user = User::query()->where('email',$request->input('email'))->first();
        if($user)
        {
            if(Hash::check($request->input('password'),$user->password))
            {
                return $this->success([
                    'data' => [
                        'user' => $user,
                        'token' => $user->createToken('First Api')->plainTextToken
                    ],
                    'code' => 200
                ]);
            }
        }
        return $this->error([
            'message' => __('No users matched the given criteria.'),
            'errors' => ['email' => __('No users matched the given criteria.')],
            'code' => 404
        ]);
    }

    public function forgot(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required','email'],
        ]);
        $user = User::query()->where('email',$request->input('email'))->first();
        if($user)
        {
          $token = Password::createToken($user);
        }

        return $this->success();
    }

    public function change_password(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required','email'],
            'token' => ['required','string'],
            'password' => ['required','string', 'confirmed'],
        ]);
        $user = User::query()->where('email',$request->input('email'))->first();

        if($user)
        {
            if(Password::tokenExists($user,$request->input('token')))
            {
                $user->update(['password' => Hash::make($request->input('password'))]);
                return $this->success();
            }else{
                return $this->error([
                    'message' => __('The provided token is invalid. Please ensure the information you entered is correct.')
                ]);
            }
        }

        return $this->error();
    }

    public function profile(Request $request) : JsonResponse
    {
        return $this->success(['data' => $request->user()->toArray()]);
    }

    public function profile_update(Request $request) : JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required','string', 'max:250'],
            'last_name' => ['required','string', 'max:250'],
            'email' => ['nullable','email', 'unique:users,email'],
            'password' => ['nullable','string', 'min:8', 'max:16'],
        ]);

        if($request->filled('password'))
        {
            $validated['password'] = Hash::make($validated['password']);
        }
        $res = $user->update($validated);
        if($res)
        {
            return $this->success(['data' => $request->user()->toArray()]);
        }else{
            return $this->error();
        }
    }


}
