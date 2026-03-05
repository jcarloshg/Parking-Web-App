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

- [ ] CRUD de espacios de estacionamiento
- [ ] Estados de espacios (disponible/ocupado/fuera_servicio)
- [ ] Tipos de espacios (general/discapacitado/eléctrico)

### Phase 5: Tickets

- [ ] Crear ticket al ingresar vehículo
- [ ] Búsqueda de tickets por placa
- [ ] Cálculo de tiempo y costo

### Phase 6: Pagos

- [ ] Registro de pagos
- [ ] Métodos de pago (efectivo/tarjeta)
- [ ] Cierre de ticket

### Phase 7: Reportes

- [ ] Reporte diario
- [ ] Reporte mensual

### Phase 8-12: Frontend

- [ ] Phase 8: Autenticación
- [ ] Phase 9: Dashboard
- [ ] Phase 10: Entrada/Salida
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
