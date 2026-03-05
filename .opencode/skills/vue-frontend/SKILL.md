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

# Testing
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
