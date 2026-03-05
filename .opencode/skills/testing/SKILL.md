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

# Run tests in parallel (requires parallel extension)
./vendor/bin/phpunit --parallel

# Single test
./vendor/bin/phpunit --filter=ParkingSpaceTest
./vendor/bin/phpunit --filter=TicketServiceTest

# Test suites
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# With detailed output
./vendor/bin/phpunit --testdox

# Coverage
./vendor/bin/phpunit --coverage-html coverage
./vendor/bin/phpunit --coverage-text

# Run tests matching pattern
./vendor/bin/phpunit --filter="test_it_can.*"
```

### Configuration (phpunit.xml)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <coverage>
        <report>
            <html outputDirectory="coverage"/>
            <text outputFile="php://stdout"/>
        </report>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="parking_test"/>
    </php>
</phpunit>
```

### Coverage Targets

- Models & Services: 80%
- Controllers/API: 85%
- Critical Components (Payment, Tariffs): 90%
- Overall Minimum: 70%

### Test Naming Conventions

```php
// Describe behavior: test_{subject}_{action}_{expected_result}
public function test_calculates_parking_fee_with_15_minute_tolerance()
public function test_returns_404_when_ticket_not_found()
public function test_prevents_double_payment_for_same_ticket()
public function test_user_can_only_access_own_parking_spaces()

// Use descriptive names for complex scenarios
public function test_calculates_correct_fee_for_oversized_vehicle_in_premium_zone()
```

### Test Structure

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── PaymentServiceTest.php
│   │   ├── TariffServiceTest.php
│   │   └── FeeCalculatorTest.php
│   ├── Models/
│   │   ├── TicketTest.php
│   │   └── ParkingSpaceTest.php
│   └── Helpers/
│       └── FeeCalculationHelperTest.php
├── Feature/
│   ├── API/
│   │   ├── TicketApiTest.php
│   │   ├── ParkingSpaceApiTest.php
│   │   └── AuthApiTest.php
│   └── Controllers/
│       ├── ParkingSpaceControllerTest.php
│       └── ReportControllerTest.php
└── Traits/
    └── RefreshDatabaseWithSeeding.php
```

### Best Practices

#### 1. Use Test Databases

```php
protected function setUp(): void
{
    parent::setUp();
    $this->artisan('migrate:fresh --seed');
}

// Or use RefreshDatabase
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;
}
```

#### 2. Factory Usage

```php
public function test_creates_ticket_with_correct_data(): void
{
    $vehicle = Vehicle::factory()->create();
    $space = ParkingSpace::factory()->create();

    $ticket = Ticket::factory()->create([
        'vehicle_id' => $vehicle->id,
        'parking_space_id' => $space->id,
    ]);

    $this->assertDatabaseHas('tickets', [
        'vehicle_id' => $vehicle->id,
        'parking_space_id' => $space->id,
    ]);
}
```

#### 3. Mocking External Services

```php
public function test_processes_payment_via_gateway(): void
{
    $paymentGateway = Mockery::mock(PaymentGateway::class);
    $paymentGateway->shouldReceive('charge')
        ->once()
        ->with(1000, 'USD')
        ->andReturn(new PaymentResult(['transaction_id' => 'txn_123']));

    $service = new PaymentService($paymentGateway);
    $result = $service->processPayment(1000, 'USD');

    $this->assertEquals('txn_123', $result->transaction_id);
}
```

#### 4. Testing API Endpoints

```php
public function test_user_can_view_their_tickets(): void
{
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->create();

    $response = $this->actingAs($user, 'api')
        ->getJson('/api/tickets');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ticket->id);
}

public function test_unauthenticated_user_cannot_access_tickets(): void
{
    $response = $this->getJson('/api/tickets');

    $response->assertStatus(401);
}
```

#### 5. Database Transactions for Faster Tests

```php
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class TicketTest extends TestCase
{
    use LazilyRefreshDatabase; // Refreshes once per test class
}
```

---

## Frontend Testing (Vitest)

### Commands

```bash
# Run all tests
npm run test

# Single test file
npm run test src/stores/auth.spec.ts

# Watch mode
npm run test:watch

# Coverage
npm run test:coverage

# UI mode
npm run test:ui

