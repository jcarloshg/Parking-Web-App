---
name: typescript
description: TypeScript best practices, type safety, and coding standards
Compatibility: opencode
---

# TypeScript Best Practices

## Core Principles

### 1. Never Use `any` - Always Define Types

The `any` type defeats the purpose of TypeScript. Always define explicit types.

```typescript
// BAD
function processData(data: any): any {
  return data;
}

// GOOD
interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'cajero' | 'supervisor';
}

function processData(data: User): User {
  return data;
}
```

### 2. Use Strict Type Annotations

```typescript
// BAD - implicit any
function add(a, b) {
  return a + b;
}

// GOOD - explicit types
function add(a: number, b: number): number {
  return a + b;
}
```

### 3. Prefer Interfaces over Types for Objects

Use interfaces for object shapes that may be extended:

```typescript
// Interface (preferred for objects)
interface ParkingSpace {
  id: number;
  number: string;
  type: 'general' | 'discapacitado' | 'eléctrico';
  status: 'disponible' | 'ocupado' | 'fuera_servicio';
}

// Type (use for unions, primitives)
type VehicleType = 'auto' | 'moto' | 'camioneta';
type Status = 'active' | 'inactive';
```

## Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| Types/Interfaces | PascalCase | `ParkingSpace`, `UserResponse` |
| Enums | PascalCase | `UserRole`, `PaymentStatus` |
| Constants | UPPER_SNAKE_CASE | `MAX_RETRY_COUNT`, `API_BASE_URL` |
| Functions | camelCase | `getUserById()`, `calculateTotal()` |
| Variables | camelCase | `userName`, `totalAmount` |
| Boolean variables | is/has/can prefix | `isActive`, `hasPermission`, `canEdit` |

## Type Definitions

### API Response Types

```typescript
// Single resource response
interface ApiResponse<T> {
  data: T;
  message?: string;
}

// Paginated response
interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

// Error response
interface ErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
}
```

### Domain Models

```typescript
// Entity model
interface ParkingSpace {
  id: number;
  number: string;
  type: 'general' | 'discapacitado' | 'eléctrico';
  status: 'disponible' | 'ocupado' | 'fuera_servicio';
  created_at: string;
  updated_at: string;
}

// DTO for creating entities
interface CreateParkingSpaceDTO {
  number: string;
  type: 'general' | 'discapacitado' | 'eléctrico';
}

// DTO for updating entities (all optional)
type UpdateParkingSpaceDTO = Partial<CreateParkingSpaceDTO> & {
  status?: 'disponible' | 'ocupado' | 'fuera_servicio';
};
```

### Form Types

```typescript
interface LoginForm {
  email: string;
  password: string;
}

interface TicketSearchForm {
  plate_number: string;
}
```

## Function Best Practices

### Explicit Return Types

```typescript
// GOOD - explicit return type
function getUserById(id: number): User | null {
  return users.find(u => u.id === id) ?? null;
}

// GOOD - async with explicit return
async function fetchParkingSpaces(): Promise<PaginatedResponse<ParkingSpace>> {
  const response = await api.get('/parking-spaces');
  return response.data;
}
```

### Optional Parameters and Default Values

```typescript
function createTicket(
  plateNumber: string,
  vehicleType: VehicleType,
  options?: {
    parkingSpaceId?: number;
    notes?: string;
  }
): Ticket {
  return {
    plate_number: plateNumber,
    vehicle_type: vehicleType,
    parking_space_id: options?.parkingSpaceId,
    // ...
  };
}

// With defaults
function paginate(items: unknown[], page = 1, limit = 15): unknown[] {
  const start = (page - 1) * limit;
  return items.slice(start, start + limit);
}
```

### Generic Functions

```typescript
// Generic fetch function
async function fetchApi<T>(url: string): Promise<T> {
  const response = await fetch(url);
  if (!response.ok) throw new Error('Failed');
  return response.json();
}

// Usage
const users = await fetchApi<User[]>('/api/users');
const parkingSpace = await fetchApi<ParkingSpace>('/api/parking-spaces/1');
```

## Error Handling

