# Phase 8: Frontend - Autenticación

## Objetivo
Implementar autenticación en Vue.js con JWT.

## Tareas

### 8.1 Estructura
- [ ] Crear router con vue-router
- [ ] Crear Pinia store para auth
- [ ] Crear composables/useAuth

### 8.2 Router
- [ ] Rutas públicas: /login
- [ ] Rutas privadas: /dashboard, /entry, /exit, /reports, /admin
- [ ] Guard: verificar token antes de navegar
- [ ] Redirect a /login si no autenticado

### 8.3 Auth Store (Pinia)
- [ ] state: user, token, isAuthenticated
- [ ] actions: login(), logout(), fetchUser()
- [ ] getters: isAdmin, isCajero, isSupervisor

### 8.4 Login Page
- [ ] Formulario: email, password
- [ ] Validación frontend
- [ ] Llamar API /api/auth/login
- [ ] Guardar token en localStorage
- [ ] Redireccionar según rol

### 8.5 Axios Configuration
- [ ] Configurar interceptor para agregar Authorization header
- [ ] Manejar 401 → logout + redirect login
- [ ] Configurar baseURL

### 8.6 Logout
- [ ] Llamar API /api/auth/logout
- [ ] Limpiar token y user del store
- [ ] Redirect a /login

## Entregables
- Login funcional
- JWT almacenado y enviado en requests
- Proteccion de rutas
- Logout funcionando
