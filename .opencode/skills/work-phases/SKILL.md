---
name: work-phases
description: Parking web app structure with frontend, backend, and database directories, each with Docker best practices
compatibility: opencode
---

# Work Phases - Project Structure

## Project Context

- **Stack**: Laravel 10+ (Backend) + Vue.js 3 (Frontend) + MySQL (Database)
- **Auth**: JWT (tymon/jwt-auth)
- **Structure**: Three main directories at root level

## Directory Structure

```
parking-web-app/
├── backend/          # Laravel API
├── frontend/         # Vue.js SPA
├── database/         # MySQL + migrations
├── docker-compose.yml
├── .env
└── .env.example
```

---

## Phase 1: Backend (Laravel)

### Dockerfile Best Practices

```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (cache optimization)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Generate application key
RUN php artisan key:generate

# Generate JWT secret
RUN php artisan jwt:secret --force

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8000 || exit 1

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### Docker Entrypoint Script

```bash
#!/bin/sh
set -e

# Wait for database
echo "Waiting for database..."
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    sleep 2
done

# Run migrations
php artisan migrate --force

# Seed database (development only)
if [ "$APP_ENV" = "local" ]; then
    php artisan db:seed --force
fi

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
```

---

## Phase 2: Frontend (Vue.js)

### Dockerfile Best Practices

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

# Build for production
RUN npm run build

# Production stage
FROM nginx:alpine AS production

# Copy built files
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://localhost || exit 1

CMD ["nginx", "-g", "daemon off;"]
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name localhost;
    root /usr/share/nginx/html;
    index index.html;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

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

### Development Dockerfile

```dockerfile
FROM node:20-alpine

WORKDIR /app

# Install pnpm
RUN npm install -g pnpm

# Copy package files
COPY package*.json pnpm-lock.yaml* ./

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy source code
COPY . .

# Expose dev server
EXPOSE 5173

CMD ["pnpm", "dev", "--host"]
```

---

## Phase 3: Database (MySQL)

### Dockerfile Best Practices

```dockerfile
FROM mysql:8.0

# Environment variables
ENV MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root_password}
ENV MYSQL_DATABASE=${MYSQL_DATABASE:-parking_db}
ENV MYSQL_USER=${MYSQL_USER:-parking_user}
ENV MYSQL_PASSWORD=${MYSQL_PASSWORD:-parking_password}

# Custom configuration
COPY my.cnf /etc/mysql/conf.d/my.cnf

# Initialize database
COPY init/ /docker-entrypoint-initdb.d/

# Expose port
EXPOSE 3306

# Health check
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
    CMD mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} || exit 1
```

### MySQL Configuration (my.cnf)

```ini
[mysqld]
# Performance
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 200
query_cache_size = 0
query_cache_type = 0

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Character set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci

# Timezone
default-time-zone = '+00:00'

[client]
default-character-set = utf8mb4
```

### Init Scripts

```sql
-- init/01-create-user.sql
CREATE USER IF NOT EXISTS 'parking_user'@'%' IDENTIFIED BY 'parking_password';
GRANT ALL PRIVILEGES ON parking_db.* TO 'parking_user'@'%';
FLUSH PRIVILEGES;
```

---

## Docker Compose

### Best Practices docker-compose.yml

```yaml
version: '3.8'

services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: parking_backend
    restart: unless-stopped
    depends_on:
      database:
        condition: service_healthy
    environment:
      APP_ENV: ${APP_ENV:-production}
      APP_DEBUG: ${APP_DEBUG:-false}
      APP_URL: ${APP_URL:-http://localhost:8000}
      DB_CONNECTION: mysql
      DB_HOST: database
      DB_PORT: 3306
      DB_DATABASE: ${DB_DATABASE:-parking_db}
      DB_USERNAME: ${DB_USERNAME:-parking_user}
      DB_PASSWORD: ${DB_PASSWORD:-parking_password}
      JWT_SECRET: ${JWT_SECRET:-}
      JWT_TTL: 60
    ports:
      - "${APP_PORT:-8000}:8000"
    volumes:
      - backend_storage:/var/www/html/storage
    networks:
      - parking_network
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
      target: production
    container_name: parking_frontend
    restart: unless-stopped
    depends_on:
      - backend
    ports:
      - "${FRONTEND_PORT:-80}:80"
    networks:
      - parking_network
    healthcheck:
      test: ["CMD", "wget", "--no-verbose", "--tries=1", "--spider", "http://localhost"]
      interval: 30s
      timeout: 10s
      retries: 3

  database:
    build:
      context: ./database
      dockerfile: Dockerfile
    container_name: parking_database
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root_password}
      MYSQL_DATABASE: ${DB_DATABASE:-parking_db}
      MYSQL_USER: ${DB_USERNAME:-parking_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-parking_password}
    ports:
      - "${DB_PORT:-3306}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - parking_network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s

networks:
  parking_network:
    driver: bridge

volumes:
  backend_storage:
  mysql_data:
```

---

## Environment Variables

### .env Example

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=parking_db
DB_USERNAME=parking_user
DB_PASSWORD=parking_password
DB_ROOT_PASSWORD=root_password

# JWT
JWT_SECRET=your-jwt-secret-here
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Frontend
FRONTEND_PORT=80
VITE_API_URL=http://localhost:8000/api
```

---

## Docker Commands

```bash
# Build all services
docker compose build --no-cache

# Start all services
docker compose up -d

# View logs
docker compose logs -f

# Run migrations
docker compose exec backend php artisan migrate

# Access backend shell
docker compose exec backend sh

# Access database
docker compose exec database mysql -u parking_user -p parking_db

# Stop all services
docker compose down

# Remove volumes
docker compose down -v

# Rebuild single service
docker compose build backend
docker compose up -d backend
```

---

## Security Best Practices

1. **Never commit secrets** - Use `.env.example` for template
2. **Use specific versions** - Pin Docker images to versions
3. **Multi-stage builds** - Reduce image size
4. **Non-root users** - Run containers as non-root when possible
5. **Health checks** - Always include health checks
6. **Network isolation** - Use custom networks
7. **Resource limits** - Set memory/CPU limits
8. **Logging** - Configure proper log drivers

---

## Production Considerations

- Use `--no-dev` for composer in production
- Enable OPcache for PHP
- Use CDN for static assets
- Configure SSL/TLS
- Set up monitoring
- Use Redis for caching and sessions
- Implement proper backup strategy
