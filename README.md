# Parking Web App

Sistema de gestión de estacionamiento con Laravel, Vue.js y MySQL.

## Estructura del Proyecto

```
parking-web-app/
├── backend/          # Laravel 12 API
├── frontend/         # Vue.js 3 SPA
├── database/         # MySQL 8.0
├── docker-compose.yml
├── DOCS/            # Documentación de fases
└── README.md
```

## Tech Stack

- **Backend**: Laravel 12 + PHP 8.4
- **Frontend**: Vue.js 3 + TypeScript + Vite
- **Database**: MySQL 8.0
- **Auth**: JWT (tymon/jwt-auth)
- **Container**: Docker

## Quick Start

```bash
# Iniciar contenedores
docker compose up -d

# Verificar estado
docker compose ps

# Acceder al backend
curl http://localhost:8000

# Acceder al frontend
curl http://localhost:80
```

## Credenciales de Prueba

| Rol        | Email                  | Password |
| ---------- | ---------------------- | -------- |
| Admin      | admin@parking.com      | password |
| Cajero     | cajero@parking.com     | password |
| Supervisor | supervisor@parking.com | password |

## API Endpoints

```
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
POST /api/auth/refresh
GET  /api/auth/me

GET    /api/parking-spaces
POST   /api/parking-spaces
GET    /api/parking-spaces/available-count

POST   /api/tickets
GET    /api/tickets/active
GET    /api/tickets/search?q=
GET    /api/tickets/{id}/calculate
POST   /api/tickets/{id}/checkout

GET    /api/reports/daily
GET    /api/reports/monthly

GET    /api/users
POST   /api/users
PUT    /api/users/{id}
DELETE /api/users/{id}
```

---

## Work Flow

### Phase 1: Setup y Configuración

**Objetivo**: Configurar el entorno de desarrollo y estructura base del proyecto.

**Tareas completadas**:

1. **Estructura de directorios** - Creada carpeta raíz con backend/, frontend/, database/
2. **Backend Laravel**:
   - Laravel 12 instalado
   - JWT (tymon/jwt-auth) configurado
   - Conexión MySQL configurada
3. **Frontend Vue.js**:
   - Vue 3 + Vite + TypeScript
   - Pinia, Vue Router, Axios instalados
   - TailwindCSS configurado
4. **Docker**:
   - docker-compose.yml con 3 servicios
   - Dockerfile para backend (PHP 8.4-fpm-alpine)
   - Dockerfile para frontend (Node.js build + Nginx)
   - Dockerfile para MySQL 8.0

**Entregables**:

- Proyecto Laravel configurado con JWT
- Proyecto Vue.js inicializado
- Contenedores Docker funcionando
- Base de datos creada y conectada

---

### Phase 2: Base de Datos y Modelos

**Objetivo**: Crear el esquema de base de datos y modelos Eloquent.

**Tareas completadas**:

1. **Migraciones**:
   - `users`: id, name, email, password, role (admin/cajero/supervisor), remember_token
   - `parking_spaces`: id, number, type (general/discapacitado/eléctrico), status (disponible/ocupado/fuera_servicio)
   - `tickets`: id, plate_number, vehicle_type (auto/moto/camioneta), entry_time, exit_time, parking_space_id, status (activo/finalizado)
   - `payments`: id, ticket_id, total, payment_method (efectivo/tarjeta), paid_at

2. **Modelos Eloquent**:
   - `User` - JWTSubject, hasMany(Ticket), hasMany(Payment)
   - `ParkingSpace` - hasMany(Ticket), scopes (available/occupied/outOfService)
   - `Ticket` - belongsTo(ParkingSpace), belongsTo(User), hasOne(Payment), scopes (active/completed)
   - `Payment` - belongsTo(Ticket), belongsTo(User)

3. **Factories**:
   - UserFactory con estados admin/cajero/supervisor
   - ParkingSpaceFactory con tipos y estados
   - TicketFactory con vehicle_type y status
   - PaymentFactory con efectivo/tarjeta

4. **Seeders**:
   - 3 usuarios (admin, cajero, supervisor)
   - 13 espacios de estacionamiento

5. **Tests**:
   - UserTest (relaciones, roles)
   - ParkingSpaceTest (relaciones, scopes)
   - TicketTest (relaciones, scopes)
   - PaymentTest (relaciones, métodos)

**Entregables**:

- Migraciones ejecutadas
- Modelos con relaciones
- Datos de prueba (seeders)
- Tests de modelos

---

### Phase 3: Autenticación JWT

**Objetivo**: Implementar autenticación con JWT usando tymon/jwt-auth.

**Tareas completadas**:

1. **Configuración JWT**:
   - TTL configurado (60 min)
   - Refresh TTL configurado (20160 min)
   - JWT secret generado

2. **AuthController**:
   - `login()` - retorna token con datos del usuario
   - `register()` - crea usuario y retorna token
   - `logout()` - invalida el token
   - `refresh()` - genera nuevo token
   - `me()` - retorna usuario actual

