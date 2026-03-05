---
name: business-rules
description: Parking system tariffs, fee calculation logic, role permissions, and domain enums
compatibility: opencode
---
# Business Rules

## Tariffs

| Vehicle Type | Price per Hour | Price per Day |
|--------------|----------------|---------------|
| Auto         | $20.00         | $150.00       |
| Moto         | $10.00         | $80.00        |
| Camioneta    | $30.00         | $200.00       |

## Fee Calculation Logic

```
1. If time <= 10 minutes: CHARGE = $0 (tolerance)
2. If time > 10 minutes AND < 24 hours:
   - Round hours UP to nearest whole hour
   - CHARGE = hours × price_per_hour
3. If time >= 24 hours:
   - CHARGE = price_per_day + remaining hours calculation
```

## Role Permissions

| Action            | Admin | Cashier | Supervisor |
|-------------------|-------|---------|------------|
| Register Entry    | ✓     | ✓       | ✓          |
| Register Exit     | ✓     | ✓       | ✓          |
| View Reports      | ✓     | ✗       | ✓          |
| Manage Users      | ✓     | ✗       | ✗          |
| Manage Tariffs    | ✓     | ✗       | ✗          |
| Manage Spaces     | ✓     | ✗       | ✗          |

## Vehicle Types
- `auto` - Car
- `moto` - Motorcycle
- `camioneta` - Van/Truck

## Payment Methods
- `efectivo` - Cash
- `tarjeta` - Card

## Parking Space Types
- `general` - General
- `discapacitado` - Disabled
- `eléctrico` - Electric

## Parking Space Status
- `disponible` - Available
- `ocupado` - Occupied
- `fuera_servicio` - Out of service

## Ticket Status
- `activo` - Active (vehicle in parking)
- `finalizado` - Completed (vehicle exited)

## User Roles
- `admin` - Full access
- `cajero` - Cashier (entry/exit only)
- `supervisor` - Reports + entry/exit
