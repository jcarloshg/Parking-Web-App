---
name: mysql
description: MySQL best practices for parking web app - schema design, indexing, query optimization, and security
compatibility: opencode
---

# MySQL Best Practices

## Schema Design

### Naming Conventions

- Tables: snake_case, plural (parking_spaces, tickets)
- Columns: snake_case (plate_number, parking_space_id)
- Indexes: idx*<table>*<column(s)>
- Foreign keys: fk*<table>*<referenced_table>

### Data Types

| Data       | Type            | Notes                 |
| ---------- | --------------- | --------------------- |
| IDs        | BIGINT UNSIGNED | AUTO_INCREMENT        |
| Money      | DECIMAL(10,2)   | Never FLOAT/DOUBLE    |
| Booleans   | TINYINT(1)      | Or BOOLEAN            |
| Enums      | ENUM            | For fixed sets        |
| Timestamps | TIMESTAMP       | With NULL for Laravel |
| Long text  | TEXT            | For descriptions      |

### Schema Best Practices

```sql
-- Always specify engine and charset
CREATE TABLE example (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ...
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Use TIMESTAMP NULL for Laravel compatibility
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL,
```

## Indexing Strategy

### When to Index

- Foreign keys (always)
- Columns in WHERE clauses
- Columns in JOIN conditions
- Columns in ORDER BY
- High-cardinality columns for searches

### Indexes for Parking App

```sql
-- Ticket searches by plate
CREATE INDEX idx_tickets_plate_number ON tickets(plate_number);

-- Ticket status queries
CREATE INDEX idx_tickets_parking_space_status ON tickets(parking_space_id, status);

-- Payment reports by date
CREATE INDEX idx_payments_paid_at ON payments(paid_at);

-- Activity logs by user
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
```

### Composite Index Rules

- Put equality conditions first
- Put range conditions last
- Order by selectivity (high → low)

```sql
-- Good: status (equality) + parking_space_id (equality)
INDEX idx_status_space (status, parking_space_id)

-- Bad: range first
INDEX idx_bad (entry_time, status)
```

## Query Optimization

### SELECT Best Practices

```sql
-- ❌ Avoid
SELECT * FROM tickets WHERE plate_number = 'ABC-123';

-- ✅ Specify columns
SELECT id, plate_number, entry_time, status
FROM tickets
WHERE plate_number = 'ABC-123';

-- ✅ Use EXPLAIN to analyze
EXPLAIN SELECT * FROM tickets WHERE plate_number = 'ABC-123';
```

### JOIN Optimization

```sql
-- ✅ Always specify JOIN conditions
SELECT t.*, p.total, ps.number
FROM tickets t
INNER JOIN payments p ON t.id = p.ticket_id
INNER JOIN parking_spaces ps ON t.parking_space_id = ps.id
WHERE t.status = 'activo';

-- ✅ Use appropriate JOIN type
-- INNER JOIN: when you need both tables
-- LEFT JOIN: when left table may not have matches
```

### Pagination

```sql
-- ❌ Slow on large tables
SELECT * FROM tickets ORDER BY id LIMIT 100000, 10;

-- ✅ Use keyset pagination
SELECT * FROM tickets
WHERE id < :last_id
ORDER BY id DESC
LIMIT 10;
```

## Security

### SQL Injection Prevention

```sql
-- ❌ Never concatenate user input
$query = "SELECT * FROM users WHERE email = '$email'";

-- ✅ Use parameterized queries (Laravel handles this)
User::where('email', $email)->get();
```

### Least Privilege

```sql
-- Create app user with limited permissions
CREATE USER 'parking_app'@'%' IDENTIFIED BY 'strong_password';

GRANT SELECT, INSERT, UPDATE, DELETE ON parking_db.*
TO 'parking_app'@'%';

-- Never grant ALL PRIVILEGES to app user
```

### Sensitive Data

```sql
-- ✅ Hash passwords (Laravel does this with bcrypt)
-- Never store plain passwords

-- ✅ Encrypt sensitive fields if needed
-- Use Laravel's encryption features
```

## Backup & Recovery

### Backup Command

```bash
# Full backup
mysqldump -u root -p parking_db > backup_$(date +%Y%m%d).sql

# Backup with drop table
mysqldump -u root -p --add-drop-table parking_db > backup.sql

# Backup specific tables
mysqldump -u root -p parking_db tickets payments > partial.sql
```

### Point-in-Time Recovery

```bash
# Enable binary logging in my.cnf
# log_bin = /var/log/mysql/mysql-bin
# expire_logs_days = 7

# Restore full backup
mysql -u root -p parking_db < full_backup.sql

# Apply binary logs to point in time
mysqlbinlog mysql-bin.000001 | mysql -u root -p
```

## Monitoring & Maintenance

