# Phase 3: Autenticación JWT

## Objetivo
Implementar autenticación con JWT usando tymon/jwt-auth.

## Tareas

### 3.1 Configuración JWT
- [ ] Publicar config de jwt-auth
- [ ] Configurar TTL (60 min)
- [ ] Configurar Refresh TTL (20160 min)
- [ ] Actualizar modelo User implementar JWTSubject
- [ ] Agregar métodos: getJWTIdentifier(), getJWTCustomClaims()

### 3.2 AuthController
- [ ] Método login(email, password) → retorna token
- [ ] Método register(name, email, password, role) → retorna token
- [ ] Método logout() → blacklisted token
- [ ] Método refresh() → nuevo token
- [ ] Método me() → datos usuario actual

### 3.3 Rutas API
```php
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
POST /api/auth/refresh
GET  /api/auth/me
```

### 3.4 Middleware
- [ ] Crear middleware JWT auth
- [ ] Proteger rutas privadas

### 3.5 Service/Repository
- [ ] AuthService para lógica de autenticación

## Validaciones
- Login: email (required|email), password (required)
- Register: name, email (required|email|unique), password (required|min:6), role (required|in:admin,cajero,supervisor)

## Testing

### Pruebas de Autenticación
```bash
./vendor/bin/phpunit --filter=AuthApiTest
./vendor/bin/phpunit --filter=AuthServiceTest
```

### Cobertura Objetivo
- AuthController: 85%
- AuthService: 90%

### Tests Requeridos
- Login exitoso retorna token JWT
- Login con credenciales inválidas retorna error
- Registro de usuario nuevo
- Logout invalida token
- Refresh token genera nuevo token
- Proteción de rutas con middleware JWT

### Estructura de Tests
```
tests/Feature/API/
├── AuthApiTest.php
tests/Unit/Services/
└── AuthServiceTest.php
```

### Ejemplo: AuthApiTest
```php
public function test_user_can_login_with_valid_credentials()
{
    $user = User::factory()->create(['password' => 'password123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access_token', 'token_type']);
}

public function test_login_fails_with_invalid_password()
{
    $user = User::factory()->create();

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
}

public function test_authenticated_user_can_logout()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/auth/logout');

    $response->assertStatus(200);
}

public function test_unauthenticated_user_cannot_access_protected_routes()
{
    $response = $this->getJson('/api/auth/me');
    $response->assertStatus(401);
}
```

## Entregables
- Endpoints de autenticación funcionando
- JWT token generado y validado
- Logout y refresh token
