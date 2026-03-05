---
name: vue-frontend
description: Vue.js 3 frontend development with Composition API, TypeScript, and Pinia
Compatibility: opencode
---

# Vue.js Frontend Development

## Project Context

- **Stack**: Vue.js 3 (Composition API) + TypeScript + Pinia
- **Build Tool**: Vite
- **Container**: Docker with Nginx

## Naming Conventions

| Element      | Convention           | Example                         |
| ------------ | -------------------- | ------------------------------- |
| Components   | PascalCase           | `ParkingSpaceCard.vue`          |
| Composables  | use\* prefix         | `useParkingSpaces.ts`           |
| Stores       | camelCase            | `useAuthStore.ts`               |
| Types        | PascalCase           | `ParkingSpace`                  |
| Props/Events | camelCase/kebab-case | `isActive`, `'vehicle-entered'` |

## TypeScript Standards

### Always Define Types - Avoid `any`

```typescript
interface Ticket {
  id: number;
  plate_number: string;
  vehicle_type: "auto" | "moto" | "camioneta";
  status: "activo" | "finalizado";
  entry_time: string;
  exit_time: string | null;
}
```

### Vue Component Script Setup

```vue
<script setup lang="ts">
interface Props {
  parkingSpace: ParkingSpace;
  isEditable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  isEditable: false,
});

const emit = defineEmits<{
  (e: "select", id: number): void;
  (e: "error", message: string): void;
}>();
</script>
```

### Error Handling

```typescript
try {
  const { data } = await axios.get<Ticket[]>("/api/tickets");
} catch (error) {
  if (axios.isAxiosError(error)) {
    notify.error(error.response?.data?.message || "Failed");
  }
}
```

## Pinia Store Pattern

```typescript
export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);

  const isAuthenticated = computed(() => !!token.value);

  async function login(credentials: LoginCredentials) {
    const response = await api.auth.login(credentials);
    token.value = response.token;
    user.value = response.user;
  }

  return { user, token, isAuthenticated, login };
});
```

## Clean Architecture Project Structure

```
src/
├── application/           # Application layer (use cases)
│   ├── components/       # Feature-specific components
│   ├── composables/      # Application composables
│   ├── pages/            # Page components (views)
│   └── stores/           # Pinia stores (state management)
│
├── infra/                # Infrastructure layer
│   ├── api/              # API client & endpoints
│   │   ├── apiClient.ts  # Axios instance
│   │   ├── auth.ts      # Auth endpoints
│   │   ├── parking.ts   # Parking endpoints
│   │   └── tickets.ts   # Ticket endpoints
│   ├── router/           # Vue Router configuration
│   └── services/         # External services (notifications, storage)
│
├── models/               # Domain layer (entities & types)
│   ├── entities/         # Core business entities
│   │   ├── User.ts
│   │   ├── ParkingSpace.ts
│   │   ├── Ticket.ts
│   │   └── Payment.ts
│   ├── types/            # TypeScript interfaces & types
│   │   ├── api.ts       # API response types
│   │   ├── forms.ts     # Form types
│   │   └── index.ts     # Re-exports
│   └── constants/        # App constants (enums, config)
│
├── shared/               # Shared utilities
│   ├── components/       # Reusable UI components
│   ├── composables/      # Shared composables
│   ├── utils/           # Utility functions
│   └── constants/       # Shared constants
│
├── assets/              # Static assets (images, fonts, styles)
├── App.vue
└── main.ts
```

### Layer Responsibilities

| Layer          | Responsibility                        |
| -------------- | ------------------------------------- |
| `application/` | UI components, pages, feature logic   |
| `infra/`       | API calls, routing, external services |
| `models/`      | Types, entities, business constants   |
| `shared/`      | Reusable components, utilities        |

## Key Commands

```bash
# Development
npm run dev

# Testing (see testing/SKILL.md for detailed test commands)
npm run test
npm run test -- src/stores/auth.spec.ts
npm run test:watch
npm run test:coverage

# Linting & Type Checking
npm run lint
npm run typecheck

# Build
npm run build

# Docker
docker build -t parking-frontend .
docker run -p 5173:80 parking-frontend
```

---

## Testing

> **See also**: [.opencode/skills/testing/SKILL.md](./testing/SKILL.md) for comprehensive testing documentation

### Test Setup

First, ensure vitest and testing dependencies are installed:

```bash
npm install -D vitest @vue/test-utils jsdom
```

### Test Configuration (vite.config.ts)

```typescript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.ts'],
    include: ['tests/**/*.{test,spec}.{js,ts}'],
  },
})
```

### Test File Structure

```
frontend/
├── tests/
│   ├── setup.ts                 # Test setup and mocks
│   ├── stores/
│   │   ├── auth.spec.ts         # Auth store tests
│   │   └── parking.spec.ts      # Parking store tests
│   ├── composables/
│   │   ├── useParking.spec.ts   # useParking composable tests
│   │   └── usePayment.spec.ts   # usePayment composable tests
│   ├── components/
│   │   ├── Dashboard.spec.ts    # Dashboard component tests
│   │   ├── ParkingGrid.spec.ts # Parking grid tests
│   │   ├── StatsCards.spec.ts   # Stats cards tests
│   │   └── Sidebar.spec.ts     # Sidebar navigation tests
│   └── views/
│       ├── Login.spec.ts        # Login view tests
│       ├── Entry.spec.ts        # Entry view tests
│       └── Exit.spec.ts         # Exit view tests
```

