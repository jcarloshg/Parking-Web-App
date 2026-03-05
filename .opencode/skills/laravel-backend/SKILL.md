---
name: laravel-backend
description: Laravel 10+ backend development conventions, naming, imports, error handling, and key commands
compatibility: opencode
---

# Laravel Backend Development

## Project Context

- **Stack**: Laravel 10+ (PHP 8.2+)
- **Auth**: Laravel Sanctum
- **Database**: MySQL 8.0

## Naming Conventions

| Element           | Convention             | Example                       |
| ----------------- | ---------------------- | ----------------------------- |
| Models            | PascalCase             | `ParkingSpace`, `Ticket`      |
| Controllers       | Plural + Controller    | `ParkingSpacesController`     |
| Methods/Variables | camelCase              | `getAvailableSpaces()`        |
| Tables            | snake_case             | `parking_spaces`              |
| Migrations        | Descriptive snake_case | `create_parking_spaces_table` |

## Import Order

1. PHP built-in (Illuminate\Http\Request)
2. External packages (Spatie, etc.)
3. Internal models (App\Models\*)
4. Internal services (App\Services\*)

## Code Standards

### Types & Return Types

Always use return types. Use nullable types when appropriate.

```php
public function getAvailableSpaces(): Collection
public function findByPlate(string $plate): ?Ticket
public function calculateFee(Ticket $ticket): array
```

### Error Handling

```php
try {
    $payment = $this->paymentService->process($data);
} catch (PaymentException $e) {
    Log::error('Payment failed: ' . $e->getMessage());
    return response()->json(['error' => 'Payment failed'], 500);
}
```

### Validation

```php
$request->validate([
    'plate_number' => 'required|string|max:20',
    'vehicle_type' => 'required|in:auto,moto,camioneta',
]);
```

### Controllers

Keep thin - delegate to services. Use Form Requests for validation.

## Key Commands

```bash
# Run tests
./vendor/bin/phpunit
./vendor/bin/phpunit --filter=ParkingSpaceTest

# Lint
./vendor/bin/pint --test
./vendor/bin/phpstan analyse

# Database
php artisan migrate
php artisan db:seed
php artisan make:model Ticket -m
php artisan make:controller TicketController
```

## Security

- Hash passwords with bcrypt
- Validate all inputs
- Use CSRF protection
- Never commit secrets (use .env.example)

## Docker Setup

### Project Structure

```
project-root/
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   └── ...
├── database/
│   ├── Dockerfile
│   ├── migrations/
│   ├── seeders/
│   └── ...
├── frontend/
│   └── ...
├── docker-compose.yml
└── .env
```

### Dockerfile Best Practices

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate application key
RUN php artisan key:generate

# Clear caches
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### docker-compose.yml Service

```yaml
backend:
  build:
    context: ./backend
    dockerfile: Dockerfile
  container_name: parking_backend
  restart: unless-stopped
  depends_on:
    mysql:
      condition: service_healthy
  environment:
    APP_ENV: ${APP_ENV:-local}
    APP_DEBUG: ${APP_DEBUG:-true}
    APP_URL: ${APP_URL:-http://localhost:8000}
    DB_CONNECTION: mysql
    DB_HOST: mysql
    DB_PORT: 3306
    DB_DATABASE: ${DB_DATABASE:-parking_db}
    DB_USERNAME: ${DB_USERNAME:-parking_user}
    DB_PASSWORD: ${DB_PASSWORD:-parking_password}
    SANCTUM_STATEFUL_DOMAINS: ${SANCTUM_STATEFUL_DOMAINS:-localhost:5173}
  ports:
    - "${APP_PORT:-8000}:8000"
  volumes:
    - ./backend:/var/www/html
  networks:
    - parking_network
  healthcheck:
    test: ["CMD", "curl", "-f", "http://localhost:8000"]
    interval: 30s
    timeout: 10s
    retries: 3
```

### Docker Entrypoint Script

```bash
#!/bin/bash
set -e

# Wait for MySQL to be ready
until mysql -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" &>/dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Run migrations
php artisan migrate --force

# Start the application
exec "$@"
```

### Key Docker Commands

```bash
# Build and start all services
docker compose up -d --build

# View logs
docker compose logs -f backend

# Run artisan commands
docker compose exec backend php artisan migrate

# Access container shell
docker compose exec backend bash

# Stop all services
docker compose down

# Rebuild single service
docker compose build backend
docker compose up -d backend
```

### Production Considerations

- Use `--no-dev` flag for composer install
- Enable OPcache for production
- Use Redis for caching and sessions
- Configure proper logging
- Set `APP_DEBUG=false` in production
- Use proper SSL/TLS termination at reverse proxy
