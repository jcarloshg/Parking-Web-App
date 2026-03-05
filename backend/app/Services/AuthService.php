<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials): array
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    public function register(array $data): array
    {
        $data['password'] = bcrypt($data['password']);
        
        $user = User::create($data);
        $token = Auth::guard('api')->login($user);

        return $this->respondWithToken($token, $user);
    }

    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    public function refresh(): array
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    public function me(): ?User
    {
        return Auth::guard('api')->user();
    }

    protected function respondWithToken(string $token, ?User $user = null): array
    {
        $user = $user ?? Auth::guard('api')->user();
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
        ];
    }
}