3. **Rutas API**:

   ```
   POST /api/auth/login
   POST /api/auth/register
   POST /api/auth/logout
   POST /api/auth/refresh
   GET  /api/auth/me
   ```

4. **Middleware**:
   - Using `auth:api` guard (JWT)

5. **AuthService**:
   - Created `app/Services/AuthService.php`

6. **Tests**:
   - AuthApiTest (10 test cases)
   - AuthServiceTest (7 test cases)

**Entregables**:

- Endpoints de autenticación funcionando
- JWT token generado y validado
- Logout y refresh token implementados

---

### Phase 4: Gestión de Espacios

**Objetivo**: Implementar API REST para espacios de estacionamiento con CRUD, paginación y políticas de acceso.

**Tareas completadas**:

1. **ParkingSpaceController**:
   - `index()` - Listar espacios con paginación (filtros: type, status)
   - `store()` - Crear espacio (Admin)
   - `show()` - Ver espacio específico
   - `update()` - Actualizar espacio (Admin)
   - `destroy()` - Eliminar espacio (Admin)

2. **ParkingSpaceService**:
   - Lógica de negocio separada
   - Métodos: getAll(), getById(), create(), update(), delete(), getAvailableCount()

3. **ParkingSpacePolicy**:
   - Admin: CRUD completo
   - Cajero/Supervisor: solo lectura (index, show)

4. **Rutas API**:

   ```
   GET    /api/parking-spaces              # Listar (público)
   POST   /api/parking-spaces              # Crear (Admin)
   GET    /api/parking-spaces/{id}         # Ver (público)
   PUT    /api/parking-spaces/{id}         # Actualizar (Admin)
   DELETE /api/parking-spaces/{id}         # Eliminar (Admin)
   GET    /api/parking-spaces/available    # Espacios disponibles
   GET    /api/parking-spaces/available-count  # Conteo disponibles
   ```

5. **Tests**:
   - ParkingSpaceApiTest (12 casos)
   - ParkingSpaceServiceTest (10 casos)

**Entregables**:

- Endpoints funcionando con paginación
- Políticas de acceso por rol
- Tests passing

**Issue**: Ruta `/api/parking-spaces/available-count` retorna 404 por conflicto con apiResource

### Phase 5: Tickets API

**Objetivo**: Implementar registro de entradas y salidas de vehículos.

**Tareas completadas**:

1. **TicketController**:
   - `index()` - Listar tickets (paginado)
   - `store()` - Crear ticket (entrada de vehículo)
   - `show()` - Ver ticket específico
   - `active()` - Listar tickets activos
   - `search()` - Buscar por placa
   - `calculate()` - Calcular costo
   - `checkout()` - Registrar salida y pago

2. **TicketService**:
   - Lógica de negocio separada
   - Métodos: getAll(), getById(), getActive(), searchByPlate(), create(), calculateFee(), checkout()
   - Asignación automática de cajón
   - Cambio de status del cajón (disponible → ocupado → disponible)

3. **Rutas API**:
   ```
   GET    /api/tickets                 # Listar (público)
   POST   /api/tickets                 # Crear ticket (Auth)
   GET    /api/tickets/{id}           # Ver ticket (público)
   GET    /api/tickets/active         # Tickets activos
   GET    /api/tickets/search?plate=  # Buscar por placa
   GET    /api/tickets/{id}/calculate # Calcular costo
   POST   /api/tickets/{id}/checkout  # Registrar salida
   ```

4. **Validaciones**:
   - plate_number: required|string|regex:/^[A-Z0-9-]+$/i
   - vehicle_type: required|in:auto,moto,camioneta
   - parking_space_id: required|exists:parking_spaces,id

5. **Tests**:
   - TicketApiTest (12 casos - 10 passing)
   - TicketServiceTest (11 casos)

**Entregables**:
- Registro de entrada funcionando
- Búsqueda de tickets por placa
- Tickets activos listados
- Cajón marcado como ocupado
- Cálculo de tarifa por hora
- Checkout con registro de pago

### Phase 6: Pagos

**Objetivo**: Implementar cálculo de tarifas y registro de pagos.

**Tareas completadas**:

1. **FeeCalculator** (CRÍTICO):
   - Tarifas por tipo de vehículo:
     - auto: $20/hora, $150/día
     - moto: $10/hora, $80/día
     - camioneta: $30/hora, $200/día
   - Tolerancia: 10 minutos = $0
   - Cobro por hora completa
   - Tarifa diaria después de 24h

2. **PaymentController**:
   - `index()` - Listar pagos (paginado)
   - `store()` - Crear pago
   - `show()` - Ver pago específico
   - `today()` - Pagos de hoy
   - `calculate()` - Calcular tarifa sin pagar

3. **PaymentService**:
   - Lógica de procesamiento de pagos
   - Métodos: getAll(), getById(), getToday(), calculateFee(), processPayment()