### Check Table Health

```sql
-- Check for corruption
CHECK TABLE tickets;

-- Optimize fragmented tables
OPTIMIZE TABLE tickets;

-- Analyze for query optimizer
ANALYZE TABLE tickets;
```

### Query Cache Considerations

```sql
-- MySQL 8.0+ removed query cache
-- Use persistent connections or application caching

-- Monitor slow queries
SHOW VARIABLES LIKE 'slow_query_log%';

-- Set slow query threshold (in seconds)
SET GLOBAL long_query_time = 2;
```

### Connection Management

```sql
-- Check active connections
SHOW STATUS LIKE 'Threads_connected';

-- Max connections
SHOW VARIABLES LIKE 'max_connections';

-- Kill idle connections (be careful)
SELECT * FROM information_schema.processlist
WHERE time > 300 AND command = 'Sleep';
```

## Common Patterns

### Soft Deletes

```sql
-- Add deleted_at column
ALTER TABLE tickets
ADD COLUMN deleted_at TIMESTAMP NULL;

-- Query excluding deleted
SELECT * FROM tickets WHERE deleted_at IS NULL;

-- Laravel handles this automatically with SoftDeletes trait
```

### UUIDs vs Auto-increment

```sql
-- For distributed systems, consider UUIDs
ALTER TABLE tickets
ADD COLUMN uuid CHAR(36) DEFAULT (UUID());

-- For single server, use BIGINT AUTO_INCREMENT (recommended)
```

### Audit Trail

```sql
-- Create trigger for audit logging
CREATE TRIGGER tickets_audit
AFTER UPDATE ON tickets
FOR EACH ROW
INSERT INTO activity_logs (user_id, action, description, created_at)
VALUES (NEW.parking_space_id, 'update',
        CONCAT('Ticket ', OLD.id, ' updated'), NOW());
```

## Docker MySQL Configuration

### Project Structure

```
database/
├── migrations/
│   ├── 2024-01-01-create-parking-spaces.sql
│   ├── 2024-01-01-create-users.sql
│   ├── 2024-01-01-create-tariffs.sql
│   ├── 2024-01-01-create-tickets.sql
│   ├── 2024-01-01-create-payments.sql
│   └── 2024-01-01-create-activity-logs.sql
├── seeders/
│   ├── 2024-01-01-seed-tariffs.sql
│   ├── 2024-01-01-seed-admin-user.sql
│   └── 2024-01-01-seed-parking-spaces.sql
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── .dockerignore
```

### Migration File Naming

- Format: `{YYYY-MM-DD}-{purpose}.sql`
- Examples: `2024-01-15-create-tickets.sql`, `2024-02-01-add-index-to-tickets.sql`

### Seeder File Naming

- Format: `{YYYY-MM-DD}-{purpose}.sql`
- Examples: `2024-01-15-seed-tariffs.sql`, `2024-01-15-seed-admin-user.sql`

### Dockerfile

```dockerfile
FROM mysql:8.0

LABEL maintainer="parking-web-app"
LABEL description="MySQL 8.0 for Parking Web App"

# Set environment variables
ENV MYSQL_ROOT_PASSWORD=root_password \
    MYSQL_DATABASE=parking_db \
    MYSQL_USER=parking_user \
    MYSQL_PASSWORD=parking_password

# Copy migration files (executed in alphabetical order)
COPY migrations/*.sql /docker-entrypoint-initdb.d/

# Copy seeder files (executed after migrations)
COPY seeders/*.sql /docker-entrypoint-initdb.d/

# Expose MySQL port
EXPOSE 3306
```

### docker-compose.yml MySQL service

```yaml
mysql:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: parking_mysql
  restart: unless-stopped
  environment:
    MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    MYSQL_DATABASE: ${DB_DATABASE}
    MYSQL_USER: ${DB_USERNAME}
    MYSQL_PASSWORD: ${DB_PASSWORD}
  ports:
    - "3306:3306"
  volumes:
    - mysql_data:/var/lib/mysql
  command:
    - --character-set-server=utf8mb4
    - --collation-server=utf8mb4_unicode_ci
    - --max_connections=200
    - --innodb_buffer_pool_size=256M
  healthcheck:
    test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
    interval: 10s
    timeout: 5s
    retries: 5
```

### Migration Template

```sql
-- Migration: {description}
-- Created: {YYYY-MM-DD}

CREATE TABLE IF NOT EXISTS example_table (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes
CREATE INDEX idx_example_name ON example_table(name);
```

### Seeder Template

```sql
-- Seeder: {description}
-- Created: {YYYY-MM-DD}

USE parking_db;

-- Insert data
INSERT INTO example_table (name, created_at, updated_at) VALUES
('Sample Name', NOW(), NOW());
```
