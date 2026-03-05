# Phase 13: Testing y Entregables

## Objetivo
Completar testing y preparar entregables finales.

## Testing Best Practices

### Comandos de Testing

#### Backend
```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Tests con cobertura
./vendor/bin/phpunit --coverage-html coverage

# Tests específicos
./vendor/bin/phpunit --filter=TicketApiTest
./vendor/bin/phpunit --filter=PaymentServiceTest

# Modo verbose
./vendor/bin/phpunit --testdox
```

#### Frontend
```bash
# Todos los tests
npm run test

# Coverage
npm run test:coverage

# Watch mode
npm run test:watch

# UI mode
npm run test:ui
```

### Cobertura Objetivo

| Componente | Target |
|------------|--------|
| Models | 80% |
| Services | 80% |
| Controllers/API | 85% |
| FeeCalculator | 95% |
| Frontend Components | 70% |

### Estructura de Tests Completa

```
backend/tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── ParkingSpaceTest.php
│   │   ├── TicketTest.php
│   │   └── PaymentTest.php
│   └── Services/
│       ├── AuthServiceTest.php
│       ├── ParkingSpaceServiceTest.php
│       ├── TicketServiceTest.php
│       ├── PaymentServiceTest.php
│       ├── FeeCalculatorTest.php
│       └── ReportServiceTest.php
└── Feature/
    └── API/
        ├── AuthApiTest.php
        ├── ParkingSpaceApiTest.php
        ├── TicketApiTest.php
        ├── PaymentApiTest.php
        └── ReportApiTest.php

frontend/tests/
├── setup.ts
├── stores/
│   ├── auth.spec.ts
│   └── parking.spec.ts
├── composables/
│   ├── useAuth.spec.ts
│   ├── useParking.spec.ts
│   └── usePayment.spec.ts
├── pages/
│   ├── LoginPage.spec.ts
│   ├── Dashboard.spec.ts
│   ├── EntryPage.spec.ts
│   ├── ExitPage.spec.ts
│   ├── ReportsPage.spec.ts
│   └── AdminUsers.spec.ts
└── components/
    ├── ParkingGrid.spec.ts
    ├── StatsCards.spec.ts
    └── PaymentForm.spec.ts
```

## Tareas

### 13.1 Backend Testing (PHPUnit)
- [ ] Tests de autenticación JWT
- [ ] Tests de tickets (CRUD, entrada)
- [ ] Tests de payments (cálculo tarifas)
- [ ] Tests de parking spaces
- [ ] Tests de reportes
- [ ] Tests de políticas/permisos

### 13.2 Frontend Testing (Vitest)
- [ ] Tests de componentes auth
- [ ] Tests de store Pinia
- [ ] Tests de composables

### 13.3 Coverage
- [ ] Backend: 70% mínimo coverage
- [ ] Frontend: componentes críticos

### 13.4 Documentación README
- [ ] Instalación backend
- [ ] Instalación frontend
- [ ] Configuración base de datos
- [ ] Configuración JWT
- [ ] Endpoints API
- [ ] Screenshots (opcional)

### 13.5 Postman Collection
- [ ] Colección con todos los endpoints
- [ ] Variables: {{base_url}}, {{token}}
- [ ] Auth: login primero
- [ ] Carpetas por recurso

### 13.6 SQL Script
- [ ] Migrations
- [ ] Seeders
- [ ] Datos iniciales

### 13.7 Revisión Final
- [ ] Verificar todos los endpoints
- [ ] Verificar frontend completo
- [ ] Verificar permisos
- [ ] Verificar responsive design

## Entregables
1. Backend Laravel con JWT
2. Frontend Vue.js
3. Script SQL (migrations + seeders)
4. README con instrucciones
5. Postman collection
6. Tests funcionando

## Checklist Final
- [ ] Login funciona
- [ ] Entrada/Salida funciona
- [ ] Tarifas correctas
- [ ] Reportes generan
- [ ] Admin gestiona usuarios
- [ ] Admin gestiona cajones
- [ ] Paginación funciona
- [ ] Validaciones trabajan
- [ ] Tests pasan
