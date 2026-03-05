# Phase 2: Base de Datos y Modelos

## Objetivo
Crear el esquema de base de datos y modelos Eloquent.

## Tareas

### 2.1 Migraciones

#### users
- [ ] id (bigInteger, unsigned, primary)
- [ ] name (string, 255)
- [ ] email (string, 255, unique)
- [ ] password (string, 255)
- [ ] role (enum: admin, cajero, supervisor)
- [ ] remember_token (string, 100, nullable)
- [ ] timestamps

#### parking_spaces
- [ ] id (bigInteger, unsigned, primary)
- [ ] number (string, 10, unique)
- [ ] type (enum: general, discapacitado, eléctrico)
- [ ] status (enum: disponible, ocupado, fuera_servicio)
- [ ] timestamps

#### tickets
- [ ] id (bigInteger, unsigned, primary)
- [ ] plate_number (string, 20)
- [ ] vehicle_type (enum: auto, moto, camioneta)
- [ ] entry_time (datetime)
- [ ] exit_time (datetime, nullable)
- [ ] parking_space_id (bigInteger, unsigned, foreign)
- [ ] status (enum: activo, finalizado)
- [ ] timestamps

#### payments
- [ ] id (bigInteger, unsigned, primary)
- [ ] ticket_id (bigInteger, unsigned, foreign)
- [ ] total (decimal, 10,2)
- [ ] payment_method (enum: efectivo, tarjeta)
- [ ] paid_at (datetime)
- [ ] timestamps

### 2.2 Modelos Eloquent
- [ ] User.php (implement JWTSubject)
- [ ] ParkingSpace.php
- [ ] Ticket.php
- [ ] Payment.php

### 2.3 Relaciones
- [ ] User → hasMany(Ticket)
- [ ] ParkingSpace → hasMany(Ticket)
- [ ] Ticket → belongsTo(ParkingSpace)
- [ ] Ticket → hasOne(Payment)
- [ ] Payment → belongsTo(Ticket)

### 2.4 Seeders
- [ ] UserSeeder (crear admin inicial)
- [ ] ParkingSpaceSeeder (cajones de ejemplo)
- [ ] DatabaseSeeder

## Entregables
- Migraciones ejecutadas
- Modelos con relaciones
- Datos de prueba (seeders)