4. **Rutas API**:
   ```
   GET    /api/payments                 # Listar pagos (público)
   POST   /api/payments                 # Registrar pago (Auth)
   GET    /api/payments/{id}           # Ver pago (público)
   GET    /api/payments/today         # Pagos de hoy
   GET    /api/payments/calculate/{ticket_id}  # Calcular tarifa
   ```

5. **Validaciones**:
   - ticket_id: required|exists:tickets,id
   - payment_method: required|in:efectivo,tarjeta

6. **Tests**:
   - FeeCalculatorTest (17 casos - todos passing)
   - PaymentApiTest (10 casos - 7 passing)

**Entregables**:
- Cálculo de tarifas funcionando
- Tolerancia de 10 minutos
- Tarifas diferenciadas por tipo de vehículo
- Tarifa diaria después de 24h
- Procesamiento de pago completo
- Liberación de cajón al pagar

### Phase 7: Reportes

**Objetivo**: Implementar reportes diarios, mensuales y dashboard summary.

**Tareas completadas**:

1. **ReportController**:
   - `daily()` - Reporte del día actual
   - `monthly()` - Reporte del mes actual
   - `summary()` - Resumen para dashboard

2. **ReportService**:
   - Lógica de reportes
   - Métodos: getDailyReport(), getMonthlyReport(), getDashboardSummary(), canAccessReports()

3. **Rutas API**:
   ```
   GET /api/reports/daily          # Reporte diario (Admin/Supervisor)
   GET /api/reports/monthly        # Reporte mensual (Admin/Supervisor)
   GET /api/reports/summary        # Dashboard summary (público)
   ```

4. **Reporte Diario**:
   - total_ingresos
   - tickets_atendidos
   - promedio_por_ticket
   - cajones_disponibles
   - tickets_activos

5. **Reporte Mensual**:
   - ingresos_por_día
   - hora_pica
   - tipo_vehiculo_frecuente
   - total_ingresos_mes
   - total_tickets_mes

6. **Dashboard Summary**:
   - cajones_disponibles
   - ingresos_dia
   - tickets_activos
   - ultimos_tickets (últimos 5)

7. **Policies**:
   - Admin y Supervisor pueden ver reportes
   - Cajero no tiene acceso

8. **Tests**:
   - ReportApiTest (11 casos - todos passing)

**Entregables**:
- Reporte diario completo
- Reporte mensual con agregaciones
- Dashboard summary público
- Permisos por rol (Admin/Supervisor)

### Phase 8: Frontend - Autenticación

**Objetivo**: Implementar autenticación en Vue.js con JWT.

**Tareas completadas**:

1. **Estructura**:
   - Router con vue-router
   - Pinia store para auth
   - Composables/useAuth

2. **Router**:
   - Rutas públicas: /login
   - Rutas privadas: /dashboard, /entry, /exit, /reports, /admin
   - Guard: verificar token antes de navegar
   - Redirect a /login si no autenticado

3. **Auth Store (Pinia)**:
   - state: user, token, isAuthenticated
   - actions: login(), logout(), fetchUser()
   - getters: isAdmin, isCajero, isSupervisor

4. **API Configuration**:
   - Axios con interceptor para Authorization header
   - Manejo de 401 → logout + redirect login
   - baseURL configurado

5. **Login Page**:
   - Formulario: email, password
   - Validación frontend
   - Llamar API /api/auth/login
   - Guardar token en localStorage
   - Redireccionar según rol

6. **Logout**:
   - Llamar API /api/auth/logout
   - Limpiar token y user del store
   - Redirect a /login

**Archivos creados**:
- frontend/src/api/index.ts
- frontend/src/api/auth.ts
- frontend/src/stores/auth.ts
- frontend/src/composables/useAuth.ts
- frontend/src/router/index.ts
- frontend/src/views/Login.vue
- frontend/src/views/Dashboard.vue
- frontend/src/views/Entry.vue
- frontend/src/views/Exit.vue
- frontend/src/views/Reports.vue
- frontend/src/views/Admin.vue
- frontend/.env

**Entregables**:
- Login funcional
- JWT almacenado y enviado en requests
- Protección de rutas
- Logout funcionando
- Redirección según rol

### Phase 9: Dashboard
- [ ] Phase 11: Reportes
- [ ] Phase 12: Administración

### Phase 13: Testing

- [ ] PHPUnit tests
- [ ] Vitest tests
- [ ] Cobertura de código

---

## Comandos Útiles

```bash
# Reiniciar contenedores
docker compose down -v && docker compose up -d

# Ver logs
docker compose logs -f backend

# Ejecutar migraciones
docker exec parking_backend php artisan migrate

# Refrescar base de datos
docker exec parking_backend php artisan migrate:fresh --seed

# Acceder al contenedor backend
docker exec -it parking_backend sh

# Ver rutas API
docker exec parking_backend php artisan route:list

# Ejecutar tests
docker exec parking_backend php artisan test
```

## Variables de Entorno

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=parking_db
DB_USERNAME=parking_user
DB_PASSWORD=parking_password

# JWT
JWT_SECRET=<generated>
JWT_TTL=60
```

## Licencia

MIT
