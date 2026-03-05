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

## Testing

### Pruebas de Autenticación Frontend
```bash
npm run test -- auth.spec.ts
npm run test -- useAuth.spec.ts
```

### Tests Requeridos
- Login con credenciales válidas
- Login con credenciales inválidas
- Logout limpia estado
- Guard de router protege rutas
- Interceptor añade token
- Redirección según rol

### Estructura de Tests
```
frontend/tests/
├── stores/
│   └── auth.spec.ts
├── composables/
│   └── useAuth.spec.ts
└── components/
    └── LoginForm.spec.ts
```

### Ejemplo: auth.spec.ts
```typescript
import { setActivePinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/api/auth', () => ({
  login: vi.fn(),
  logout: vi.fn(),
}))

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
  })

  it('login sets user and token', async () => {
    const mockUser = { id: 1, name: 'Admin', role: 'admin' }
    const mockToken = 'jwt-token'
    
    vi.mocked(login).mockResolvedValueOnce({ user: mockUser, token: mockToken })
    
    const store = useAuthStore()
    await store.login('admin@test.com', 'password')

    expect(store.user).toEqual(mockUser)
    expect(store.token).toBe(mockToken)
    expect(localStorage.getItem('token')).toBe(mockToken)
  })

  it('logout clears user and token', () => {
    const store = useAuthStore()
    store.user = { id: 1, name: 'Admin' }
    store.token = 'token'

    store.logout()

    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(localStorage.getItem('token')).toBeNull()
  })

  it('isAdmin returns true for admin role', () => {
    const store = useAuthStore()
    store.user = { id: 1, role: 'admin' }

    expect(store.isAdmin).toBe(true)
    expect(store.isCajero).toBe(false)
  })
})
```

### Ejemplo: Router Guard Test
```typescript
import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/login', name: 'login' },
  { path: '/dashboard', name: 'dashboard', meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
  } else {
    next()
  }
})

describe('router auth guard', () => {
  it('redirects to login when no token', async () => {
    const push = vi.fn()
    router.push('/dashboard')
    
    await router.isReady()
    
    expect(router.currentRoute.value.name).toBe('login')
  })
})
```

## Entregables
- Login funcional
- JWT almacenado y enviado en requests
- Proteccion de rutas
- Logout funcionando
