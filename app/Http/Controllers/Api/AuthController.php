<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
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

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('First Api')->plainTextToken
        ],201);
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
                return response()->json([
                    'user' => $user,
                    'token' => $user->createToken('First Api')->plainTextToken
                ],200);
            }
        }

        return response()->json(['email' => 'user not founded!'],404);
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

        return response()->json([],200);
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
                return response()->json([],200);
            }else{
                return response()->json([],422);
            }
        }

        return response()->json([],422);
    }

    public function profile(Request $request) : JsonResponse
    {
        return response()->json(['user' => $request->user()->toArray()],200);
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
        return response()->json(['user' => $user],$res ? 200 : 422);
    }


}