```typescript
// Custom error class
class ApiError extends Error {
  constructor(
    message: string,
    public statusCode: number,
    public code?: string
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

// Error handling
async function handleApiError(error: unknown): Promise<string> {
  if (error instanceof ApiError) {
    return error.message;
  }
  if (error instanceof Error) {
    return error.message;
  }
  return 'An unexpected error occurred';
}

// Type guard
function isApiError(error: unknown): error is ApiError {
  return error instanceof ApiError;
}
```

## Type Guards

```typescript
// Type guard for axios errors
function isAxiosError(error: unknown): error is AxiosError<ErrorResponse> {
  return axios.isAxiosError(error);
}

// Usage
try {
  await api.get('/users');
} catch (error) {
  if (isAxiosError(error)) {
    console.log(error.response?.data.message);
  }
}

// Type guard for object shape
function isParkingSpace(value: unknown): value is ParkingSpace {
  return (
    typeof value === 'object' &&
    value !== null &&
    'id' in value &&
    'number' in value &&
    'status' in value
  );
}
```

## Enums and Const Objects

```typescript
// Enum for fixed values
enum UserRole {
  ADMIN = 'admin',
  CASHIER = 'cajero',
  SUPERVISOR = 'supervisor',
}

// Const object (preferred when values won't change)
const USER_ROLES = {
  ADMIN: 'admin',
  CASHIER: 'cajero',
  SUPERVISOR': 'supervisor',
} as const;

type UserRoleType = typeof USER_ROLES[keyof typeof USER_ROLES];
```

## Utility Types

Use built-in utility types:

```typescript
// Make all properties optional
type PartialUser = Partial<User>;

// Make all properties required
type RequiredUser = Required<User>;

// Pick specific properties
type UserBasicInfo = Pick<User, 'id' | 'name' | 'email'>;

// Omit specific properties
type UserWithoutPassword = Omit<User, 'password'>;

// Make specific properties readonly
type ReadonlyUser = Readonly<User>;

// Nullable types
type Nullable<T> = T | null;
```

## React/Vue Specific Patterns

### Component Props

```typescript
// Vue 3 with Composition API
interface Props {
  user: User;
  isLoading?: boolean;
  onSelect?: (user: User) => void;
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
});
```

### Event Types

```typescript
// Vue 3 emit types
const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'submit', payload: FormData): void;
  (e: 'error', error: Error): void;
}>();
```

## Configuration

### tsconfig.json Best Practices

```json
{
  "compilerOptions": {
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "strictFunctionTypes": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true
  }
}
```

## Linting

Use ESLint with TypeScript support:

```bash
npm install -D @typescript-eslint/eslint-plugin @typescript-eslint/parser
```

## Common Patterns

### State Management

```typescript
// Auth store state
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}
```

### API Service Layer

```typescript
// API client with types
const api = {
  async get<T>(url: string): Promise<T> {
    const response = await axios.get<T>(url);
    return response.data;
  },
  
  async post<T, D = unknown>(url: string, data: D): Promise<T> {
    const response = await axios.post<T>(url, data);
    return response.data;
  },
  
  async put<T, D = unknown>(url: string, data: D): Promise<T> {
    const response = await axios.put<T>(url, data);
    return response.data;
  },
  
  async delete<T>(url: string): Promise<T> {
    const response = await axios.delete<T>(url);
    return response.data;
  },
};
```

### Form Validation

```typescript
interface ValidationRule {
  required?: boolean;
  min?: number;
  max?: number;
  pattern?: RegExp;
  message: string;
}

interface FormValidation {
  field: string;
  rules: ValidationRule[];
}

function validateForm(data: Record<string, string>, validations: FormValidation[]): Record<string, string> {
  const errors: Record<string, string> = {};
  
  for (const validation of validations) {
    const value = data[validation.field];
    
    for (const rule of validation.rules) {
      if (rule.required && !value) {
        errors[validation.field] = rule.message;
        break;
      }
      if (rule.min && value.length < rule.min) {
        errors[validation.field] = rule.message;
        break;
      }
    }
  }
  
  return errors;
}
```

## Quick Reference

| Pattern | Use Case |
|---------|----------|
| `interface` | Object shapes, API responses |
| `type` | Unions, primitives, aliases |
| `enum` | Fixed set of constants |
| `as` | Type assertion (use sparingly) |
| `is` | Type guards functions |
| `?` | Optional properties |
| `!` | Non-null assertion (avoid) |
| `unknown` | Safe any alternative |
| `never` | Functions that throw/error |
