---
name: api-development
description: REST API endpoints, request/response formats, authentication, and examples
compatibility: opencode
---
# API Development

## Response Format

### Single Resource
```json
{ "id": 1, "plate_number": "ABC-1234", "status": "activo" }
```

### Collection with Pagination
```json
{
  "data": [...],
  "meta": { "current_page": 1, "per_page": 15, "total": 50 }
}
```

### Error
```json
{ "message": "Validation failed", "errors": { "plate_number": ["Required"] } }
```

## Key Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout (requires Bearer token)

### Parking Spaces
- `GET /api/parking-spaces` - List spaces (?status=disponible, ?type=general)
- `GET /api/parking-spaces/available-count` - Get available count

### Tickets
- `POST /api/tickets` - Create ticket (entry)
- `GET /api/tickets/active` - Active tickets
- `GET /api/tickets/search?plate_number=ABC` - Search by plate
- `POST /api/tickets/{id}/calculate` - Calculate fee
- `POST /api/tickets/{id}/checkout` - Process payment & exit

### Reports
- `GET /api/reports/daily?date=YYYY-MM-DD` - Daily report
- `GET /api/reports/monthly?year=2024&month=1` - Monthly report

### Users (Admin only)
- `GET /api/users` - List users
- `POST /api/users` - Create user

## Request/Response Examples

### Login
```json
// Request
{ "email": "admin@parking.com", "password": "password" }

// Response 200
{ "token": "eyJ0eXAiOiJKV1Q...", "user": { "id": 1, "name": "Admin", "email": "admin@parking.com", "role": "admin" } }
```

### Create Ticket (Entry)
```json
// Headers: Authorization: Bearer {token}
// Request
{ "plate_number": "ABC-1234", "vehicle_type": "auto", "parking_space_id": 1 }

// Response 201
{ "id": 100, "plate_number": "ABC-1234", "vehicle_type": "auto", "entry_time": "2024-01-15T10:00:00Z", "status": "activo" }
```

### Calculate Fee
```json
// Response 200
{
  "ticket_id": 100,
  "plate_number": "ABC-1234",
  "duration_minutes": 270,
  "hours_charged": 5,
  "vehicle_type": "auto",
  "tariff": { "price_per_hour": 20.0, "price_per_day": 150.0 },
  "subtotal": 100.0,
  "discount_minutes": 10,
  "total": 100.0
}
```

### Checkout
```json
// Request
{ "payment_method": "efectivo" }

// Response 201
{
  "ticket_id": 100,
  "payment": { "id": 50, "total": 100.00, "payment_method": "efectivo", "paid_at": "2024-01-15T14:30:00Z" },
  "message": "Vehicle exited successfully"
}
```

### Daily Report
```json
// Response 200
{
  "date": "2024-01-15",
  "total_income": 2500.00,
  "tickets_attended": 45,
  "average_per_ticket": 55.56,
  "vehicles_by_type": { "auto": 30, "moto": 10, "camioneta": 5 }
}
```
