---
name: testing
description: PHPUnit and Vitest testing commands, coverage targets, and test structure
compatibility: opencode
---
# Testing

## Backend Testing (PHPUnit)

### Commands
```bash
# Run all tests
./vendor/bin/phpunit

# Single test
./vendor/bin/phpunit --filter=ParkingSpaceTest
./vendor/bin/phpunit --filter=TicketServiceTest

# Test suites
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# Coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Coverage Targets
- Models & Services: 70%
- Controllers/API: 80%
- Critical Components: 60%

### Test Naming
```php
public function test_calculates_fee_with_tolerance()
public function test_returns_404_for_nonexistent_ticket()
public function test_prevents_double_payment()
```

### Test Structure
```
tests/
├── Unit/
│   ├── Services/
│   │   └── PaymentServiceTest.php
│   └── Models/
│       └── TicketTest.php
├── Feature/
│   ├── API/
│   │   └── TicketApiTest.php
│   └── Controllers/
│       └── ParkingSpaceControllerTest.php
```

## Frontend Testing (Vitest)

### Commands
```bash
# Run all tests
npm run test

# Single test file
npm run test -- src/stores/auth.spec.ts
npm run test:unit -- auth.spec.ts

# Watch mode
npm run test:watch

# Coverage
npm run test:coverage
```

### Test File Organization
```
frontend/tests/
├── stores/
│   └── auth.spec.ts
├── composables/
│   └── useParkingSpaces.spec.ts
└── components/
    └── ParkingSpaceCard.spec.ts
```

## Integration Points
- Test fee calculation logic thoroughly
- Test role-based access control
- Test payment flow end-to-end
- Test parking space availability logic
