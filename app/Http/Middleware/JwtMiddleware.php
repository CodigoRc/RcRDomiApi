<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $request->attributes->add(['userId' => $decoded->sub]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}