# Run tests matching pattern
npm run test -- --grep="auth"
```

### Configuration (vite.config.ts)

```typescript
import { defineConfig } from "vitest/config";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: "jsdom",
    setupFiles: ["./tests/setup.ts"],
    include: ["tests/**/*.{test,spec}.{js,ts}"],
    coverage: {
      provider: "v8",
      reporter: ["text", "json", "html"],
      exclude: ["tests/**", "node_modules/**"],
    },
  },
});
```

### Test File Organization

```
frontend/
├── tests/
│   ├── setup.ts
│   ├── stores/
│   │   ├── auth.spec.ts
│   │   └── parking.spec.ts
│   ├── composables/
│   │   ├── useParking.spec.ts
│   │   └── usePayment.spec.ts
│   ├── components/
│   │   ├── ParkingSpaceCard.spec.ts
│   │   └── TicketModal.spec.ts
│   └── utils/
│       └── feeCalculator.spec.ts
```

### Best Practices

#### 1. Component Testing

```typescript
import { mount } from "@vue/test-utils";
import { describe, it, expect, beforeEach } from "vitest";
import ParkingSpaceCard from "./ParkingSpaceCard.vue";

describe("ParkingSpaceCard", () => {
  let wrapper: ReturnType<typeof mount>;

  beforeEach(() => {
    wrapper = mount(ParkingSpaceCard, {
      props: {
        space: {
          id: 1,
          name: "A1",
          type: "standard",
          isAvailable: true,
        },
      },
    });
  });

  it("renders parking space information", () => {
    expect(wrapper.text()).toContain("A1");
    expect(wrapper.text()).toContain("standard");
  });

  it("emits select event when clicked", () => {
    wrapper.find("button").trigger("click");
    expect(wrapper.emitted("select")).toBeTruthy();
  });
});
```

#### 2. Pinia Store Testing

```typescript
import { createPinia, setActivePinia } from "pinia";
import { describe, it, expect, beforeEach } from "vitest";
import { useAuthStore } from "@/stores/auth";

describe("auth store", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("login sets user and token", async () => {
    const store = useAuthStore();

    // Mock API call
    vi.mock("@/api/auth", () => ({
      login: vi.fn().mockResolvedValue({
        user: { id: 1, name: "Test" },
        token: "abc123",
      }),
    }));

    await store.login("test@example.com", "password");

    expect(store.user).toEqual({ id: 1, name: "Test" });
    expect(store.token).toBe("abc123");
  });

  it("logout clears user and token", () => {
    const store = useAuthStore();
    store.user = { id: 1, name: "Test" };
    store.token = "abc123";

    store.logout();

    expect(store.user).toBeNull();
    expect(store.token).toBeNull();
  });
});
```

#### 3. Composable Testing

```typescript
import { useParking } from "./useParking";
import { describe, it, expect, vi } from "vitest";

vi.mock("@/api/parking", () => ({
  getParkingSpaces: vi.fn().mockResolvedValue([{ id: 1, name: "A1" }]),
}));

describe("useParking", () => {
  it("fetches parking spaces", async () => {
    const { spaces, fetchSpaces } = useParking();

    await fetchSpaces();

    expect(spaces.value).toHaveLength(1);
    expect(spaces.value[0].name).toBe("A1");
  });
});
```

#### 4. API Mocking

```typescript
import { setupServer } from "msw/node";
import { http, HttpResponse } from "msw";

const server = setupServer(
  http.get("/api/parking-spaces", () => {
    return HttpResponse.json([{ id: 1, name: "A1", type: "standard" }]);
  }),
);

beforeAll(() => server.listen());
afterEach(() => server.resetHandlers());
afterAll(() => server.close());
```

#### 5. Testing with Vue Router

```typescript
import { mount } from "@vue/test-utils";
import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "./Dashboard.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: "/", name: "dashboard", component: Dashboard }],
});

it("navigates to parking spaces", async () => {
  const wrapper = mount(Dashboard, {
    global: {
      plugins: [router],
    },
  });

  await router.isReady();
  wrapper.find('[data-testid="parking-link"]').trigger("click");

  expect(router.currentRoute.value.name).toBe("parking-spaces");
});
```

---

## Integration Points

### Critical Test Coverage Areas

- Fee calculation logic (all tariff types)
- Role-based access control (RBAC)
- Payment processing flow
- Parking space availability
- Ticket creation and expiration
- User authentication flow

### End-to-End Testing

- Full payment flow: login → select space → create ticket → payment → exit
- Admin workflow: login → manage parking spaces → view reports
- Multi-role testing: verify each role has correct permissions

### Test Data Management

- Use factories for consistent test data
- Never hardcode IDs in tests
- Clean up test data after each test
- Use transactions or database refresh for isolation