### Component Testing Example

```typescript
import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import ParkingGrid from '@/views/components/ParkingGrid.vue'

describe('ParkingGrid', () => {
  const mockSpaces = [
    { id: 1, number: 'A1', status: 'disponible', type: 'general' },
    { id: 2, number: 'A2', status: 'ocupado', type: 'general' },
  ]

  it('renders all parking spaces', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: mockSpaces },
    })

    expect(wrapper.findAll('.parking-space')).toHaveLength(2)
  })

  it('shows green color for available spaces', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', status: 'disponible', type: 'general' }] },
    })

    expect(wrapper.find('.parking-space').classes()).toContain('status-available')
  })

  it('shows red color for occupied spaces', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', status: 'ocupado', type: 'general' }] },
    })

    expect(wrapper.find('.parking-space').classes()).toContain('status-occupied')
  })

  it('displays space number and type', () => {
    const wrapper = mount(ParkingGrid, {
      props: { spaces: [{ id: 1, number: 'A1', type: 'eléctrico', status: 'disponible' }] },
    })

    expect(wrapper.text()).toContain('A1')
    expect(wrapper.text()).toContain('Eléctrico')
  })
})
```

### Store Testing Example

```typescript
import { createPinia, setActivePinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/api/auth', () => ({
  authApi: {
    login: vi.fn().mockResolvedValue({
      token: 'test-token',
      user: { id: 1, name: 'Test User', role: 'admin', email: 'test@test.com' },
    }),
    logout: vi.fn().mockResolvedValue(undefined),
  },
}))

describe('auth store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
  })

  it('login sets user and token', async () => {
    const store = useAuthStore()
    
    await store.login({ email: 'test@test.com', password: 'password' })

    expect(store.token).toBe('test-token')
    expect(store.user).toEqual(expect.objectContaining({ id: 1, name: 'Test User' }))
  })

  it('logout clears user and token', async () => {
    const store = useAuthStore()
    store.token = 'test-token'
    store.user = { id: 1, name: 'Test', role: 'admin', email: 'test@test.com' }

    await store.logout()

    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
  })

  it('isAdmin returns true for admin role', () => {
    const store = useAuthStore()
    store.user = { id: 1, name: 'Admin', role: 'admin', email: 'admin@test.com' }

    expect(store.isAdmin).toBe(true)
  })
})
```

### API Mocking Example

```typescript
import { vi } from 'vitest'

vi.mock('@/api/parking', () => ({
  parkingApi: {
    getAll: vi.fn().mockResolvedValue([
      { id: 1, number: 'A1', status: 'disponible', type: 'general' },
    ]),
    getAvailable: vi.fn().mockResolvedValue([
      { id: 1, number: 'A1', status: 'disponible', type: 'general' },
    ]),
  },
  reportsApi: {
    getSummary: vi.fn().mockResolvedValue({
      cajones_disponibles: 10,
      tickets_activos: 5,
      ingresos_dia: 1500,
    }),
  },
}))
```

### Running Tests

```bash
# Run all tests
npm run test

# Run single test file
npm run test src/views/Dashboard.spec.ts

# Watch mode
npm run test:watch

# With coverage
npm run test:coverage
```

### Test Coverage Targets

- Components: 80%
- Stores/Composables: 85%
- Critical Features (Payment, Auth): 90%

---

## Docker Configuration

### Dockerfile

```dockerfile
# Build stage
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Build application
RUN npm run build

# Production stage
FROM nginx:alpine AS production

# Copy custom nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy built assets from builder
COPY --from=builder /app/dist /usr/share/nginx/html

# Expose port
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
```

### nginx.conf

```nginx
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json application/javascript;

    # Vue Router history mode
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### docker-compose.yml

```yaml
services:
  frontend:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "5173:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
    environment:
      - NODE_ENV=production
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "wget", "-q", "--spider", "http://localhost:80"]
      interval: 30s
      timeout: 10s
      retries: 3
    networks:
      - parking-network

networks:
  parking-network:
    driver: bridge
```

### Development Dockerfile

```dockerfile
FROM node:20-alpine

WORKDIR /app

# Install dependencies
COPY package*.json ./
RUN npm install

# Copy source code
COPY . .

# Expose Vite dev server
EXPOSE 5173

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

### Development docker-compose.yml

```yaml
services:
  frontend:
    build:
      context: .
      dockerfile: Dockerfile.dev
    ports:
      - "5173:5173"
    volumes:
      - .:/app
      - /app/node_modules
    environment:
      - VITE_API_URL=http://localhost:8000
      - VITE_APP_NAME=Parking Web App
    restart: unless-stopped
    networks:
      - parking-network

networks:
  parking-network:
    external: true
```

### Docker Best Practices

1. **Multi-stage builds**: Use builder + production stages to reduce image size
2. **Layer caching**: Copy `package*.json` first, then source code
3. **Non-root user**: Run as non-root user in production
4. **Health checks**: Add health checks for container orchestration
5. **Nginx config**: Use Vue Router history mode and proper caching
6. **Environment variables**: Use VITE\_\* prefix for client-side env vars
7. **Expose single port**: Only expose the port the app listens on
