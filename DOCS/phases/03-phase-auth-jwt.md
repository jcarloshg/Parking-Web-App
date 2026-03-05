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

## Entregables
- Endpoints de autenticación funcionando
- JWT token generado y validado
- Logout y refresh token
