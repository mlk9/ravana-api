<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use App\Models\User;

class AuthenticateWithJwtCookie
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('auth_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $payload = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
            $user = User::query()->find($payload->sub);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            auth()->login($user); // لاگین دستی

        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        return $next($request);
    }
}
