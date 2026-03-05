<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $result = $this->authService->login([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function test_login_throws_exception_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->authService->login([
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $result = $this->authService->register([
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@test.com',
            'password' => 'password123',
            'role' => 'cajero',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@test.com',
            'name' => 'Nuevo Usuario',
        ]);

        $this->assertArrayHasKey('access_token', $result);
    }

    public function test_logout_invalidates_token(): void
    {
        $user = User::factory()->create();
        Auth::guard('api')->login($user);

        $this->authService->logout();

        $this->assertNull(Auth::guard('api')->user());
    }

    public function test_refresh_returns_new_token(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        $result = $this->authService->refresh();

        $this->assertArrayHasKey('access_token', $result);
    }

    public function test_me_returns_current_user(): void
    {
        $user = User::factory()->create();
        Auth::guard('api')->login($user);

        $result = $this->authService->me();

        $this->assertEquals($user->id, $result->id);
    }

    public function test_me_returns_null_when_not_authenticated(): void
    {
        $result = $this->authService->me();

        $this->assertNull($result);
    }
}
