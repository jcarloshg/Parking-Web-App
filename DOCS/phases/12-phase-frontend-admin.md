# Phase 12: Frontend - Administration

## Objetivo
Implementar pantallas de administración.

## Tareas

### 12.1 Users Management (Admin)
- [ ] Listado usuarios (tabla paginada)
- [ ] Columnas: nombre, email, rol, fecha creación
- [ ] Botón crear usuario
- [ ] Modal/Slide-over crear:
  - Nombre
  - Email
  - Password
  - Rol (select)
- [ ] Botón editar usuario
- [ ] Modal editar
- [ ] Botón eliminar usuario (confirmación)
- [ ] API: GET/POST/PUT/DELETE /api/users

### 12.2 Parking Spaces Management (Admin)
- [ ] Listado cajones (grid o tabla)
- [ ] Columnas: número, tipo, estado
- [ ] Botón crear cajón
- [ ] Formulario crear:
  - Número
  - Tipo (select)
- [ ] Botón editar
- [ ] Formulario editar
- [ ] Botón eliminar
- [ ] Toggle status rápido
- [ ] API: CRUD /api/parking-spaces

### 12.3 Components
- [ ] UsersTable.vue
- [ ] UserForm.vue
- [ ] ParkingSpacesTable.vue
- [ ] ParkingSpaceForm.vue
- [ ] ConfirmDialog.vue

### 12.4 Permisos
- [ ] Admin: gestión completa
- [ ] Cajero: NO acceso
- [ ] Supervisor: NO acceso

### 12.5 Navigation
- [ ] Menú Admin solo visible para admin
- [ ] Rutas protegidas server-side también

## Testing

### Pruebas de Admin
```bash
npm run test -- AdminUsers.spec.ts
npm run test -- AdminParkingSpaces.spec.ts
npm run test -- UserForm.spec.ts
```

### Tests Requeridos
- CRUD usuarios completo
- CRUD cajones completo
- Validaciones de formularios
- Confirmación de eliminación
- Permisos (solo admin)
- Navegación restringida

### Estructura de Tests
```
frontend/tests/
├── pages/
│   ├── AdminUsers.spec.ts
│   └── AdminParkingSpaces.spec.ts
└── components/
    ├── UsersTable.spec.ts
    ├── UserForm.spec.ts
    └── ConfirmDialog.spec.ts
```

### Ejemplo: AdminUsers.spec.ts
```typescript
vi.mock('@/api/users', () => ({
  getUsers: vi.fn(),
  createUser: vi.fn(),
  updateUser: vi.fn(),
  deleteUser: vi.fn(),
}))

describe('AdminUsers', () => {
  it('displays users table', async () => {
    vi.mocked(getUsers).mockResolvedValueOnce([
      { id: 1, name: 'Admin User', email: 'admin@test.com', role: 'admin' },
    ])

    const wrapper = mount(AdminUsers)
    await flushPromises()

    expect(wrapper.text()).toContain('Admin User')
    expect(wrapper.text()).toContain('admin@test.com')
  })

  it('opens create user modal', async () => {
    const wrapper = mount(AdminUsers)
    
    await wrapper.find('[data-testid="create-btn"]').trigger('click')

    expect(wrapper.find('[data-testid="user-modal"]').exists()).toBe(true)
  })

  it('creates new user', async () => {
    vi.mocked(createUser).mockResolvedValueOnce({ id: 1, name: 'New User' })

    const wrapper = mount(AdminUsers)
    await wrapper.find('[data-testid="create-btn"]').trigger('click')
    
    await wrapper.find('[data-testid="name-input"]').setValue('New User')
    await wrapper.find('[data-testid="email-input"]').setValue('new@test.com')
    await wrapper.find('[data-testid="password-input"]').setValue('password')
    await wrapper.find('[data-testid="role-select"]').setValue('cajero')
    await wrapper.find('form').trigger('submit')

    expect(createUser).toHaveBeenCalledWith(expect.objectContaining({
      name: 'New User',
      email: 'new@test.com',
      role: 'cajero',
    }))
  })

  it('shows confirmation dialog before delete', async () => {
    const wrapper = mount(AdminUsers)
    
    await wrapper.find('[data-testid="delete-btn"]').trigger('click')

    expect(wrapper.findComponent(ConfirmDialog).exists()).toBe(true)
    expect(wrapper.text()).toContain('¿Estás seguro?')
  })

  it('deletes user after confirmation', async () => {
    vi.mocked(deleteUser).mockResolvedValueOnce(true)

    const wrapper = mount(AdminUsers)
    await wrapper.find('[data-testid="delete-btn"]').trigger('click')
    await wrapper.find('[data-testid="confirm-delete"]').trigger('click')

    expect(deleteUser).toHaveBeenCalledWith(1)
  })

  it('validates required fields', async () => {
    const wrapper = mount(AdminUsers)
    
    await wrapper.find('[data-testid="create-btn"]').trigger('click')
    await wrapper.find('form').trigger('submit')

    expect(wrapper.text()).toContain('El nombre es requerido')
    expect(wrapper.text()).toContain('El email es requerido')
  })
})
```

### Ejemplo: Navigation Permission Test
```typescript
describe('Admin navigation', () => {
  it('hides admin menu for non-admin users', () => {
    const store = useAuthStore()
    store.user = { id: 1, role: 'cajero' }

    const wrapper = mount(AppLayout)
    
    expect(wrapper.find('[data-testid="admin-menu"]').exists()).toBe(false)
  })

  it('shows admin menu for admin users', () => {
    const store = useAuthStore()
    store.user = { id: 1, role: 'admin' }

    const wrapper = mount(AppLayout)
    
    expect(wrapper.find('[data-testid="admin-menu"]').exists()).toBe(true)
  })
})
```

## Entregables
- CRUD completo usuarios
- CRUD completo cajones
- Solo admin tiene acceso
- Validaciones frontend
