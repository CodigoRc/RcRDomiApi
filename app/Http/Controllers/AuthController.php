<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    private $jwtSecret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'rememberMe' => 'nullable|boolean',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Determinar el tiempo de expiración del token
        $expirationTime = $credentials['rememberMe'] ? null : time() + 10 * 24 * 60 * 60; // 10 días

        $payload = [
            'iss' => "your-issuer", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => $expirationTime // Expiration time
        ];

        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

        return response()->json(['access_token' => $jwt]);
    }

    public function me(Request $request)
    {
        // Obtener el token desde el cuerpo de la solicitud
        $token = $request->input('access_token');

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $user = User::find($decoded->sub);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Devolver el usuario y el token de acceso
            return response()->json([
                'user' => $user,
                'access_token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function refresh(Request $request)
    {
        // Obtener el token desde el cuerpo de la solicitud
        $token = $request->input('access_token');

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            if (isset($decoded->exp) && $decoded->exp < time()) {
                return response()->json(['message' => 'Token expired'], 401);
            }

            $payload = [
                'iss' => "your-issuer",
                'sub' => $decoded->sub,
                'iat' => time(),
                'exp' => time() + 10 * 24 * 60 * 60 // 10 días
            ];

            $newToken = JWT::encode($payload, $this->jwtSecret, 'HS256');

            return response()->json(['access_token' => $newToken]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token: ' . $e->getMessage()], 401);
        }
    }

    public function logout(Request $request)
    {
        // No hay lógica específica para el cierre de sesión en este ejemplo,
        // pero puedes agregar cualquier lógica necesaria aquí.
        return response()->json(['message' => 'Successfully logged out']);
    }
}